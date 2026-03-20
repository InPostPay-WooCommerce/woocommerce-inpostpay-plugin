<?php

namespace Ilabs\Inpost_Pay\Integration\Shipping;

class GenericIntegration extends AbstractShippingMethodIntegration
	implements ShippingMethodIntegrationInterface {

	public function __construct(
		string $iziDeliveryMethodId
	) {
		$this->iziDeliveryMethodId = $iziDeliveryMethodId;
	}
}
