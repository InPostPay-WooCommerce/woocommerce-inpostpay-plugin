<?php

namespace Ilabs\Inpost_Pay\Lib\omnibus;

use DateTime;
use Exception;

class Price_Post_Meta_Repository implements Price_Repository_Interface {

	public function push( Price_Model $price, int $post_id ) {
		$result = $this->get_all_prices_arr( $post_id );

		if ( ! $result ) {
			$result = [];
		}
		$result[] = $price->to_array();

		update_post_meta( $post_id, $this->get_post_meta_key(), $result );
	}

	public function get_all_prices_arr( int $product_id ): ?array {
		( new Product_Integrity() )->handle_check_integrity( $product_id );
		$result = get_post_meta( $product_id,
			$this->get_post_meta_key(),
			true );

		if ( is_array( $result ) && ! empty( $result ) ) {

			return $result;
		}

		return null;
	}

	public function get_last_price( int $product_id ): ?Price_Model {
		$result = $this->get_all_prices_arr( $product_id );
		if ( ! $result ) {
			return null;
		}

		$last_el = end( $result );
		$last_price = ( new Price_Model_Factory() )->create( $last_el );

		return $last_price instanceof Price_Model ? $last_price : null;
	}

	public function is_price_lower_than_last_price(
		Price_Model $price,
		int $product_id
	): ?bool {
		$last_price = $this->get_last_price( $product_id );

		if ( ! $last_price ) {
			return null;
		}

		return $price->get_price_float() < $last_price->get_price_float();
	}

	public function get_lowest_price(
		$product_id,
		DateTime $discount_date_time
	): ?Price_Model {
		try {
			$all_prices = $this->get_all_prices_arr( $product_id );

			if ( ! $all_prices ) {
				return null;
			}

			return ( new Price_Service() )->find_lowest_price_before_discount( $all_prices,
				$discount_date_time );
		} catch ( Exception $e ) {
			return null;
		}
	}

	public function is_not_purchasable_for_the_last_31_days(
		$product_id,
		DateTime $discount_date_time
	): bool {
		try {
			$all_prices = $this->get_all_prices_arr( $product_id );

			if ( ! $all_prices ) {
				return false;
			}

			return ( new Price_Service() )->is_not_purchasable_for_the_last_31_days( $all_prices,
				$discount_date_time );
		} catch ( Exception $e ) {
			return false;
		}
	}


	public function get_post_meta_key(): string {
		return inpost_pay()->get_omnibus()->add_slug_prefix( 'prices_history' );
	}


}
