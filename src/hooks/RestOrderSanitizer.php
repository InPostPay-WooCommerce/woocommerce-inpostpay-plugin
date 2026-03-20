<?php
/**
 * REST Order Sanitizer Hook.
 *
 * @package InpostPay
 * @subpackage Hooks
 */

namespace Ilabs\Inpost_Pay\hooks;

use WC_Order;

/**
 * Class RestOrderSanitizer
 *
 * Sanitizes order data for REST API.
 */
class RestOrderSanitizer extends Base {

	/**
	 * Attach hooks.
	 *
	 * @return void
	 */
	public function attach_hook(): void {
		add_action( 'inpost_pay_order_created', array( $this, 'sanitizeOrder' ), 5, 2 );
	}

	/**
	 * Sanitize order.
	 *
	 * @param int   $order_id The order ID.
	 * @param mixed $data     The order data.
	 *
	 * @return void
	 */
	public function sanitizeOrder( int $order_id, $data ): void {
		$order = new WC_Order( $order_id );

		if ( ! $order instanceof WC_Order || $order->get_payment_method() !== 'inpost-izi' ) {
			return;
		}

		$inpost_mail   = $order->get_meta( '_inpost_delivery_mail' );
		$original_mail = $order->get_billing_email();

		if ( is_email( $inpost_mail ) ) {
			if ( ! $order->get_meta( '_original_user_email' ) ) {
				$order->update_meta_data( '_original_user_email', $original_mail );
			}
			$order->set_billing_email( sanitize_email( $inpost_mail ) );
			$order->save();
		}
	}
}
