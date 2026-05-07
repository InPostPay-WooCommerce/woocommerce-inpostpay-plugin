<?php

namespace Ilabs\Inpost_Pay\Integration\Shipping;

use Ilabs\Inpost_Pay\WooCommerce\WooDeliveryPrice;

/**
 * @desc iLabs
 */
class WoocommerceInpostIntegration extends AbstractShippingMethodIntegration implements ShippingMethodIntegrationInterface {

	public function __construct(
		string $iziDeliveryMethodId
	) {
		$this->iziDeliveryMethodId = $iziDeliveryMethodId;
	}

	public static function isEasyPack( $delivery_method ) {
		return strpos( $delivery_method, 'easypack_' );
	}
	public static function getRatesForDelivery( $rate_method, $rate_instance_id, $net_price, $add_tax ) {
		$easyRate = 0;
		if ( class_exists( '\InspireLabs\WoocommerceInpost\EasyPack_Helper' ) ) {
			$easyRates = \InspireLabs\WoocommerceInpost\EasyPack_Helper::EasyPack_Helper()->get_saved_method_rates( $rate_method, $rate_instance_id );
			foreach ( $easyRates as $eRate ) {
				$diff = abs( floatval( $eRate['cost'] ) - floatval( $net_price ) );
				if ( $eRate['cost'] && $diff <= 0.01 ) {
					if ( ! $add_tax ) {
						$easyRate = wc_format_decimal( $eRate['cost'], 3 );
					} else {
						$easyRate = wc_format_decimal( ( $eRate['cost'] / WooDeliveryPrice::getShippingTaxModifier() ), 3 );
					}
					break;
				}
			}
		}
		return $easyRate;
	}
}
