<?php

namespace Ilabs\Inpost_Pay\Lib\omnibus;

use DateTime;
use Exception;

class Price_Model_Factory {

	public function create( array $data ): ?Price_Model {
		if ( isset( $data[ Price_Model::ARRAY_PRICE_KEY_0 ] )
		     && isset( $data[ Price_Model::ARRAY_DATETIME_KEY_1 ] )
		     && isset( $data[ Price_Model::ARRAY_IS_ON_SALE_KEY_2 ] )
		) {
			try {
				$price = floatval( wc_format_decimal( $data[ Price_Model::ARRAY_PRICE_KEY_0 ],
					2 ) );

				$date_time = $this->create_date_time( $data[ Price_Model::ARRAY_DATETIME_KEY_1 ] );
				$is_purchasable = $this->get_is_purchasable_from_data( $data );

				return new Price_Model( $price,
					$date_time,
					$data[ Price_Model::ARRAY_IS_ON_SALE_KEY_2 ],
					$is_purchasable
				);
			} catch ( Exception $e ) {
				return null;
			}
		}

		return null;
	}

	/**
	 * @throws Exception
	 */
	private function create_date_time( string $date ): DateTime {
		return new DateTime( $date );
	}

	private function get_is_purchasable_from_data( array $data ): int {
		if ( ! isset( $data[ Price_Model::ARRAY_IS_PURCHASABLE_KEY_3 ] ) ) {
			return Price_Model::IS_PURCHASABLE_UNKNOWN;
		}

		$is_purchasable = $data[ Price_Model::ARRAY_IS_PURCHASABLE_KEY_3 ];

		switch ( $is_purchasable ) {
			case Price_Model::IS_PURCHASABLE_TRUE:
				return Price_Model::IS_PURCHASABLE_TRUE;
			case Price_Model::IS_PURCHASABLE_FALSE:
				return Price_Model::IS_PURCHASABLE_FALSE;
		}

		return Price_Model::IS_PURCHASABLE_UNKNOWN;
	}
}
