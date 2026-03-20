<?php

namespace Ilabs\Inpost_Pay\Lib\omnibus;

use DateTime;

interface Price_Repository_Interface {

	public function push( Price_Model $price, int $post_id );

	/**
	 * @return Price_Model[]
	 */
	public function get_all_prices_arr(int $product_id): ?array;

	public function get_last_price(int $product_id): ?Price_Model;

	public function get_lowest_price($product_id, DateTime $discount_date_time): ?Price_Model;
}
