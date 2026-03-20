<?php

namespace Ilabs\Inpost_Pay\Lib\omnibus;

use DateInterval;
use DateTime;
use Exception;

class Price_Service {

	/**
	 * @throws Exception
	 */
	public function find_lowest_price_before_discount(
		array $prices,
		DateTime $discount_date
	): ?Price_Model {

		if ( empty( $prices ) ) {
			return null;
		}
		$interval                    = new DateInterval( 'P30D' );
		$discount_date_minus_30_days = new DateTime();
		$discount_date_minus_30_days->setTimestamp( $discount_date->getTimestamp() );
		$discount_date_minus_30_days->sub( $interval );
		$prices = array_reverse( $prices );

		$lowest_price_obj = ( new Price_Model_Factory() )->create( $prices[0] );
		foreach ( $prices as $k => $row ) {
			if ( 0 === $k ) {
				continue;
			}

			$price_obj = ( new Price_Model_Factory() )->create( $row );

			if ( $price_obj->get_date_time() < $discount_date_minus_30_days ) {
				break;
			}

			if ( $price_obj->get_price_float() < $lowest_price_obj->get_price_float() ) {
				$lowest_price_obj = $price_obj;
			}

		}


		return $lowest_price_obj;
	}

	public function is_not_purchasable_for_the_last_31_days(
		array $prices,
		DateTime $discount_date
	): bool {

		if ( empty( $prices ) ) {
			return false;
		}

		$return = false;

		$interval                    = new DateInterval( 'P31D' );
		$discount_date_minus_31_days = new DateTime();
		$discount_date_minus_31_days->setTimestamp( $discount_date->getTimestamp() );
		$discount_date_minus_31_days->sub( $interval );
		$prices = array_reverse( $prices );

		foreach ( $prices as $k => $row ) {
			if ( 0 === $k ) {
				continue;
			}

			$price_obj = ( new Price_Model_Factory() )->create( $row );

			if ( $price_obj->is_purchasable() || $price_obj->is_purchasable_status_unknown() ) {
				return false;
			}

			if ( $price_obj->get_date_time() <= $discount_date_minus_31_days ) {
				//If we are here, then we know that the product WAS NOT available for purchase in the last 31 days
				$return = true;
				break;
			}
		}

		return $return;
	}
}
