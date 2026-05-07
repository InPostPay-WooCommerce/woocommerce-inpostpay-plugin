<?php

namespace Ilabs\Inpost_Pay\WooCommerce;

use Ilabs\Inpost_Pay\Lib\InPostIzi;
use Ilabs\Inpost_Pay\Logger;

class WooCommerceInPostIzi extends InPostIzi {

	public function getBasket() {
		return WooCommerceBasket::getBasket();
	}
}
