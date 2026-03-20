<?php
/**
 * Virtual Payment Method Utils
 *
 * @package Ilabs\Inpost_Pay\Lib\Payment
 * @since 2.0.6
 */

namespace Ilabs\Inpost_Pay\Lib\Payment;

use Ilabs\Inpost_Pay\Lib\config\payment\Virtual_Payment_Gateway_Config;


/**
 * Class Virtual_Payment_Method_Utils
 *
 * Utility class for handling virtual payment method functionality.
 *
 * @package Ilabs\Inpost_Pay\Lib\Payment
 * @since 2.0.6
 */
class Virtual_Payment_Method_Utils {
	/**
	 * Returns the payment method to be used for an order.
	 *
	 * If the virtual payment method is enabled in the configuration, it returns the virtual payment method ID.
	 * Otherwise, it returns the default payment method ID (inpost-izi).
	 *
	 * @return string The payment method ID.
	 * @since 2.0.6
	 */
	public static function get_payment_method_for_order(): string {
		if ( ( new Virtual_Payment_Gateway_Config() )->is_enabled() ) {
			add_filter(
				'inpost_pay_virtual_payment_method_is_hide',
				static function ( $is_hide ) {
					return false;
				}
			);
			return Inpost_Pay_Virtual_Payment_Gateway::INPOST_PAY_VIRTUAL_PAYMENT_GATEWAY_ID;
		}
		return 'inpost-izi';
	}

	/**
	 * Registers the virtual payment method.
	 *
	 * If the virtual payment method is enabled in the configuration, it registers the virtual payment gateway.
	 *
	 * @since 2.0.6
	 */
	public function register_virtual_payment_method(): void {
		if ( ! ( new Virtual_Payment_Gateway_Config() )->is_enabled() ) {
			return;
		}
		add_filter(
			'woocommerce_payment_gateways',
			array( $this, 'inpost_register_virtual_payment_gateway' )
		);
	}

	/**
	 * Registers the virtual payment gateway
	 *
	 * @param array $gateways Available gateways.
	 * @return array
	 * @since 2.0.6
	 */
	public function inpost_register_virtual_payment_gateway( array $gateways ): array {
		$gateways[] = Inpost_Pay_Virtual_Payment_Gateway::class;
		return $gateways;
	}
}
