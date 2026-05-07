<?php

namespace Ilabs\Inpost_Pay\Lib\helpers;

use Ilabs\Inpost_Pay\models\Destination;
use WC_Shipping_Zone;
use WC_Shipping_Zones;

class ShippingZoneHelper {

	/**
	 * Get all shipping zones including the default zone
	 *
	 * @return array Array of WC_Shipping_Zone objects
	 */
	public static function get_all_shipping_zones(): array {
		$zones = array();

		$shipping_zones = WC_Shipping_Zones::get_zones();
		foreach ( $shipping_zones as $zone_data ) {
			$zones[] = new WC_Shipping_Zone( $zone_data['zone_id'] );
		}

		$zones[] = new WC_Shipping_Zone( 0 );

		return $zones;
	}

	public static function getAvailableShippingMethodsForZone( int $zone_id ): array {
		$zone      = \WC_Shipping_Zones::get_zone( $zone_id );
		$available = array();

		$zone_shipping_methods = $zone->get_shipping_methods();
		foreach ( $zone_shipping_methods as $index => $method ) {
			$available[ $method->get_rate_id() ] = $method->get_title();
		}

		return $available;
	}

	public static function getShippingRatesForZone( int $zone_id, array $destination = null ): array {
		$zone             = \WC_Shipping_Zones::get_zone( $zone_id );
		$shipping_methods = $zone->get_shipping_methods( true );

		$rates = array();

		if ( $destination === null ) {
			$destination = Destination::get();
		}

		$package = array(
			'contents'        => WC()->cart->get_cart(),
			'contents_cost'   => WC()->cart->get_cart_contents_total(),
			'applied_coupons' => WC()->cart->get_applied_coupons(),
			'user'            => array(
				'ID' => get_current_user_id(),
			),
			'destination'     => array(
				'country'   => (string) ( $destination['country'] ?? '' ),
				'state'     => (string) ( $destination['state'] ?? '' ),
				'postcode'  => (string) ( $destination['postcode'] ?? '' ),
				'city'      => (string) ( $destination['city'] ?? '' ),
				'address'   => (string) ( $destination['address'] ?? '' ),
				'address_1' => (string) ( $destination['address'] ?? '' ),
				'address_2' => (string) ( $destination['address_2'] ?? '' ),
			),
		);

		foreach ( $shipping_methods as $method ) {
			if ( ! $method->is_enabled() ) {
				continue;
			}

			if ( ! $method->is_available( $package ) ) {
				continue;
			}

			$method->calculate_shipping( $package );

			if ( isset( $method->rates ) && is_array( $method->rates ) ) {
				foreach ( $method->rates as $rate ) {
					$rates[ $rate->get_id() ] = $rate;
				}
			}
		}

		// The inpost_pay_shipping_rates_for_zone filter is executed before returning the results.
		$rates = apply_filters( 'inpost_pay_shipping_rates_for_zone', $rates, $zone_id );

		// Synchronize izi_full_cost meta with the final cost after filters.
		// The filter may have changed the cost (e.g. set_cost(0) for free shipping thresholds),
		// so izi_full_cost must reflect the updated value for correct price mapping.
		foreach ( $rates as $rate ) {
			if ( ! $rate instanceof \WC_Shipping_Rate ) {
				continue;
			}
			$meta = $rate->get_meta_data();
			if ( isset( $meta['izi_full_cost'] ) && (float) $meta['izi_full_cost'] !== (float) $rate->get_cost() ) {
				$rate->add_meta_data( 'izi_full_cost', (float) $rate->get_cost() );
			}
		}

		return $rates;
	}
}
