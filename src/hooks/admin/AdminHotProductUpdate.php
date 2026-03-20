<?php
/**
 * Admin Hot Product Update Hook.
 *
 * @package InpostPay
 * @subpackage Hooks/Admin
 */

namespace Ilabs\Inpost_Pay\hooks\admin;

use Ilabs\Inpost_Pay\hooks\Base;
use Ilabs\Inpost_Pay\Lib\Api\v1\Products;
use Ilabs\Inpost_Pay\Lib\config\product\HotProductsConfig;
use Ilabs\Inpost_Pay\Lib\form\exception\IsNotArrayException;
use Ilabs\Inpost_Pay\Lib\Product\CustomMeta\HotProductPublishedMeta;
use Ilabs\Inpost_Pay\Lib\Utils\HotProductUtils;
use Ilabs\Inpost_Pay\Logger;
use Ilabs\Inpost_Pay\Lib\helpers\WooProductHelper;
use WC_Order;
use function Ilabs\Inpost_Pay\inpost_pay_container;

/**
 * Class AdminHotProductUpdate
 *
 * Handles hot product updates in the admin interface.
 */
class AdminHotProductUpdate extends Base {

	/**
	 * Product helper instance.
	 *
	 * @var WooProductHelper
	 */
	protected WooProductHelper $product_helper;

	/**
	 * AdminHotProductUpdate constructor.
	 */
	public function __construct() {
		/**
		 * Get from container DI.
		 *
		 * @var WooProductHelper $product_helper
		 */
		$product_helper       = inpost_pay_container()->get( WooProductHelper::SERVICE_KEY );
		$this->product_helper = $product_helper;
	}

	/**
	 * Flag to prevent recursive execution.
	 *
	 * @var bool
	 */
	public static bool $is_start = false;

	/**
	 * Attach hooks.
	 *
	 * @return void
	 */
	public function attach_hook(): void {
		add_action(
			'woocommerce_process_product_meta',
			array(
				$this,
				'update_product',
			),
			20,
			2
		);
		add_action(
			'woocommerce_delete_product',
			array(
				$this,
				'delete_product',
			)
		);
		add_action(
			'woocommerce_trash_product',
			array(
				$this,
				'delete_product',
			)
		);

		add_action(
			'woocommerce_reduce_order_stock',
			array(
				$this,
				'check_zero_stock_products',
			)
		);

		add_action(
			'update_option_woocommerce_currency',
			array(
				$this,
				'handle_currency_change',
			),
			10,
			2
		);

		add_action(
			'update_option_izi_main_image_only',
			array(
				$this,
				'handle_main_image_only_change',
			),
			10,
			2
		);
	}

	/**
	 * Update product.
	 *
	 * @param int   $post_id The post ID.
	 * @param mixed $post    The post object.
	 *
	 * @return void
	 */
	public function update_product( int $post_id, $post ): void {
		if ( self::$is_start ) {
			return;
		}

		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		self::$is_start = true;

		$product    = wc_get_product( $post_id );
		$product_id = $product->get_id();

		$gallery_ids = array();
		if ( isset( $_POST['product_image_gallery'] ) ) {
			// woocommerce_process_product_meta fires after WooCommerce already verified the nonce
			// in WC_Meta_Box_Product_Data::save(), so a second check here is redundant.
			$gallery_string = sanitize_text_field( wp_unslash( $_POST['product_image_gallery'] ) );
			$gallery_ids    = array_unique( array_filter( array_map( 'absint', explode( ',', $gallery_string ) ) ) );
		}

		$hot_products_config = new HotProductsConfig();
		$izi_product_api     = new Products();

		if ( $product->get_status() !== 'publish' ) {
			$izi_product_api->delete( $product_id );
			$hot_products_list = array_values( array_diff( $hot_products_config->get(), array( $product_id ) ) );
			$hot_products_config->update( $hot_products_list );
			self::$is_start = false;

			return;
		}

		try {
			if ( ! $hot_products_config->checkValueInArray( $product_id ) ) {
				self::$is_start = false;

				return;
			}
		} catch ( IsNotArrayException $e ) {
			Logger::log( 'Error checking hot products array: ' . $e->getMessage() );
			self::$is_start = false;

			return;
		}

		$response = $izi_product_api->put( $product, true, $gallery_ids );

		if ( ! $response || isset( $response->error_code ) ) {
			self::$is_start = false;

			return;
		}

		HotProductUtils::handlePutResponse( $response );

		self::$is_start = false;
	}

