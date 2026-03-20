<?php
/**
 * Payment methods configuration fetcher.
 *
 * @package Ilabs\Inpost_Pay\Lib\config\payment
 */

namespace Ilabs\Inpost_Pay\Lib\config\payment;

use Ilabs\Inpost_Pay\EntityLayer\Cache\PersistentCache;
use Ilabs\Inpost_Pay\Lib\Connection;

/**
 * Fetches available IZI payment methods and caches them.
 */
class PaymentMethodsGet extends Connection {

	/**
	 * Cache key for payment methods.
	 *
	 * @var string
	 */
	public const CACHE_KEY = 'inpost_pay_payment_methods';

	/**
	 * Get payment methods list.
	 *
	 * @return array
	 */
	public function get(): array {
		$cache           = new PersistentCache();
		$payment_methods = $cache->get( self::CACHE_KEY );
		if ( is_array( $payment_methods ) ) {
			return $payment_methods;
		}

		$payment_methods = $this->request( 'v1/izi/payment-methods' );
		if ( isset( $payment_methods->payment_type ) ) {
			$cache->set( self::CACHE_KEY, $payment_methods->payment_type, DAY_IN_SECONDS );

			return $payment_methods->payment_type;
		}

		return PaymentMethodsInterface::IZI_PAYMENT_METHODS_DEFAULT;
	}
}
