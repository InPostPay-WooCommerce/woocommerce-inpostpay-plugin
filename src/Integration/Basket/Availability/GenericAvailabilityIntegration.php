<?php

namespace Ilabs\Inpost_Pay\Integration\Basket\Availability;

class GenericAvailabilityIntegration extends AbstractAvailabilityIntegration {

	public function __construct( $cart_item ) {
			parent::__construct( $cart_item );
	}

	public function isInStock(): bool {
		return parent::isInStock();
	}
}
