<?php
/**
 * Front Widget V2 Hook.
 *
 * @package InpostPay
 * @subpackage Hooks/Front
 */

namespace Ilabs\Inpost_Pay\hooks\front;

use Ilabs\Inpost_Pay\Lib\BasketIdentification;
use Ilabs\Inpost_Pay\Lib\helpers\LangHelper;
use Ilabs\Inpost_Pay\Lib\InPostIzi;
use Ilabs\Inpost_Pay\Logger;
use Ilabs\Inpost_Pay\models\CartSessionService;
use Ilabs\Inpost_Pay\objects\BasketBindingApiKey;
use function Ilabs\Inpost_Pay\inpost_pay_container;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class FrontWidgetV2
 *
 * Widget V2.0 implementation.
 */
class FrontWidgetV2 extends FrontBase {

	/**
	 * Merchant ID.
	 *
	 * @var string|null
	 */
	private static ?string $merchant_id = null;

	/**
	 * Basket ID.
	 *
	 * @var string|null
	 */
	private static ?string $basket_id = null;

	/**
	 * Cart cache.
	 *
	 * @var array
	 */
	private static array $cart_cache = array();

	/**
	 * Cart session service.
	 *
	 * @var CartSessionService
	 */
	private CartSessionService $cart_session;

	/**
	 * FrontWidgetV2 constructor.
	 */
	public function __construct() {
		/**
		 * Get from container DI.
		 *
		 * @var CartSessionService $cart_session
		 */
		$this->cart_session = inpost_pay_container()->get( CartSessionService::SERVICE_KEY );
	}

	/**
	 * Attach hooks.
	 *
	 * @return void
	 */
	public function attach_hook(): void {
		add_action( 'wp_loaded', array( $this, 'init' ) );
		if ( function_exists( 'wp_body_open' ) ) {
			add_action(
				'wp_body_open',
				array( $this, 'add_root_script_after_body_open' )
			);
		} else {
			echo '<script>console.log(\'InpostPay: There are no hook WP_BODY_OPEN to attach initial configuration.\')</script>';
		}

		if ( function_exists( 'wp_dashboard' ) ) {
			add_action(
				'wp_dashboard',
				array( $this, 'add_root_script_after_dashboard_body_open' )
			);
		}
	}

	/**
	 * Initialize widget.
	 *
	 * @return void
	 */
	public function init(): void {
		if ( ( defined( 'REST_REQUEST' ) && REST_REQUEST ) || is_admin() || wp_doing_ajax() ) {
			return;
		}

		if ( isset( $_SERVER['HTTP_ACCEPT'] ) && sanitize_text_field( wp_unslash( $_SERVER['HTTP_ACCEPT'] ) ) === 'application/json' ) {
			return;
		}

		$basket_id = BasketIdentification::get();

		// Cache from previous fragment.
		$object = self::$cart_cache[ $basket_id ] ?? wp_cache_get( "cart_session_{$basket_id}", 'inpost_pay' );

		if ( false === $object ) {
			$object = $this->cart_session->get_object_by_id( $basket_id );
			if ( $object ) {
				wp_cache_set( "cart_session_{$basket_id}", $object, 'inpost_pay', 60 );
			}
		}

		self::$cart_cache[ $basket_id ] = $object;

		if ( $object && $object->get_redirect_url() === 'deleted' ) {
			Logger::log( 'DROP BINDING ON DELETE' );
			// phpcs:ignore Squiz.PHP.CommentedOutCode.Found,Squiz.Commenting.InlineComment.InvalidEndChar
			// BasketIdentification::drop();
			InPostIzi::getStorage()->eraseSession( 'binding_get' );
			unset( $binding );
		}

		if ( $object ) {
			$order_id = $object->get_order_id();

			if ( $order_id && $order_id > 0 ) {
				Logger::log( "DROP BINDING ON ORDER - order_id={$order_id}" );
				$this->cart_session->reset_after_order( $basket_id );
				wp_cache_delete( "cart_session_{$basket_id}", 'inpost_pay' );
				BasketIdentification::drop();
				( new BasketBindingApiKey() )->drop();
			}
		}

		self::$merchant_id = self::get_merchant_id();
		self::$basket_id   = $basket_id;
	}

	/**
	 * Attach root script just after <body> tag.
	 *
	 * @return void
	 */
	public function add_root_script_after_body_open(): void {
		?>
		<script>
			const IPPWidgetOptions = {
				merchantClientId: `<?php echo esc_attr( self::$merchant_id ); ?>`,
				basketBindingApiKey: ``,
				language: `<?php echo esc_attr( LangHelper::getWidgetLangAttr() ); ?>`,
				isBlock: `<?php echo has_block( 'woocommerce/cart' ) || has_block( 'woocommerce/checkout' ) ? 'true' : 'false'; ?>`,
				cartWidgetPlacement: `<?php echo esc_attr( get_option( 'izi_place_basket', '' ) ); ?>`
			};
			console.log("Basket ID: <?php echo esc_html( self::$basket_id ); ?>");
		</script>
		<?php
	}
	/**
	 * Attach root script after dashboard body open.
	 *
	 * @return void
	 */
	public function add_root_script_after_dashboard_body_open(): void {
		?>
		<script>
			const IPPWidgetOptions = {
				merchantClientId: `<?php echo esc_attr( self::$merchant_id ); ?>`,
				basketBindingApiKey: ``
			};
		</script>
		<?php
	}

	/**
	 * Get merchant ID.
	 *
	 * @return string Merchant ID.
	 */
	public static function get_merchant_id(): string {
		if ( ! self::$merchant_id ) {
			self::$merchant_id = get_option( 'izi_merchant_id' );
		}

		return self::$merchant_id;
	}
}
