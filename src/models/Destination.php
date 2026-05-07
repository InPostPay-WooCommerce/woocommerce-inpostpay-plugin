<?php

declare(strict_types=1);

namespace Ilabs\Inpost_Pay\models;

class Destination {
	public static function get(): array {
		$user_id = get_current_user_id();
		$dest    = array();

		$defaults = array(
			'country'   => 'PL',
			'state'     => '',
			'postcode'  => '00-000',
			'city'      => 'Warszawa',
			'address'   => 'ul. Przykładowa 1',
			'address_2' => '',
		);

		if ( $user_id > 0 ) {
			$shipping_data = array(
				'country'   => get_user_meta( $user_id, 'shipping_country', true ),
				'state'     => get_user_meta( $user_id, 'shipping_state', true ),
				'postcode'  => get_user_meta( $user_id, 'shipping_postcode', true ),
				'city'      => get_user_meta( $user_id, 'shipping_city', true ),
				'address'   => get_user_meta( $user_id, 'shipping_address_1', true ),
				'address_2' => get_user_meta( $user_id, 'shipping_address_2', true ),
			);

			$billing_data = array(
				'country'   => get_user_meta( $user_id, 'billing_country', true ),
				'state'     => get_user_meta( $user_id, 'billing_state', true ),
				'postcode'  => get_user_meta( $user_id, 'billing_postcode', true ),
				'city'      => get_user_meta( $user_id, 'billing_city', true ),
				'address'   => get_user_meta( $user_id, 'billing_address_1', true ),
				'address_2' => get_user_meta( $user_id, 'billing_address_2', true ),
			);

		} else {
			// Country is used only for shipping zone matching. WC session country can be
			// corrupted by IP geolocation from external server requests (e.g. InPost
			// confirmation requests come from non-PL servers and WC saves the geolocated
			// country back to the user's session). Use the store's configured base location
			// to ensure stable zone resolution regardless of request origin.
			$base_location    = wc_get_base_location();
			$session_customer = WC()->session ? (array) WC()->session->get( 'customer' ) : array();

			$shipping_data = array(
				'country'   => $base_location['country'] ?? '',
				'state'     => $session_customer['shipping_state'] ?? '',
				'postcode'  => $session_customer['shipping_postcode'] ?? '',
				'city'      => $session_customer['shipping_city'] ?? '',
				'address'   => $session_customer['shipping_address_1'] ?? '',
				'address_2' => $session_customer['shipping_address_2'] ?? '',
			);

			$billing_data = array(
				'country'   => $base_location['country'] ?? '',
				'state'     => $session_customer['billing_state'] ?? '',
				'postcode'  => $session_customer['billing_postcode'] ?? '',
				'city'      => $session_customer['billing_city'] ?? '',
				'address'   => $session_customer['billing_address_1'] ?? '',
				'address_2' => $session_customer['billing_address_2'] ?? '',
			);
		}

		foreach ( array( 'country', 'state', 'postcode', 'city', 'address', 'address_2' ) as $field ) {
			$dest[ $field ] = '';
			if ( ! empty( $shipping_data[ $field ] ) ) {
				$dest[ $field ] = (string) $shipping_data[ $field ];
			} elseif ( ! empty( $billing_data[ $field ] ) ) {
				$dest[ $field ] = (string) $billing_data[ $field ];
			} else {
				$dest[ $field ] = $defaults[ $field ];
			}
		}

		return $dest;
	}
}
