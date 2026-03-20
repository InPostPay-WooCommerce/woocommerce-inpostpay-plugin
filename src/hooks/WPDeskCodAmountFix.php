<?php
/**
 * Hook: WPDesk COD Amount Fix.
 *
 * Ensures that _paczkomat_cod_amount meta is always returned with correct value
 * for InPost Pay orders by intercepting WooCommerce meta retrieval.
 *
 * @package Inpost_Pay
 */

namespace Ilabs\Inpost_Pay\hooks;

use Ilabs\Inpost_Pay\Integration\WPDesk\WPDeskHelper;

/**
 * Class WPDeskCodAmountFix
 */
class WPDeskCodAmountFix extends Base {

	/**
	 * Attach hooks for fixing COD amount in WP Desk.
	 *
	 * @return void
	 */
	public function attach_hook(): void {
		if ( false === WPDeskHelper::isActiveInPostPlugin() ) {
			return;
		}

		add_filter( 'woocommerce_order_get_meta', array( WPDeskHelper::class, 'filterCodAmountOnGetMeta' ), 999, 5 );
	}
}