	/**
	 * Delete product.
	 *
	 * @param int $product_id The product ID.
	 *
	 * @return void
	 */
	public function delete_product( int $product_id ): void {
		$hot_products = new HotProductsConfig();

		$hot_products_list = $hot_products->get();

		if ( ! in_array( $product_id, $hot_products_list, true ) ) {
			return;
		}

		$izi_product_api = new Products();
		$izi_product_api->delete( $product_id );

		$hot_products_list = array_values( array_diff( $hot_products_list, array( $product_id ) ) );

		$hot_products->update( $hot_products_list );
	}

	/**
	 * Check zero stock products.
	 *
	 * @param WC_Order $order The order that reduced stock.
	 *
	 * @return void
	 */
	public function check_zero_stock_products( WC_Order $order ): void {
		$hot_products_config = new HotProductsConfig();
		$hot_products        = $hot_products_config->get();

		if ( empty( $hot_products ) ) {
			return;
		}

		$products_api     = new Products();
		$updated_products = array();

		foreach ( $order->get_items() as $item ) {
			if ( ! ( $item instanceof \WC_Order_Item_Product ) ) {
				continue;
			}

			$product = $item->get_product();
			if ( ! $product ) {
				continue;
			}

			$product_id = $product->get_id();

			if ( ! in_array( $product_id, $hot_products, true ) || in_array( $product_id, $updated_products, true ) ) {
				continue;
			}

			if ( $product->managing_stock() && $product->get_stock_quantity() <= 0 ) {
				try {
					$response = $products_api->put( $product );

					HotProductUtils::handlePutResponse( $response );
				} catch ( \JsonException $e ) {
					Logger::log( 'Error checking hot products array: ' . $e->getMessage() );
				}
			}
		}
	}

	/**
	 * Handle currency change.
	 *
	 * @param string $old_value The previous currency code.
	 * @param string $new_value The new currency code.
	 *
	 * @return void
	 */
	public function handle_currency_change( string $old_value, string $new_value ): void {
		if ( $old_value === $new_value ) {
			return;
		}

		$hot_products_config = new HotProductsConfig();
		$hot_product_ids     = $hot_products_config->get();

		if ( empty( $hot_product_ids ) ) {
			return;
		}

		$products     = $this->product_helper->load_products_safe( $hot_product_ids );
		$products_api = new Products();

		foreach ( $products as $id => $product ) {
			if ( ! $product ) {
				continue;
			}

			$product_status = get_post_meta(
				$id,
				HotProductPublishedMeta::INPOST_PAY_HOT_PRODUCT_PUBLISHED,
				true
			);

			if ( HotProductUtils::STATUS_ACTIVE === $product_status ) {
				update_post_meta(
					$id,
					HotProductPublishedMeta::INPOST_PAY_HOT_PRODUCT_PUBLISHED,
					HotProductUtils::STATUS_INACTIVE
				);

				if ( is_admin() ) {
					set_transient(
						'inpost_pay_product_update_hot_inactive_' . get_current_user_id(),
						$id,
						30
					);
				}

				$response = $products_api->put( $product );

				HotProductUtils::handlePutResponse( $response );
			}
		}
	}

