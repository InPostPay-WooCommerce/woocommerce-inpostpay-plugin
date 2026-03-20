<?php

namespace Ilabs\Inpost_Pay\Lib\omnibus;

interface Lowest_Price_Cache_Repository_Interface {

	public function update( Price_Model $price, int $product_id );

	public function get( int $product_id ): ?Price_Model;
}
