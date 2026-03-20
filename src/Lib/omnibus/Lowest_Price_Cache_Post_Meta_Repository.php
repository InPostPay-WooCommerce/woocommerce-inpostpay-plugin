<?php

namespace Ilabs\Inpost_Pay\Lib\omnibus;

use DateInterval;
use DateTime;

class Lowest_Price_Cache_Post_Meta_Repository implements Lowest_Price_Cache_Repository_Interface {

	public function update( Price_Model $price, int $product_id ) {
		update_post_meta( $product_id, $this->get_post_meta_key(),
			$price->to_array() );
	}

	public function get( int $product_id ): ?Price_Model {
		( new Product_Integrity() )->handle_check_integrity( $product_id );
		$result = get_post_meta( $product_id, $this->get_post_meta_key(),
			true );

		if ( ! is_array( $result ) ) {
			return null;
		}

		$result = ( new Price_Model_Factory() )->create( $result );

		if ( ! $result instanceof Price_Model ) {
			return null;
		}

		return $result;
	}

	public function get_post_meta_key(): string {
		return inpost_pay()->get_omnibus()->add_slug_prefix( 'lowest_price_cache' );
	}

	public function is_price_outdated( Price_Model $price_model ): bool {
		return $this->is_date_difference_greater_than_30_Days( $price_model->get_date_time() );
	}

	private function is_date_difference_greater_than_30_Days( DateTime $date
	): bool {
		$currentDateTime    = new DateTime();
		$dateDifference     = $currentDateTime->diff( $date );
		$thirtyDaysInterval = new DateInterval( 'P30D' );

		return $dateDifference->days > $thirtyDaysInterval->d;
	}
}
