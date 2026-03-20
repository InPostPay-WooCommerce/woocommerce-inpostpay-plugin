<?php

namespace Ilabs\Inpost_Pay\Integration\Basket;

use Ilabs\Inpost_Pay\Lib\item\Quantity;
use WC_Product;

class SmartCompositeProductFilter implements ProductFilterInterface {


	public function quantityModificationLockIsRequired(
		WC_Product $product
	): bool {

		//todo add checking for this plugin

		return false;
	}
}
