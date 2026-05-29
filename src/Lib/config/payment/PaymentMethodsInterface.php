<?php
/**
 * Payment methods interface.
 *
 * @package Ilabs\Inpost_Pay\Lib\config\payment
 */

declare( strict_types=1 );

namespace Ilabs\Inpost_Pay\Lib\config\payment;

/**
 * Interface PaymentMethodsInterface
 *
 * Defines constants for the enabled payment methods configuration option.
 */
interface PaymentMethodsInterface {
	public const IZI_PAYMENT_METHODS = 'izi_payment_methods';

	public const IZI_PAYMENT_METHODS_DEFAULT = array(
		'CARD',
		'CARD_TOKEN',
		'APPLE_PAY',
		'BLIK_CODE',
		'BLIK_TOKEN',
		'GOOGLE_PAY',
	);
}
