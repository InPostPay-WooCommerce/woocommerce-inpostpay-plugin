<?php
/**
 * FrontBasketChange hook handler.
 *
 * This class handles front-end basket change events and updates the InPost Pay
 * basket when cart modifications occur. It listens to various WooCommerce cart
 * actions and triggers basket updates accordingly.
 *
 * @package Ilabs\Inpost_Pay\hooks\front
 * @since 1.0.0
 */

namespace Ilabs\Inpost_Pay\hooks\front;

use Ilabs\Inpost_Pay\Exception\RepositoryException;
use Ilabs\Inpost_Pay\Lib\Basket\BasketPutService;
use Ilabs\Inpost_Pay\Lib\BasketIdentification;
use Ilabs\Inpost_Pay\Lib\config\Hooks\Executor\CartHooksExecutor;
use Ilabs\Inpost_Pay\Lib\helpers\DigitalProduct;
use Ilabs\Inpost_Pay\Lib\Remote;
use Ilabs\Inpost_Pay\Logger;
use Ilabs\Inpost_Pay\models\CartSessionService;
use Ilabs\Inpost_Pay\objects\BasketBindingApiKey;
use Ilabs\Inpost_Pay\Type\WooCommerceSessionKeyType;
use Ilabs\Inpost_Pay\WooCommerce\WooCommerceInPostIzi;
use Throwable;
use function Ilabs\Inpost_Pay\inpost_pay_container;

/**
 * FrontBasketChange hook handler class.
 *
 * Handles front-end basket change events and updates the InPost Pay
 * basket when cart modifications occur in WooCommerce.
 *
 * @package Ilabs\Inpost_Pay\hooks\front
 */
class FrontBasketChange extends FrontBase {
	public static bool $block_action_set = false;

	public static bool $hook_is_start = false;

	private CartSessionService $cart_session;

	private const KNOWN_AJAX_ADD_TO_CART_ACTIONS = array(
		'woodmart_ajax_add_to_cart',
		'etheme_ajax_add_to_cart',
		'stockie_ajax_add_to_cart_woo_single',
		'base_pro_add_to_cart',
	);

	/**
	 * Constructor.
	 *
	 * Initializes the FrontBasketChange instance by setting up the cart session service
	 * from the dependency injection container.
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
	 * Extends the base frontend hook registration to also cover theme-specific AJAX actions.
	 *
	 * The parent skips is_admin() requests (which includes admin-ajax.php), so AJAX
	 * add-to-cart from custom themes would never trigger a basket sync. Registering
	 * wp_ajax_* hooks here fills that gap while still respecting the canShow() guard
	 * from the call site.
	 *
	 * @return void
	 */
	public function attach_frontend_hook(): void {
		$this->attach_ajax_theme_hooks();
		parent::attach_frontend_hook();
	}

	/**
	 * Attaches hooks to the following actions:
	 *
	 * - `woocommerce_update_cart_action_cart_updated`
	 * - `woocommerce_add_to_cart`
	 * - `woocommerce_cart_item_removed`
	 * - `cart_item_set_quantity`
	 * - `woocommerce_cart_item_restored`
	 * - `woocommerce_applied_coupon`
	 * - `woocommerce_removed_coupon`
	 *
	 * The hook is fired after the cart has been modified, and it will fire the
	 * `handle_coupon_update` method if a coupon has been added or removed.
	 *
	 * @return void
	 */
	public function attach_hook(): void {
		if ( ! ( new BasketBindingApiKey() )->get( false ) ) {
			return;
		}

		$hook = $this->create_basket_sync_hook();

		add_action( 'woocommerce_update_cart_action_cart_updated', $hook, 9999 );
		add_action( 'woocommerce_add_to_cart', $hook, 9999 );
		add_action( 'woocommerce_cart_item_removed', $hook, 9999 );

		$executor = new CartHooksExecutor();
		$executor->add_callable_hook(
			'cart_item_set_quantity',
			$hook
		);

		add_action( 'woocommerce_cart_item_restored', $hook, 9999 );

		add_action( 'woocommerce_applied_coupon', array( $this, 'handle_coupon_update' ) );
		add_action( 'woocommerce_removed_coupon', array( $this, 'handle_coupon_update' ) );
		add_action( 'woocommerce_store_api_validate_add_to_cart', $hook, 9999, 2 );
		add_action( 'woocommerce_store_api_validate_cart_item', $hook, 9999, 2 );
		add_action( 'woocommerce_store_api_cart_update_order_from_request', $hook, 9999, 2 );
		add_action( 'woocommerce_store_api_cart_update_customer_from_request', $hook, 9999, 2 );
		add_action( 'woocommerce_store_api_cart_select_shipping_rate', $hook, 9999, 3 );
		add_action( 'woocommerce_store_api_checkout_update_order_meta', array(
			$this,
			'handle_coupon_update'
		), 9999, 1 );
		add_action(
			'woocommerce_store_api_checkout_update_customer_from_request',
			array(
				$this,
				'handle_coupon_update',
			),
			9999,
			2
		);
	}

