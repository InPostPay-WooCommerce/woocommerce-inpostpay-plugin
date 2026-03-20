<?php
/**
 * Front Template Redirect Hook.
 *
 * @package InpostPay
 * @subpackage Hooks/Front
 */

namespace Ilabs\Inpost_Pay\hooks\front;

use Ilabs\Inpost_Pay\models\CartSessionService;
use function Ilabs\Inpost_Pay\inpost_pay_container;

/**
 * Class FrontTemplateRedirect
 *
 * Handles template redirection on the frontend.
 */
class FrontTemplateRedirect extends FrontBase {

	/**
	 * Attach hooks.
	 *
	 * @return void
	 */
	public function attach_hook() {
		add_action( 'template_redirect', array( $this, 'thankYouPage' ) );
	}

	/**
	 * Thank you page redirect.
	 *
	 * @return bool|null Returns false if no redirect, otherwise exits.
	 */
	public function thankYouPage(): ?bool {
		if ( ! isset( $_COOKIE['izi_basket_id'] ) ) {
			return false;
		}

		$cart_id = sanitize_text_field( wp_unslash( $_COOKIE['izi_basket_id'] ) );

		/**
		 * Get from container DI.
		 *
		 * @var CartSessionService $cart_session
		 */
		$cart_session = inpost_pay_container()->get( CartSessionService::SERVICE_KEY );

		if ( ! $cart_session->should_redirect( $cart_id ) ) {
			return false;
		}

		$redirect_url = $cart_session->get_redirect_url_for_template( $cart_id );

		if ( ! $redirect_url ) {
			return false;
		}

		$cart_session->set_redirected_by_id( $cart_id, 1 );

		wp_safe_redirect( $redirect_url );
		exit;
	}
}
