<?php

namespace Ilabs\Inpost_Pay\Integration\Basket;

use Ilabs\Inpost_Pay\Lib\item\Quantity;
use WC_Product;

class ExtendonsCompositeProductFilter implements ProductFilterInterface {


	public function quantityModificationLockIsRequired(
		WC_Product $product
	): bool {

		if ( 'compositepro' === $product->get_type() ) {
			return true;
		}

		return false;
	}
}
