<?php

namespace Ilabs\Inpost_Pay\Integration\Basket\Availability;

class ProductIsEmptyException extends \Exception {
	/**
	 * @param $cart_item
	 */
	public function __construct( $cart_item ) {
		parent::__construct( 'Product is empty in cart item ' . $cart_item['key'] );
	}
}
