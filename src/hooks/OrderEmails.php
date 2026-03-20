<?php
/**
 * Handles order-related email customizations for InPost Pay.
 *
 * @package    InPost Pay
 * @subpackage hooks
 * @since      2.0.1
 * @author     iLabs
 */

namespace Ilabs\Inpost_Pay\hooks;

use Ilabs\Inpost_Pay\filters\NewOrderEmailsFilter;
use Ilabs\Inpost_Pay\Lib\OrderAliasHelper;
use Ilabs\Inpost_Pay\Lib\Payment\Inpost_Pay_Virtual_Payment_Gateway;
use Ilabs\Inpost_Pay\Lib\Utils\OrderRecipientResolver;
use WC_Order;

/**
 * Handles order-related email customizations for InPost Pay.
 *
 * @package Ilabs\Inpost_Pay\hooks
 * @since 2.0.1
 */
class OrderEmails extends Base {

	/**
	 * Email filter instance.
	 *
	 * @var NewOrderEmailsFilter
	 */
	private NewOrderEmailsFilter $email_filter;

	/**
	 * Constructs the object.
	 *
	 * @param NewOrderEmailsFilter $email_filter Instance of NewOrderEmails.
	 */
	public function __construct( NewOrderEmailsFilter $email_filter ) {
		$this->email_filter = $email_filter;
	}

	/**
	 * Attaches the filter to modify the recipient of order-related emails.
	 *
	 * This function attaches the `overrideRecipient` filter to the following filters:
	 * - `woocommerce_email_recipient_customer_failed_order`
	 * - `woocommerce_email_recipient_customer_on_hold_order`
	 * - `woocommerce_email_recipient_customer_processing_order`
	 * - `woocommerce_email_recipient_customer_completed_order`
	 * - `woocommerce_email_recipient_customer_refunded_order`
	 * - `woocommerce_email_recipient_customer_invoice`
	 * - `woocommerce_email_recipient_customer_note`
	 *
	 * Additionally, if the order has downloadable products, the `overrideRecipientDownloadable` filter is attached to the `woocommerce_email_recipient_customer_completed_order` filter.
	 *
	 * This function also attaches the `send_create_order_email` function to the `inpost_pay_order_created` action.
	 *
	 * @since 2.0.1
	 */
	public function attach_hook(): void {
		$email_filters = array(
			'woocommerce_email_recipient_customer_failed_order',
			'woocommerce_email_recipient_customer_on_hold_order',
			'woocommerce_email_recipient_customer_processing_order',
			'woocommerce_email_recipient_customer_completed_order',
			'woocommerce_email_recipient_customer_refunded_order',
			'woocommerce_email_recipient_customer_invoice',
			'woocommerce_email_recipient_customer_note',
		);

		foreach ( $email_filters as $filter ) {
			add_filter( $filter, array( $this, 'override_recipient' ), 10, 2 );
			if ( 'woocommerce_email_recipient_completed_order' === $filter ) {
				add_filter( $filter, array( $this, 'override_recipient_downloadable' ), 10, 2 );
			}
		}

		add_action( 'inpost_pay_order_created', array( $this, 'send_create_order_email' ), 10, 2 );
		add_action( 'inpost_pay_order_updated', array( $this, 'send_order_email_on_update' ), 10, 2 );
	}

	/**
	 * Sends the WooCommerce new order emails for InPost Pay.
	 * Send On Hold email on the order status is on-hold or pending.
	 * Send Processing email on the order status is processing.
	 * Send New Order email on the order status is new.
	 *
	 * Called when the order is created with the payment method 'inpost-izi'.
	 *
	 * @param int|string $order_id The ID of the order.
	 * @param array      $data The data passed to the hook.
	 * @since 2.0.1
	 */
	public function send_create_order_email( $order_id, $data ): void {
		if ( is_string( $order_id ) ) {
			$order = OrderAliasHelper::resolve( $order_id );
			if ( ! $order instanceof WC_Order ) {
				return;
			}
		} else {
			$order = wc_get_order( $order_id );
		}

		if ( ! ( $order instanceof WC_Order ) ) {
			return;
		}

		if ( ! in_array( $order->get_payment_method(), array( 'inpost-izi', Inpost_Pay_Virtual_Payment_Gateway::INPOST_PAY_VIRTUAL_PAYMENT_GATEWAY_ID ), true ) ) {
			return;
		}

		$this->email_filter->disable_email_block();
		$this->send_order_emails( $order, $order->get_id(), true );
		$this->email_filter->enable_email_block();
	}