	/**
	 * Returns the list of known AJAX add-to-cart actions, extensible via filter.
	 *
	 * @return string[]
	 */
	private static function get_ajax_actions(): array {
		return apply_filters( 'inpost_pay_ajax_actions', self::KNOWN_AJAX_ADD_TO_CART_ACTIONS );
	}

	/**
	 * Register basket sync handlers for known theme-specific AJAX add-to-cart actions.
	 *
	 * Hooks into wp_ajax_{action} / wp_ajax_nopriv_{action} at priority 0 so our handler
	 * runs before the theme's handler calls wp_die(). The actual sync is deferred to
	 * shutdown so the cart is already updated by the time we read it.
	 *
	 * @return void
	 */
	private function attach_ajax_theme_hooks(): void {
		$hook = $this->create_basket_sync_hook();

		foreach ( self::get_ajax_actions() as $action ) {
			add_action( 'wp_ajax_' . $action, $hook, 0 );
			add_action( 'wp_ajax_nopriv_' . $action, $hook, 0 );
		}
	}

	/**
	 * Build the callable used as a cart-change hook.
	 *
	 * Sets hook_is_start to prevent re-entry and schedules execute_basket_sync on
	 * shutdown (unless a sync was already queued via block_action_set).
	 *
	 * @return callable
	 */
	private function create_basket_sync_hook(): callable {
		$cart_session = $this->cart_session;

		return static function () use ( $cart_session ) {
			if ( self::$hook_is_start ) {
				return;
			}

			self::$hook_is_start = true;

			if ( false === self::$block_action_set ) {
				add_action(
					'shutdown',
					static function () use ( $cart_session ) {
						self::execute_basket_sync( $cart_session );
					},
					9999
				);
			}
		};
	}

	/**
	 * Sync the basket with InPost Pay if the cart has changed.
	 *
	 * Shared by both the standard WooCommerce hook path and the theme-specific AJAX path.
	 * Guards against empty session and unchanged cart before making the remote PUT call.
	 *
	 * @param CartSessionService $cart_session Cart session service instance.
	 *
	 * @return void
	 */
	private static function execute_basket_sync( CartSessionService $cart_session ): void {
		try {
			if ( ! ( new BasketBindingApiKey() )->get( false ) ) {
				return;
			}

			if ( ! \WC()->session ) {
				return;
			}

			if ( ! self::cart_has_changed() ) {
				return;
			}

			\WC()->session->save_data();

			$izi          = WooCommerceInPostIzi::get_instance();
			Remote::$done = false;

			DigitalProduct::handleDigitalProduct();

			/**
			 * Get from container DI.
			 *
			 * @var BasketPutService $basket_put_service
			 */
			$basket_put_service = inpost_pay_container()->get( BasketPutService::SERVICE_KEY );
			$basket_put_service->put(
				fn() => $izi->getBasket(),
				$izi->get_controller()
			);

			self::$block_action_set = true;

			$count = \WC()->cart ? \WC()->cart->get_cart_contents_count() : 0;
			$cart_session->set_action_by_id( BasketIdentification::get(), 'update-count:' . $count );
		} catch ( RepositoryException $e ) {
			Logger::log( '[BASKET_CHANGE] RepositoryException: ' . $e->getMessage() );
		} catch ( Throwable $e ) {
			Logger::log( '[BASKET_CHANGE] Throwable: ' . $e->getMessage() );
		}
	}

