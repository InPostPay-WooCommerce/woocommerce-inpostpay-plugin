<?php

namespace Ilabs\Inpost_Pay\Integration\Basket\Availability;

class ProductExtrasForWoocommerceAvailabilityIntegration extends AbstractAvailabilityIntegration {
	public function __construct( $cart_item ) {
		parent::__construct( $cart_item );
	}

	public function checkAvailability(): bool {

		if ( $this->isChild() ) {
			return $this->isInStock();
		}

		return parent::checkAvailability();
	}

	private function isChild(): bool {

		if ( isset( $this->cart_item['product_extras']['products']['child_field'] )
		     && $this->cart_item['product_extras']['products']['child_field'] === 1 ) {
			return true;
		}

		return false;
	}
}
