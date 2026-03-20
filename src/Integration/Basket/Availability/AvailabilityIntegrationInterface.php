<?php

namespace Ilabs\Inpost_Pay\Integration\Basket\Availability;

use WC_Product;

interface AvailabilityIntegrationInterface {
	/**
	 * @param array|WC_Product $cart_item
	 */
	public function __construct( $cart_item );

	public function checkAvailability();
}
