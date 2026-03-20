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
			$shipping_data = array(
				'country'   => WC()->customer->get_shipping_country(),
				'state'     => WC()->customer->get_shipping_state(),
				'postcode'  => WC()->customer->get_shipping_postcode(),
				'city'      => WC()->customer->get_shipping_city(),
				'address'   => WC()->customer->get_shipping_address(),
				'address_2' => WC()->customer->get_shipping_address_2(),
			);

			$billing_data = array(
				'country'   => WC()->customer->get_shipping_country(),
				'state'     => WC()->customer->get_shipping_state(),
				'postcode'  => WC()->customer->get_shipping_postcode(),
				'city'      => WC()->customer->get_shipping_city(),
				'address'   => WC()->customer->get_shipping_address(),
				'address_2' => WC()->customer->get_shipping_address_2(),
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