	/**
	 * Handle Hot Product refresh when the global "main image only" option changes.
	 *
	 * Triggered when the "izi_main_image_only" option value is updated.
	 * Refreshes all active Hot Products in InPost Pay to reflect the
	 * new global gallery configuration.
	 *
	 * @param string|bool $old_value Previous option value.
	 * @param string|bool $new_value New option value.
	 */
	public function handle_main_image_only_change( $old_value, $new_value ): void {

		if ( $old_value === $new_value ) {
			// phpcs:ignore Squiz.PHP.CommentedOutCode.Found,Squiz.Commenting.InlineComment.InvalidEndChar
			// Logger::log(Logger::PREFIX_HOT_PRODUCTS . ' No change in "izi_main_image_only" value, skipping refresh.');
			return;
		}

		$hot_products_config = new HotProductsConfig();
		$hot_product_ids     = $hot_products_config->get();

		if ( empty( $hot_product_ids ) ) {
			// phpcs:ignore Squiz.PHP.CommentedOutCode.Found,Squiz.Commenting.InlineComment.InvalidEndChar
			// Logger::log(Logger::PREFIX_HOT_PRODUCTS . ' No Hot Products found, nothing to refresh.');
			return;
		}

		$products_api = new Products();
		// phpcs:ignore Squiz.PHP.CommentedOutCode.Found,Squiz.Commenting.InlineComment.InvalidEndChar
		// Logger::log(Logger::PREFIX_HOT_PRODUCTS . ' Product ID list: ' . implode(', ', $hot_products).');

		$hot_products = $this->product_helper->load_products_safe( $hot_product_ids );
		foreach ( $hot_products as $product ) {
			$product_id     = $product->get_id();
			$product_status = get_post_meta(
				$product_id,
				HotProductPublishedMeta::INPOST_PAY_HOT_PRODUCT_PUBLISHED,
				true
			);

			// phpcs:ignore Squiz.PHP.CommentedOutCode.Found,Squiz.Commenting.InlineComment.InvalidEndChar
			// Logger::log(sprintf(Logger::PREFIX_HOT_PRODUCTS . ' Product #%d current status: %s', $product_id, $product_status ?: 'N/A').');

			if ( HotProductUtils::STATUS_ACTIVE !== $product_status ) {
				Logger::log( sprintf( Logger::PREFIX_HOT_PRODUCTS . ' Product #%d skipped (status != ACTIVE)', $product_id ) );
				continue;
			}

			try {
				// phpcs:ignore Squiz.PHP.CommentedOutCode.Found,Squiz.Commenting.InlineComment.InvalidEndChar
				// Logger::log(sprintf(Logger::PREFIX_HOT_PRODUCTS . ' Sending PUT request for product #%d...', $product_id).');
				$response = $products_api->put( $product, false );
				// phpcs:ignore Squiz.PHP.CommentedOutCode.Found,Squiz.Commenting.InlineComment.InvalidEndChar
				// Logger::log(sprintf(Logger::PREFIX_HOT_PRODUCTS . ' PUT response for #%d: %s', $product_id, var_export($response, true)).');

				if ( ! $response ) {
					Logger::log( sprintf( Logger::PREFIX_HOT_PRODUCTS . ' Empty PUT response for product #%d', $product_id ) );
					continue;
				}

				if ( isset( $response->error_code ) ) {
					Logger::log(
						sprintf(
							Logger::PREFIX_HOT_PRODUCTS . ' Refresh failed for product #%d: %s',
							$product_id,
							$response->error_code
						)
					);
					continue;
				}

				$status = HotProductUtils::handlePutResponse( $response );
				// phpcs:ignore Squiz.PHP.CommentedOutCode.Found,Squiz.Commenting.InlineComment.InvalidEndChar
				// Logger::log(sprintf(Logger::PREFIX_HOT_PRODUCTS . ' handlePutResponse returned status "%s" for product #%d', $status, $product_id));

				// phpcs:ignore Squiz.PHP.CommentedOutCode.Found,Squiz.Commenting.InlineComment.InvalidEndChar
				// $updated_meta = get_post_meta( $product_id, HotProductPublishedMeta::INPOST_PAY_HOT_PRODUCT_PUBLISHED, true );
				// Logger::log(sprintf(Logger::PREFIX_HOT_PRODUCTS . ' Product #%d meta after update: %s', $product_id, $updated_meta ?: 'N/A'));

			} catch ( \Throwable $e ) {
				Logger::log(
					sprintf(
						Logger::PREFIX_HOT_PRODUCTS . ' Exception for product #%d: %s',
						$product_id,
						$e->getMessage()
					)
				);
			}
		}
	}
}
