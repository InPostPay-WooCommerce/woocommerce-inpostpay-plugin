<?php
/**
 * Front Session Cleanup Hook.
 *
 * @package InpostPay
 * @subpackage Hooks/Front
 */

declare( strict_types=1 );

namespace Ilabs\Inpost_Pay\hooks\front;

use Ilabs\Inpost_Pay\Lib\helpers\CookieHelper;
use Ilabs\Inpost_Pay\Logger;
use Ilabs\Inpost_Pay\models\CartSessionService;
use function Ilabs\Inpost_Pay\inpost_pay_container;

/**
 * Class FrontSessionCleanup
 *
 * Handles session cleanup on the frontend.
 */
class FrontSessionCleanup extends FrontBase {

	private static bool $cleanup_in_progress = false;
	private static bool $cleanup_done        = false;

	/**
	 * Attach hooks.
	 *
	 * @return void
	 */
	public function attach_hook(): void {
		add_action( 'woocommerce_set_cart_cookies', array( $this, 'cleanup_on_session_change' ), 10, 1 );
	}

	/**
	 * Cleanup on session change.
	 *
	 * @param mixed $set The set parameter.
	 *
	 * @return void
	 */
	public function cleanup_on_session_change( $set ): void {
		if ( self::$cleanup_done ) {
			return;
		}

		if ( defined( 'REST_REQUEST' ) && REST_REQUEST ) {
			return;
		}

		if ( wp_doing_ajax() && ! $this->is_cart_ajax() ) {
			return;
		}

		if ( ! function_exists( 'WC' ) || ! WC()->session ) {
			return;
		}

		if ( self::$cleanup_in_progress ) {
			return;
		}

		$new_session_cookie = WC()->session->get_session_cookie();
		$old_session_id     = CookieHelper::get( 'wp_woocommerce_session_' . COOKIEHASH );

		if ( ! $old_session_id || empty( $new_session_cookie ) ) {
			return;
		}

		$new_session_id = implode( '|', $new_session_cookie );

		if ( $old_session_id === $new_session_id ) {
			return;
		}

		if ( $this->is_same_session( $old_session_id, $new_session_cookie ) ) {
			return;
		}

		self::$cleanup_in_progress = true;
		self::$cleanup_done        = true;

		// phpcs:ignore Squiz.PHP.CommentedOutCode.Found,Squiz.Commenting.InlineComment.InvalidEndChar
		// Logger::log( "[SessionCleanup] Session changed: {$old_session_id} → {$new_session_id}" ).

		/**
		 * Get from container DI.
		 *
		 * @var CartSessionService $cart_session
		 */
		$cart_session = inpost_pay_container()->get( CartSessionService::SERVICE_KEY );

		$old_cart_id = $cart_session->get_session_by_wc_session_id( $old_session_id );
		if ( $old_cart_id ) {
			$cart_session->remove_basket_binding_api_key( $old_cart_id );
			Logger::log( "[SessionCleanup] Removed binding for old cart_id={$old_cart_id}" );
		}

		CookieHelper::delete( 'izi_basket' );

		self::$cleanup_in_progress = false;
	}

	/**
	 * Check if request is cart AJAX.
	 *
	 * @return bool True if cart AJAX.
	 */
	private function is_cart_ajax(): bool {
		$allowed_actions = array(
			'woocommerce_update_order_review',
			'woocommerce_apply_coupon',
			'woocommerce_remove_coupon',
			'woocommerce_update_shipping_method',
		);

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$action = isset( $_REQUEST['action'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['action'] ) ) : '';

		return in_array( $action, $allowed_actions, true );
	}

	/**
	 * Compare session by stable identifiers (user_id + hash).
	 * Ignores timestamps which change frequently.
	 *
	 * @param string $old_session_id     The old session ID.
	 * @param array  $new_session_cookie The new session cookie.
	 *
	 * @return bool True if same session.
	 */
	private function is_same_session( string $old_session_id, array $new_session_cookie ): bool {
		$old_parts = explode( '|', $old_session_id );

		$old_user_id = $old_parts[0] ?? '';
		$old_hash    = $old_parts[3] ?? '';

		$new_user_id = $new_session_cookie[0] ?? '';
		$new_hash    = $new_session_cookie[3] ?? '';

		return $old_user_id === $new_user_id && $old_hash === $new_hash;
	}
}