	/**
	 * Sends the appropriate WooCommerce emails based on order status.
	 *
	 * @param WC_Order   $order Order instance.
	 * @param string|int $order_id Order ID (maybe aliased).
	 * @param bool       $new_order If new order created set to true.
	 *
	 * @return void
	 */
	private function send_order_emails( WC_Order $order, $order_id, bool $new_order = false ): void {
		$mailer = WC()->mailer();

		if ( in_array( $order->get_status(), array( 'on-hold', 'pending' ), true ) ) {
			if ( isset( $mailer->emails['WC_Email_Customer_On_Hold_Order'] ) ) {
				$on_hold_email = $mailer->emails['WC_Email_Customer_On_Hold_Order'];

				if ( is_object( $on_hold_email ) && method_exists( $on_hold_email, 'is_enabled' ) && $on_hold_email->is_enabled() ) {
					$on_hold_email->trigger( $order_id );
				}
			}
		}

		if ( 'processing' === $order->get_status() ) {
			if ( isset( $mailer->emails['WC_Email_Customer_Processing_Order'] ) ) {
				$processing_email = $mailer->emails['WC_Email_Customer_Processing_Order'];

				if ( is_object( $processing_email ) && method_exists( $processing_email, 'is_enabled' ) && $processing_email->is_enabled() ) {
					$processing_email->trigger( $order_id );
				}
			}
		}

		if ( $new_order && isset( $mailer->emails['WC_Email_New_Order'] ) ) {
			$new_order_email = $mailer->emails['WC_Email_New_Order'];

			if ( is_object( $new_order_email ) && method_exists( $new_order_email, 'is_enabled' ) && $new_order_email->is_enabled() ) {
				$new_order_email->trigger( $order_id );
			}
		}
	}

	/**
	 * Sends the WooCommerce order emails for InPost Pay on update form API.
	 * Send On Hold email on the order status is on-hold or pending.
	 * Send Processing email on the order status is processing.
	 *
	 * @param int|string $order_id Order ID.
	 * @param array      $data     Additional data from the InPost Pay integration.
	 *
	 * @return void
	 */
	public function send_order_email_on_update( $order_id, $data ): void {

		if ( is_string( $order_id ) ) {
			$order = OrderAliasHelper::resolve( $order_id );
		} else {
			$order = wc_get_order( $order_id );
		}

		if ( ! ( $order instanceof WC_Order ) ) {
			return;
		}

		if ( ! in_array( $order->get_payment_method(), array( 'inpost-izi', Inpost_Pay_Virtual_Payment_Gateway::INPOST_PAY_VIRTUAL_PAYMENT_GATEWAY_ID ), true ) ) {
			return;
		}

		$this->email_filter->disable_email_block();
		$this->send_order_emails( $order, $order->get_id() );
		$this->email_filter->enable_email_block();
	}

	/**
	 * Conditionally overrides the recipient of an email based on order content.
	 *
	 * @param string   $recipient Email recipient.
	 * @param WC_Order $order     Order instance.
	 *
	 * @return string Filtered recipient.
	 */
	public function maybe_override_recipient( $recipient, $order ): string {
		if ( ! ( $order instanceof WC_Order ) ) {
			return $recipient;
		}

		if ( true === self::has_downloadable_products( $order ) ) {
			return $this->override_recipient_downloadable( $recipient, $order );
		}

		return $this->override_recipient( $recipient, $order );
	}

	/**
	 * Checks whether the order contains downloadable products.
	 *
	 * @param WC_Order $order Order instance.
	 *
	 * @return bool True if the order has downloadable products, false otherwise.
	 */
	private static function has_downloadable_products( WC_Order $order ): bool {

		foreach ( $order->get_items() as $item ) {
			$product = $item->get_product();

			if ( $product && true === $product->is_downloadable() ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Overrides the email recipient for downloadable orders.
	 *
	 * @param string   $recipient Email recipient.
	 * @param WC_Order $order     Order instance.
	 *
	 * @return string Filtered recipient.
	 */
	public function override_recipient_downloadable( $recipient, $order ): string {
		if ( ! ( $order instanceof WC_Order ) ) {
			return $recipient;
		}

		return OrderRecipientResolver::resolve_recipients( $order, true );
	}

	/**
	 * Overrides the email recipient for physical orders.
	 *
	 * @param string   $recipient Email recipient.
	 * @param WC_Order $order     Order instance.
	 *
	 * @return string Filtered recipient.
	 */
	public function override_recipient( $recipient, $order ): string {
		if ( ! ( $order instanceof WC_Order ) ) {
			return $recipient;
		}

		return OrderRecipientResolver::resolve_recipients( $order );
	}
}
