<?php

namespace Ilabs\Inpost_Pay\Lib\config\payment;

interface PaymentMethodsInterface {
	public const IZI_PAYMENT_METHODS = 'izi_payment_methods';

	public const IZI_PAYMENT_METHODS_DEFAULT = [
		"CARD",
		"CARD_TOKEN",
		"APPLE_PAY",
		"BLIK_CODE",
		"BLIK_TOKEN",
		"GOOGLE_PAY"
	];
}
