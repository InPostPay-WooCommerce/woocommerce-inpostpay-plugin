<?php

namespace Ilabs\Inpost_Pay\Lib\config\ShippingCost;

interface ShippingMappingFieldInterface {

	public function getDeliveryOptionCode(): string;
}
