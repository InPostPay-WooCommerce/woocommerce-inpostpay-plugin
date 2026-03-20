<?php

namespace Ilabs\Inpost_Pay\Integration\Basket\Quantity;

class GenericQuantityIntegration extends AbstractQuantityIntegration {

	public function __construct( \WC_Product $product ) {
		parent::__construct( $product );
	}
}
