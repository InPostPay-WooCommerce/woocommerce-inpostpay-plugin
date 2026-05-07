<?php

namespace Ilabs\Inpost_Pay\models;

class DeliveryMethodTaxableStatus {


	private string $deliveryTypeCode;
	private bool $isTaxable;


	/**
	 * @param string $deliveryTypeCode
	 * @param bool   $isTaxable
	 */
	public function __construct(
		string $deliveryTypeCode,
		bool $isTaxable
	) {

		$this->deliveryTypeCode = $deliveryTypeCode;
		$this->isTaxable        = $isTaxable;
	}


	public function getDeliveryTypeCode(): string {
		return $this->deliveryTypeCode;
	}

	public function isTaxable(): bool {
		return $this->isTaxable;
	}
}
