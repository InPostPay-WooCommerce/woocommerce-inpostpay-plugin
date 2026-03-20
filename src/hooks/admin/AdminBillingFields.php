<?php
/**
 * Admin Billing Fields Hook.
 *
 * @package InpostPay
 * @subpackage Hooks/Admin
 */

namespace Ilabs\Inpost_Pay\hooks\admin;

use Ilabs\Inpost_Pay\hooks\Base;

/**
 * Class AdminBillingFields
 *
 * Handles admin billing fields customization.
 */
class AdminBillingFields extends Base {
	/**
	 * Attach hooks.
	 *
	 * @return void
	 */
	public function attach_hook() {
		add_filter(
			'woocommerce_admin_billing_fields',
			function ( $fields ) {
				$fields['invoice_note'] = array(
					'label' => 'Uwagi',
					'show'  => true,
				);
				return $fields;
			}
		);
	}
}