	/**
	 * Check if the cart has changed since last check.
	 *
	 * Compares the current cart contents hash with the stored hash to determine
	 * if any cart modifications have occurred. Updates the stored hash if changed.
	 *
	 * @return bool True if cart has changed, false otherwise.
	 */
	private static function cart_has_changed(): bool {
		// Guard: Don't access cart/session if not initialized (cache-friendly pages).
		if ( ! \WC()->session ) {
			return false;
		}

		$cart = \WC()->cart;

		if ( ! $cart ) {
			return false;
		}

		$current = md5( wp_json_encode( $cart->get_cart() ) );
		$stored  = \WC()->session->get( WooCommerceSessionKeyType::INPOST_CART_HASH );

		if ( $stored === $current ) {
			return false;
		}

		if ( \WC()->session ) {
			\WC()->session->set( WooCommerceSessionKeyType::INPOST_CART_HASH, $current );
		}

		return true;
	}

	/**
	 * Check if the coupon state has changed since the last check.
	 *
	 * Compares the current coupon state hash with the stored hash to determine
	 * whether applied coupons or related totals have changed. Updates the stored
	 * hash when a change is detected.
	 *
	 * @return bool True if the coupon state has changed, false otherwise.
	 */
	private static function coupon_state_has_changed(): bool {
		// Guard: Don't access cart/session if not initialized (cache-friendly pages).
		if ( ! \WC()->session ) {
			return false;
		}

		$cart = \WC()->cart;

		if ( ! $cart ) {
			return false;
		}

		$state = array(
			'applied_coupons' => $cart->get_applied_coupons(),
			'discount_total'  => $cart->get_discount_total(),
			'total'           => $cart->get_total( 'edit' ),
			'shipping_total'  => $cart->get_shipping_total(),
		);

		$current = md5( wp_json_encode( $state ) );
		$stored  = \WC()->session->get( 'inpost_coupon_hash' );

		if ( $stored === $current ) {
			return false;
		}

		\WC()->session->set( 'inpost_coupon_hash', $current );

		return true;
	}

	/**
	 * Handle coupon update.
	 *
	 * This function is triggered after adding or removing a coupon. It will fire the `basketPut` method
	 * to update the basket on the remote server.
	 *
	 * @return void
	 */
	public function handle_coupon_update(): void {
		if ( self::$hook_is_start ) {
			return;
		}

		// Guard: Don't process if WC session not initialized.
		if ( ! \WC()->session ) {
			return;
		}

		if ( ! self::coupon_state_has_changed() ) {
			return;
		}

		self::$hook_is_start = true;

		if ( false === self::$block_action_set ) {
			if ( \WC()->cart ) {
				\WC()->cart->calculate_totals();
			}

			add_action(
				'shutdown',
				static function () {
					try {
						$izi          = WooCommerceInPostIzi::get_instance();
						Remote::$done = false;
						/**
						 * Get from container DI.
						 *
						 * @var BasketPutService $basket_put_service
						 */
						$basket_put_service = inpost_pay_container()->get( BasketPutService::SERVICE_KEY );
						$basket_put_service->put(
							fn() => $izi->getBasket(),
							$izi->get_controller()
						);
					} catch ( RepositoryException $e ) {
						Logger::log( '[BASKET_CHANGE] RepositoryException (coupon): ' . $e->getMessage() );
					} catch ( Throwable $e ) {
						Logger::log( '[BASKET_CHANGE] Throwable (coupon): ' . $e->getMessage() );
					}
				},
				9999
			);
		}
	}
}
