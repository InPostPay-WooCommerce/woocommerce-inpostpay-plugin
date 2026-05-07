<?php

namespace Ilabs\Inpost_Pay\Integration\Shipping;

use WC_Order;

interface ShippingMethodIntegrationInterface {

	public function configure();

	public function getIziDeliveryMethodId(): string;

	public function setWcOrder( WC_Order $order );

	public function filterTotal( callable $callable, bool $isTaxed, object $basketPriceObj );
}
