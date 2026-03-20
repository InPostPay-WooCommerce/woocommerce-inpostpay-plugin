<?php
/**
 * Integration: WP Desk Helper.
 *
 * Provides helper methods for integrating with WP Desk InPost plugin.
 *
 * @package Inpost_Pay
 */

namespace Ilabs\Inpost_Pay\Integration\WPDesk;

use Ilabs\Inpost_Pay\Logger;
use WC_Order;

/**
 * Class WPDeskHelper
 *
 * @since 1.0.0
 */
class WPDeskHelper {

	/**
	 * Check if WP Desk InPost plugin is active.
	 *
	 * @return bool True if plugin is active, false otherwise.
	 * @since 1.0.0
	 */
	public static function isActiveInPostPlugin(): bool {
		return class_exists( 'WPDesk_Paczkomaty_Plugin' );
	}

	/**
	 * Filter _paczkomat_cod_amount when WP Desk retrieves it via $order->get_meta().
	 *
	 * @param mixed    $value   The meta value.
	 * @param WC_Order $order   WooCommerce order object.
	 * @param string   $key     Meta key.
	 * @param bool     $single  Whether to return single value.
	 * @param string   $context Context (view or edit).
	 *
	 * @return mixed Filtered meta value.
	 */
	public static function filterCodAmountOnGetMeta( $value, $order, $key, $single, $context ) {
		if ( '_paczkomat_cod_amount' !== $key ) {
			return $value;
		}

		if ( ! is_admin() ) {
			return $value;
		}

		if ( ! $order instanceof WC_Order || 'inpost-izi' !== $order->get_payment_method() ) {
			return $value;
		}

		$actual_total = $order->get_total();

		if ( empty( $value ) || (float) $value !== (float) $actual_total ) {
			Logger::log(
				sprintf(
					'[WPDeskCodAmountFix] Forcing COD amount. Order %d | Old: %s | New: %s',
					$order->get_id(),
					empty( $value ) ? 'EMPTY' : (string) $value,
					(string) $actual_total
				)
			);

			return $single ? $actual_total : [ $actual_total ];
		}

		return $value;
	}
}
