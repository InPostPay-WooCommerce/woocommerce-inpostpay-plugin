<?php
/**
 * Virtual Payment Method for WooCommerce
 *
 * @package    Inpost_Pay
 * @subpackage Inpost_Pay/Lib/Payment
 * @author     iLabs
 * @since 2.0.6
 */

namespace Ilabs\Inpost_Pay\Lib\Payment;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Virtual Payment Method for WooCommerce
 *
 * This class registers a new payment method gateway that can be hidden in cart and checkout.
 */
class Inpost_Pay_Virtual_Payment_Gateway extends \WC_Payment_Gateway {

	public const INPOST_PAY_VIRTUAL_PAYMENT_GATEWAY_ID = 'inpost_pay_virtual_payment_gateway';

	/**
	 * Class constructor
	 */
	public function __construct() {
		$this->id                 = self::INPOST_PAY_VIRTUAL_PAYMENT_GATEWAY_ID;
		$this->method_title       = __( 'InPostPay Virtual Payment Gateway', 'inpost-pay' );
		$this->method_description = __( 'Virtual payment gateway for InPost services.', 'inpost-pay' );
		$this->has_fields         = false;
		$this->supports           = array( 'products' );

		// Load the settings.
		$this->init_form_fields();
		$this->init_settings();

		// Define user set variables.
		$this->title       = $this->get_option( 'title' );
		$this->description = $this->get_option( 'description' );
		$this->enabled     = $this->get_option( 'enabled' );

		// Actions.
		add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
		add_filter( 'woocommerce_available_payment_gateways', array( $this, 'maybe_hide_payment_gateway' ) );
	}

	/**
	 * Initialize Gateway Settings Form Fields
	 */
	public function init_form_fields(): void {
		$this->form_fields = array(
			'enabled'     => array(
				'title'   => __( 'Enable/Disable', 'inpost-pay' ),
				'type'    => 'checkbox',
				'label'   => __( 'Enable Virtual Payment', 'inpost-pay' ),
				'default' => 'yes',
			),
			'title'       => array(
				'title'       => __( 'Title', 'inpost-pay' ),
				'type'        => 'text',
				'description' => __( 'This controls the title which the user sees during checkout.', 'inpost-pay' ),
				'default'     => __( 'InpostPay Virtual Payment', 'inpost-pay' ),
				'desc_tip'    => true,
			),
			'description' => array(
				'title'       => __( 'Description', 'inpost-pay' ),
				'type'        => 'textarea',
				'description' => __( 'This is virtual payment method. If you see this payment method, don\'t use it. Please contact to the support.', 'inpost-pay' ),
				'default'     => __( 'Do not pay using this payment method.', 'inpost-pay' ),
				'desc_tip'    => true,
			),
		);
	}

	/**
	 * Process the payment
	 *
	 * @param int $order_id Order ID.
	 * @return array
	 */
	public function process_payment( $order_id ): array {
		$order = wc_get_order( $order_id );

		// Mark as on-hold (we're awaiting the payment).
		$order->update_status( 'on-hold', __( 'Awaiting payment', 'inpost-pay' ) );

		// Reduce stock levels.
		wc_reduce_stock_levels( $order_id );

		// Remove cart.
		WC()->cart->empty_cart();

		// Return thankyou redirect.
		return array(
			'result'   => 'success',
			'redirect' => $this->get_return_url( $order ),
		);
	}

	/**
	 * Hide the payment method gateway if needed
	 *
	 * @param array $available_gateways Available gateways.
	 * @return array
	 */
	public function maybe_hide_payment_gateway( array $available_gateways ): array {
		$is_hide = true;
		/**
		 * Filters whether to hide the virtual payment method.
		 *
		 * @since 2.0.6
		 *
		 * @param bool $is_hide Whether to hide the payment method. Default true.
		 * @return bool Modified value indicating whether to hide the payment method.
		 */
		$is_hide = apply_filters(
			'inpost_pay_virtual_payment_method_is_hide',
			$is_hide
		);

		if ( ! $is_hide ) {
			return $available_gateways;
		}

		if ( is_admin() ) {
			return $available_gateways;
		}

		// Remove this payment method from available gateways.
		if ( isset( $available_gateways[ $this->id ] ) ) {
			unset( $available_gateways[ $this->id ] );
		}

		return $available_gateways;
	}
}
