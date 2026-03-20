<?php

namespace Ilabs\Inpost_Pay\Lib\omnibus;

use WC_Product;
use WC_Product_Variation;

class Product_Helper {

	public function is_purchasable( WC_Product $product ): bool {
		$is_purchasable            = $product->is_purchasable();
		$is_in_stock               = $product->is_in_stock();
		$is_post_publicly_viewable = ( is_post_publicly_viewable(
			$product->get_id() && ! $product instanceof WC_Product_Variation
		) );

		$return = $is_purchasable && $is_in_stock && $is_post_publicly_viewable;

		return $return;
	}
}
