<?php
/**
 * Resolves WooCommerce shipping zone for the current user context.
 *
 * @package Ilabs\Inpost_Pay\Lib\Shipping
 */

declare( strict_types=1 );

namespace Ilabs\Inpost_Pay\Lib\Shipping;

use Ilabs\Inpost_Pay\Logger;
use Ilabs\Inpost_Pay\models\Destination;

/**
 * Class ShippingZoneResolver
 *
 * Resolves the current user's shipping zone and prepares WooCommerce package
 * data used for zone matching and shipping calculations.
 *
 * @package Ilabs\Inpost_Pay\Lib\Shipping
 */
class ShippingZoneResolver {

	/**
	 * Current user zone ID.
	 *
	 * @var int|null
	 */
	private static ?int $zone_id = null;

	/**
	 * Resolved shipping zone for the current user.
	 *
	 * @var \WC_Shipping_Zone|null
	 */
	private static ?\WC_Shipping_Zone $user_zone = null;

	/**
	 * Prepares the current user's shipping zone context.
	 *
	 * Resolves the matching WC shipping zone based on the user's destination
	 * and caches the result for the duration of the request.
	 *
	 * @return \WC_Shipping_Zone The matching shipping zone.
	 */
	public static function prepare_user_zone_context(): \WC_Shipping_Zone {
		if ( self::$user_zone instanceof \WC_Shipping_Zone ) {
			return self::$user_zone;
		}

		$dest           = Destination::get();
		$manual_package = self::prepare_manual_package( $dest );

//		Logger::log( '[ZONE_DIAG] cart=' . ( WC()->cart ? 'available' : 'null' )
//			. ' dest_country=' . ( $dest['country'] ?? 'MISSING' )
//			. ' package_empty=' . ( empty( $manual_package ) ? 'yes' : 'no' )
//			. ' package_has_dest=' . ( isset( $manual_package['destination']['country'] ) ? $manual_package['destination']['country'] : 'NO' ) );

		self::$user_zone = \WC_Shipping_Zones::get_zone_matching_package( $manual_package );

		return self::$user_zone;
	}

	/**
	 * Prepares a manual WooCommerce package array for shipping zone matching.
	 *
	 * @param array $dest Destination data.
	 *
	 * @return array Manual package data.
	 */
	public static function prepare_manual_package( array $dest ): array {
		// Guard: Don't access cart if not initialized.
		if ( ! WC()->cart ) {
			return array();
		}

		return array(
			'contents'        => WC()->cart->get_cart(),
			'contents_cost'   => WC()->cart->get_cart_contents_total(),
			'applied_coupons' => WC()->cart->get_applied_coupons(),
			'user'            => array(
				'ID' => get_current_user_id(),
			),
			'destination'     => array(
				'country'   => (string) ( $dest['country'] ?? 'Poland' ),
				'state'     => (string) ( $dest['state'] ?? '' ),
				'postcode'  => (string) ( $dest['postcode'] ?? '00-000' ),
				'city'      => (string) ( $dest['city'] ?? 'Warszawa' ),
				'address'   => (string) ( $dest['address'] ?? '00-000' ),
				'address_1' => (string) ( $dest['address'] ?? 'ul. Przykładowa 1' ),
				'address_2' => (string) ( $dest['address_2'] ?? '' ),
			),
		);
	}

	/**
	 * Sets the current zone ID.
	 *
	 * @param int|null $zone_id Zone ID to set.
	 *
	 * @return void
	 */
	public static function set_zone_id( ?int $zone_id ): void {
		self::$zone_id = $zone_id;
	}

	/**
	 * Gets the current zone ID.
	 *
	 * @return int|null Current zone ID.
	 */
	public static function get_zone_id(): ?int {
		return self::$zone_id;
	}
}
