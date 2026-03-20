<?php
/**
 * Front Logout Monitor Hook.
 *
 * @package InpostPay
 * @subpackage Hooks/Front
 */

declare( strict_types=1 );

namespace Ilabs\Inpost_Pay\hooks\front;

use Ilabs\Inpost_Pay\Lib\BasketIdentification;
use Ilabs\Inpost_Pay\Lib\helpers\CookieHelper;
use Ilabs\Inpost_Pay\Lib\Remote;
use Ilabs\Inpost_Pay\models\CartSessionService;
use Ilabs\Inpost_Pay\Logger;
use Ilabs\Inpost_Pay\objects\BasketBindingApiKey;
use function Ilabs\Inpost_Pay\inpost_pay_container;

/**
 * Class FrontLogoutMonitor
 *
 * Monitors logout events on the frontend.
 */
class FrontLogoutMonitor extends FrontBase {

	/**
	 * Cart session service.
	 *
	 * @var CartSessionService
	 */
	private CartSessionService $cart_session;

	/**
	 * FrontLogoutMonitor constructor.
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
		add_action( 'wp_logout', array( $this, 'handleLogout' ) );
		add_action( 'clear_auth_cookie', array( $this, 'handleLogout' ) );
		add_action( 'init', array( $this, 'remove_block_action' ) );
	}

	/**
	 * Handle logout.
	 *
	 * @return void
	 */
	public function handleLogout(): void {
		$basket_id = BasketIdentification::get();

		$current_user = wp_get_current_user();
		if ( ! $current_user || 0 === $current_user->ID ) {
			return;
		}

		 Logger::log( '[LogoutMonitor] basket id key: ' . $basket_id );

		if ( empty( $basket_id ) ) {
			 Logger::log( '[LogoutMonitor] No basket id – skip unbind call.' );

			return;
		}

		$binding_key = $this->cart_session->basket_binding_api_key( $basket_id );

		if ( empty( $binding_key ) ) {
			$binding_key = ( new BasketBindingApiKey() )->get( false );
		}

		if ( empty( $binding_key ) ) {
			 Logger::log( '[LogoutMonitor] No binding key for basket – skip unbind.' );

			return;
		}

	    Logger::log( "[LogoutMonitor] Attempt unbind bindingKey={$binding_key}" );

		$resp            = ( new Remote() )->basket_binding_delete();
		$terminal_errors = array( 'BASKET_NOT_FOUND', 'BASKET_NOT_BOUND' );
		if ( isset( $resp->error_code ) && in_array( $resp->error_code, $terminal_errors, true ) ) {
			Logger::log( "[LogoutMonitor] Basket {$basket_id} already not bound – treating as unbound." );
		}

		$this->cart_session->remove_basket_binding_api_key( $basket_id );
		( new BasketBindingApiKey() )->drop();
		CookieHelper::delete('basket_binding_api_key');

		FrontBasketChange::$block_action_set = true;
		Logger::log( "[LogoutMonitor] Basket {$basket_id} unbound on logout" );
	}

	/**
	 * Remove block action.
	 *
	 * @return void
	 */
	public function remove_block_action(): void {
		FrontBasketChange::$block_action_set = false;
	}
}
