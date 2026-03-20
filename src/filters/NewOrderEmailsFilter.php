<?php
/**
 * Handles email notifications for new orders with InPost Pay payment method.
 *
 * @package   Inpost_Pay
 * @author    Ilabs
 * @since     2.0.1
 */

namespace Ilabs\Inpost_Pay\filters;

use Ilabs\Inpost_Pay\Lib\Payment\Inpost_Pay_Virtual_Payment_Gateway;

/**
 * Manages the delivery of order-related emails for orders processed via the InPost Pay payment method.
 *
 * This class controls whether email notifications for new orders and customer processing orders are sent
 * when the order uses the InPost Pay payment method. By default, such emails are blocked to prevent
 * unnecessary or redundant notifications. The behavior can be modified using the provided methods.
 *
 * The class registers WordPress filters to disable email delivery for orders using the InPost Pay method,
 * unless explicitly disabled via the `disable_Email_Block` method.
 *
 * @package   Inpost_Pay
 */
class NewOrderEmailsFilter extends Base {

	/**
	 * Whether email sending is currently blocked.
	 *
	 * @var bool
	 */
	private bool $block_emails = true;

	/**
	 * Disable blocking of order-related emails.
	 * Disables email blocking, allowing all WooCommerce emails to be sent.
	 *
	 * @since 2.0.1
	 * @return void
	 */
	public function disable_email_block(): void {
		$this->block_emails = false;
	}

	/**
	 * Enables email blocking for specific payment methods..
	 *
	 * By default, emails for orders with InPost Pay payment method are blocked.
	 * This method enables the blocking of emails, so they will not be sent to customers.
	 *
	 * @since 2.0.1
	 * @return void
	 */
	public function enable_email_block(): void {
		$this->block_emails = true;
	}

	/**
	 * Registers filters to block order-related emails for InPost Pay payment method.
	 *
	 * This function registers two filters:
	 * - `woocommerce_email_enabled_customer_processing_order`
	 * - `woocommerce_email_enabled_new_order`
	 *
	 * The filters block emails for orders with InPost Pay payment method, unless the `disable_Email_Block` method is called.
	 *
	 * @since 2.0.1
	 *
	 * @return void
	 */
	public function register_filters(): void {
		add_filter(
			'woocommerce_email_enabled_customer_processing_order',
			function ( $enabled, $order ) {
				if ( false === $this->block_emails ) {
					return $enabled;
				}

				if ( $order instanceof \WC_Order && in_array( $order->get_payment_method(), array( 'inpost-izi', Inpost_Pay_Virtual_Payment_Gateway::INPOST_PAY_VIRTUAL_PAYMENT_GATEWAY_ID ), true ) ) {
					return false;
				}

				return $enabled;
			},
			10,
			2
		);

		add_filter(
			'woocommerce_email_enabled_new_order',
			function ( $enabled, $order ) {
				if ( false === $this->block_emails ) {
					return $enabled;
				}

				if ( $order instanceof \WC_Order && in_array( $order->get_payment_method(), array( 'inpost-izi', Inpost_Pay_Virtual_Payment_Gateway::INPOST_PAY_VIRTUAL_PAYMENT_GATEWAY_ID ), true ) ) {
					return false;
				}

				return $enabled;
			},
			10,
			2
		);
	}
}
