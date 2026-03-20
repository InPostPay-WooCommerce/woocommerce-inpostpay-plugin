<?php
/**
 * Hook initialization for syncing InPost Pay basket cookie/session expiration.
 *
 * @package Ilabs\Inpost_Pay
 * @since 1.0.0
 */

namespace Ilabs\Inpost_Pay\hooks;

use Ilabs\Inpost_Pay\EntityLayer\Repository\CartSessionRepository;
use Ilabs\Inpost_Pay\Lib\BasketIdentification;
use Ilabs\Inpost_Pay\Lib\helpers\CookieHelper;
use Ilabs\Inpost_Pay\Lib\helpers\Woo_Commerce_Session_Helper;
use Ilabs\Inpost_Pay\Lib\InPostIzi;
use Ilabs\Inpost_Pay\Models\CartSessionService;
use function Ilabs\Inpost_Pay\inpost_pay_container;

/**
 * Registers WooCommerce hooks related to cart cookies and initializes session data.
 *
 * @since 1.0.0
 */
class SessionInit extends Base {

	/**
	 * Attach WordPress/WooCommerce hooks.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function attach_hook(): void {
		add_action( 'woocommerce_set_cart_cookies', array( $this, 'init' ), 10 );
	}

	/**
	 * Initialize/refresh basket expiration based on current session expiration.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function init(): void {

		if ( null === CookieHelper::get( 'basket_binding_api_key' ) ) {
			return;
		}
		/**
		 * Get from container DI.
		 *
		 * @var CartSessionService $cart_session
		 */
		$cart_session = inpost_pay_container()->get( CartSessionService::SERVICE_KEY );

		/**
		 * Get from container DI.
		 *
		 * @var CartSessionRepository $cart_session_repository
		 */
		$cart_session_repository = inpost_pay_container()->get( CartSessionRepository::SERVICE_KEY );

		$session = $cart_session->get_session_by_basket_binding_api_key( CookieHelper::get( 'basket_binding_api_key' ) );

		if ( null === $session ) {
			CookieHelper::delete( 'basket_binding_api_key' );
			InPostIzi::getStorage()->eraseSession( 'basket_binding_api_key' );
			add_action( 'shutdown', array( BasketIdentification::class, 'drop' ) );

			return;
		}

		$expiration = Woo_Commerce_Session_Helper::get_session_expiration_time();
		CookieHelper::update_basket_binding_api_key_expiration( $expiration );
		$session->set_session_expiry( $expiration );
		$cart_session_repository->save( $session );
	}
}
