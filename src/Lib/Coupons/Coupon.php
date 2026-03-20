<?php

namespace Ilabs\Inpost_Pay\Lib\Coupons;

use Automattic\WooCommerce\Utilities\NumberUtil;
use Exception;
use Ilabs\Inpost_Pay\InpostPay;
use Ilabs\Inpost_Pay\Lib\BasketIdentification;
use Ilabs\Inpost_Pay\Lib\Coupons\Fields\FieldInterface;
use Ilabs\Inpost_Pay\Lib\Coupons\Fields\PromotionUrlField;
use Ilabs\Inpost_Pay\Lib\Coupons\Fields\VisibleInAppCheckboxField;
use Ilabs\Inpost_Pay\Lib\InPostIzi;
use Ilabs\Inpost_Pay\models\CartSessionService;
use Ilabs\Inpost_Pay\rest\RestRequest;
use JsonException;
use WC_Admin_Meta_Boxes;
use WC_Coupon;
use WC_Discounts;
use function Ilabs\Inpost_Pay\inpost_pay_container;

class Coupon {

	public const COUPON_TYPE_PERCENT_PRODUCT = 'inpost_pay_discount';
	public const COUPON_TYPE_FIXED_PRODUCT = 'inpost_pay_discount_fixed_product';
	public const COUPON_TYPE_FIXED_CART = 'inpost_pay_discount_fixed_cart';
	public const META_VISIBLE_IN_APP = '_inpostpay_visible';
	public const META_PROMOTION_URL = 'inpost_pay_promotion_url';
	public const ONLY_IN_APP_COUPONS = [
		self::COUPON_TYPE_PERCENT_PRODUCT,
		self::COUPON_TYPE_FIXED_PRODUCT,
		self::COUPON_TYPE_FIXED_CART,
	];
	/** @var FieldInterface[] */
	private array $fields;

	private CartSessionService $cart_session;

	public function __construct() {
		/**
		 * Get from container DI.
		 *
		 * @var CartSessionService $cart_session
		 */
		$this->cart_session = inpost_pay_container()->get( CartSessionService::SERVICE_KEY );

		$this->fields = [
			new PromotionUrlField(),
			new VisibleInAppCheckboxField(),
		];
	}

	public function hooks(): void {
		add_filter( 'woocommerce_coupon_data_tabs', [ $this, 'add_inpostpay_coupon_tab' ] );
		add_filter( 'woocommerce_coupon_discount_types', [ $this, 'inpost_pay_custom_discount_type' ], 10, 1 );
		add_action( 'woocommerce_coupon_options_save', [ $this, 'save_meta' ], 10, 2 );
		add_action( 'woocommerce_coupon_data_panels', [ $this, 'render_inpostpay_tab_panel' ], 10, 2 );
		add_filter( 'woocommerce_coupon_is_valid', [ $this, 'inpost_pay_validate_custom_coupon' ], 10, 3 );
		add_filter( 'woocommerce_coupon_get_discount_amount', [
			$this,
			'inpost_pay_apply_custom_coupon_discount',
		], 10, 5 );
		add_filter( 'woocommerce_product_coupon_types', [
			$this,
			'inpost_pay_woocommerce_product_coupon_types',
		], 10, 2 );

		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_admin_coupon_scripts' ], 75 );
	}

	public function add_inpostpay_coupon_tab( $tabs ) {
		$tabs['inpostpay'] = [
			'label'    => __( 'InPost Pay', 'inpost-pay' ),
			'target'   => 'inpost_pay_coupon_data',
			'class'    => [],
			'priority' => 25,
		];

		return $tabs;
	}

	public function render_inpostpay_tab_panel( $coupon_id, $coupon ) {
		echo '<div id="inpost_pay_coupon_data" class="panel woocommerce_options_panel" style="display: none;">';
		foreach ( $this->fields as $field ) {
			$field->render( $coupon_id );
		}
		echo '</div>';
	}

	public function inpost_pay_custom_discount_type( $discount_types ) {
		$discount_types[ self::COUPON_TYPE_PERCENT_PRODUCT ] = __( 'InPost Pay – percentage (product)', 'inpost-pay' );
		$discount_types[ self::COUPON_TYPE_FIXED_PRODUCT ]   = __( 'InPost Pay – fixed amount (product)', 'inpost-pay' );
		$discount_types[ self::COUPON_TYPE_FIXED_CART ]      = __( 'InPost Pay – fixed amount (cart)', 'inpost-pay' );

		return $discount_types;
	}

	public function save_meta( $post_id, $coupon ): void {
		foreach ( $this->fields as $field ) {
			$field->save( $post_id );
		}
	}

	/**
	 * @throws JsonException
	 */
	public function inpost_pay_validate_custom_coupon( $is_valid, WC_Coupon $coupon, WC_Discounts $discount ) {
		$type = $coupon->get_discount_type();

		if ( ! in_array( $type, self::ONLY_IN_APP_COUPONS, true ) ) {
			return $is_valid;
		}

		if ( RestRequest::isRequested() ) {
			return true;
		}

		$data = $this->cart_session->get_cart_confirmation( BasketIdentification::get() );
		if ( is_string( $data ) && strlen( $data ) > 10 ) {
			$data = json_decode( $data, false, 512, JSON_THROW_ON_ERROR );
			return $data->status === 'SUCCESS';
		}

		return false;
	}

	public function inpost_pay_apply_custom_coupon_discount(
		$discount,
		$discounting_amount,
		$cart_item,
		$single,
		$coupon
	) {
		$type = $coupon->get_discount_type();

		if ( ! in_array( $type, self::ONLY_IN_APP_COUPONS, true ) ) {
			return $discount;
		}

		$amount = (float) $coupon->get_amount();

		if ( self::COUPON_TYPE_PERCENT_PRODUCT === $type ) {
			return NumberUtil::round(
				min( $amount * ( $discounting_amount / 100 ), $discounting_amount ),
				wc_get_rounding_precision()
			);
		}

		if ( self::COUPON_TYPE_FIXED_PRODUCT === $type ) {
			return NumberUtil::round(
				min( $amount, $discounting_amount ),
				wc_get_rounding_precision()
			);
		}

		if ( self::COUPON_TYPE_FIXED_CART === $type ) {
			$cart_total = 0.0;
			foreach ( WC()->cart->get_cart() as $item ) {
				if ( isset( $item['data'] ) && $item['data'] instanceof \WC_Product ) {
					$price = $item['data']->get_price();
					$qty = $item['quantity'];
					$cart_total += $price * $qty;
				}
			}

			if ( $cart_total <= 0 ) {
				return 0.0;
			}

			$share_ratio = $discounting_amount / $cart_total;
			return NumberUtil::round( min( $amount * $share_ratio, $discounting_amount ), wc_get_rounding_precision() );
		}

		return $discount;
	}

	// note: FIXED_CART is included here only to trigger woocommerce_coupon_get_discount_amount
	public function inpost_pay_woocommerce_product_coupon_types( $types ): array {
		$types[] = self::COUPON_TYPE_PERCENT_PRODUCT;
		$types[] = self::COUPON_TYPE_FIXED_PRODUCT;
		$types[] = self::COUPON_TYPE_FIXED_CART;

		return $types;
	}

	public function enqueue_admin_coupon_scripts(): void {
		$current_screen = get_current_screen();
		if ( $current_screen instanceof \WP_Screen && 'shop_coupon' === $current_screen->id ) {
			wp_enqueue_script(
				'inpostpay-coupons',
				InpostPay::get_instance()->get_js_assets_path() . 'admin-coupon-script.js',
				[ 'jquery' ]
			);
		}
	}
}
