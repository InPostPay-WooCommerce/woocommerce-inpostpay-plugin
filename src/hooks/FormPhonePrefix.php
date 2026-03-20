<?php
/**
 * Form Phone Prefix Hook.
 *
 * @package InpostPay
 * @subpackage Hooks
 */

namespace Ilabs\Inpost_Pay\hooks;

/**
 * Class FormPhonePrefix
 *
 * Handles phone prefix functionality in checkout forms.
 *
 * @todo Maybe to remove.
 */
class FormPhonePrefix extends Base {

	/**
	 * Attach hooks.
	 *
	 * @return void
	 */
	public function attach_hook(): void {
		add_filter( 'woocommerce_checkout_fields', array( $this, 'add_billing_phone_prefix' ) );
		add_action( 'woocommerce_checkout_process', array( $this, 'alter_phone_number' ) );
	}
	/**
	 * Add a billing phone prefix field.
	 *
	 * @param array $fields Checkout fields.
	 *
	 * @return array Modified checkout fields.
	 */
	public function add_billing_phone_prefix( array $fields ): array {
		$fields['billing']['billing_phone_prefix'] = array(
			'type'        => 'tel',
			'label'       => 'Prefiks kraju',
			'placeholder' => '48',
			'priority'    => 99,
			'class'       => array(
				0 => 'form-row-first',
			),
			'required'    => true,
		);

		$fields['billing']['billing_phone']['class'][0] = 'form-row-last';

		return $fields;
	}

	/**
	 * Alter the phone number with a prefix.
	 *
	 * @return void
	 */
	public function alter_phone_number(): void {
		check_admin_referer( 'woocommerce-process_checkout' );
		$phone_prefix           = isset( $_POST['billing_phone_prefix'] ) ? sanitize_text_field( wp_unslash( $_POST['billing_phone_prefix'] ) ) : '';
		$phone                  = isset( $_POST['billing_phone'] ) ? sanitize_text_field( wp_unslash( $_POST['billing_phone'] ) ) : '';
		$_POST['billing_phone'] = $phone_prefix . ' ' . $phone;
	}
}
