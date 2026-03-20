<?php
/**
 * Resolves available InPost shipping rates for products and the cart.
 *
 * @package Ilabs\Inpost_Pay\Lib\Shipping
 */

declare( strict_types=1 );

namespace Ilabs\Inpost_Pay\Lib\Shipping;

use Ilabs\Inpost_Pay\Integration\FlexibleShipping\FlexibleShippingProductValidator;
use Ilabs\Inpost_Pay\Lib\config\ShippingCost\GroupInterface;
use Ilabs\Inpost_Pay\Lib\Utils\DeliveryAvailabilityTracker;
use Ilabs\Inpost_Pay\models\Destination;
use WC_Product;
use WC_Shipping_Rate;

/**
 * Class ShippingRatesResolver
 *
 * Resolves which InPost shipping types are available for a given product
 * by running WooCommerce's calculate_shipping() or falling back to a
 * direct zone-based check when no rates are returned.
 *
 * @package Ilabs\Inpost_Pay\Lib\Shipping
 */
class ShippingRatesResolver {

	/**
	 * Cache for available InPost delivery type codes, keyed by "productId:quantity".
	 *
	 * @var array
	 */
	private static array $available_methods_cache = array();

	/**
	 * Shipping packages captured via the woocommerce_shipping_packages filter.
	 *
	 * @var array
	 */
	private static array $store_packages = array();

	/**
	 * Gets available InPost delivery type codes for a product.
	 *
	 * Performs a full WooCommerce calculate_shipping() call and checks returned
	 * rates against the mapped method lists. Falls back to zone-based checking
	 * when no rates are returned (e.g. Flexible Shipping with complex conditions).
	 *
	 * @param WC_Product                       $product               Product.
	 * @param array                            $mapped_courier_methods Mapped courier method IDs.
	 * @param array                            $mapped_apm_methods    Mapped APM method IDs.
	 * @param int                              $quantity              Product quantity.
	 * @param DeliveryAvailabilityTracker|null $tracker               Decision tracker.
	 *
	 * @return array Delivery type codes that have a matching available rate.
	 */
	public static function get_available_methods_for_product(
		WC_Product $product,
		array $mapped_courier_methods,
		array $mapped_apm_methods,
		int $quantity = 1,
		?DeliveryAvailabilityTracker $tracker = null
	): array {
		$cache_key = $product->get_id() . ':' . $quantity;
		if ( isset( self::$available_methods_cache[ $cache_key ] ) ) {
			return self::$available_methods_cache[ $cache_key ];
		}

		$destination = Destination::get();
		$package     = self::build_package_for_product( $product, $quantity, $destination );

		$shipping       = WC()->shipping();
		$wc_session_key = 'shipping_for_package_0';
		WC()->session->set( $wc_session_key, null );

		add_filter(
			'woocommerce_shipping_packages',
			static function ( $packages ) {
				self::$store_packages = $packages;

				return $packages;
			},
			1
		);

		$shipping->calculate_shipping( array( $package ) );

		$packages = self::$store_packages;

		$filtered_methods = array();
		$found_rates      = array();

		foreach ( $packages[0]['rates'] ?? array() as $rate ) {
			if ( ! $rate instanceof WC_Shipping_Rate ) {
				continue;
			}

			$base_id       = $rate->get_method_id() . ':' . $rate->get_instance_id();
			$found_rates[] = $base_id;

			if ( in_array( $base_id, $mapped_courier_methods, true ) ) {
				$filtered_methods[ GroupInterface::DELIVERY_TYPE_CODE_COURIER ] = true;
			}

			if ( in_array( $base_id, $mapped_apm_methods, true ) ) {
				$filtered_methods[ GroupInterface::DELIVERY_TYPE_CODE_APM ] = true;
			}
		}

		// Fallback: If calculate_shipping returned no rates, try zone-based checking.
		// This helps with plugins like Flexible Shipping that may have complex conditions.
		if ( empty( $filtered_methods ) ) {
			if ( $tracker ) {
				$tracker->add_decision(
					'calculate_shipping_fallback',
					'zone_check',
					true,
					'calculate_shipping returned no rates, using zone-based fallback',
					array( 'found_rates' => $found_rates )
				);
			}

			$filtered_methods = self::get_available_methods_from_zone(
				$product,
				$mapped_courier_methods,
				$mapped_apm_methods,
				$quantity,
				$tracker
			);
			$found_rates      = array_merge(
				array_keys( array_filter( $mapped_courier_methods ) ),
				array_keys( array_filter( $mapped_apm_methods ) )
			);
		}

		if ( $tracker ) {
			foreach ( array( 'COURIER', 'APM' ) as $type ) {
				$is_available = isset( $filtered_methods[ $type ] );
				$tracker->add_decision(
					'shipping_rates_check',
					$type,
					$is_available,
					$is_available
						? 'Found matching shipping rate in zone'
						: 'No matching shipping rate found in zone',
					array(
						'available_rates' => $found_rates,
						'mapped_courier'  => $mapped_courier_methods,
						'mapped_apm'      => $mapped_apm_methods,
					)
				);
			}
		}

		self::$available_methods_cache[ $cache_key ] = array_keys( $filtered_methods );

		return self::$available_methods_cache[ $cache_key ];
	}

	/**
	 * Gets available InPost delivery types for the current cart's shipping packages.
	 *
	 * @return array Available delivery type codes.
	 */
	public static function get_delivery_types_for_cart(): array {
		$shipping = WC()->shipping();
		$packages = $shipping->get_packages();

		$has_rates = false;
		foreach ( $packages as $p ) {
			if ( ! empty( $p['rates'] ) ) {
				$has_rates = true;
				break;
			}
		}
		if ( ! $has_rates ) {
			$shipping->calculate_shipping( WC()->cart->get_shipping_packages() );
			$packages = $shipping->get_packages();
		}

		$available_types = array();
		foreach ( $packages as $package ) {
			foreach ( $package['rates'] as $rate_id => $rate ) {
				$delivery_type = ShippingMethodMapper::get_delivery_type_from_method( $rate_id );
				if ( null !== $delivery_type && ! in_array( $delivery_type, $available_types, true ) ) {
					$available_types[] = $delivery_type;
				}
			}
		}

		return $available_types;
	}

	/**
	 * Fallback: gets available delivery types directly from the user's shipping zone.
	 *
	 * Used when calculate_shipping() returns no rates (e.g. Flexible Shipping
	 * with complex rules that prevent calculation outside a full cart context).
	 *
	 * @param WC_Product                       $product                Product.
	 * @param array                            $mapped_courier_methods Mapped courier method IDs.
	 * @param array                            $mapped_apm_methods     Mapped APM method IDs.
	 * @param int                              $quantity               Product quantity.
	 * @param DeliveryAvailabilityTracker|null $tracker                Decision tracker.
	 *
	 * @return array Filtered methods array keyed by delivery type code.
	 */
	private static function get_available_methods_from_zone(
		WC_Product $product,
		array $mapped_courier_methods,
		array $mapped_apm_methods,
		int $quantity,
		?DeliveryAvailabilityTracker $tracker
	): array {
		$user_zone = ShippingZoneResolver::prepare_user_zone_context();
		$zone_id   = $user_zone->get_id();

		// Get all enabled shipping methods in the zone.
		$shipping_methods = $user_zone->get_shipping_methods( true );

		$filtered_methods = array();
		$found_methods    = array();

		foreach ( $shipping_methods as $method ) {
			if ( ! $method->is_enabled() ) {
				continue;
			}

			$method_id       = $method->get_rate_id();
			$found_methods[] = $method_id;

			// Check if method is in mapped courier methods.
			if ( in_array( $method_id, $mapped_courier_methods, true ) ) {
				if ( FlexibleShippingProductValidator::is_flexible_shipping_method( $method ) ) {
					// For Flexible Shipping, do basic validation.
					if ( FlexibleShippingProductValidator::product_matches_flexible_shipping_basic_rules( $product, $method ) ) {
						$filtered_methods[ GroupInterface::DELIVERY_TYPE_CODE_COURIER ] = true;
					}
				} else {
					$filtered_methods[ GroupInterface::DELIVERY_TYPE_CODE_COURIER ] = true;
				}
			}

			// Check if method is in mapped APM methods.
			if ( in_array( $method_id, $mapped_apm_methods, true ) ) {
				if ( FlexibleShippingProductValidator::is_flexible_shipping_method( $method ) ) {
					if ( FlexibleShippingProductValidator::product_matches_flexible_shipping_basic_rules( $product, $method ) ) {
						$filtered_methods[ GroupInterface::DELIVERY_TYPE_CODE_APM ] = true;
					}
				} else {
					$filtered_methods[ GroupInterface::DELIVERY_TYPE_CODE_APM ] = true;
				}
			}
		}

		if ( $tracker ) {
			$tracker->add_decision(
				'zone_fallback_check',
				'methods_found',
				! empty( $filtered_methods ),
				sprintf(
					'Zone fallback found %d enabled methods in zone %d',
					count( $found_methods ),
					$zone_id
				),
				array(
					'zone_id'        => $zone_id,
					'found_methods'  => $found_methods,
					'mapped_courier' => $mapped_courier_methods,
					'mapped_apm'     => $mapped_apm_methods,
				)
			);
		}

		return $filtered_methods;
	}

	/**
	 * Builds a WooCommerce shipping package array for the given product and quantity.
	 *
	 * @param WC_Product $product  Product object.
	 * @param int        $quantity Quantity of the product.
	 * @param array      $dest     Destination data.
	 *
	 * @return array Package data.
	 */
	private static function build_package_for_product( WC_Product $product, int $quantity, array $dest ): array {
		$line_total    = (float) $product->get_price() * $quantity;
		$product_id    = $product->get_id();
		$line_subtotal = $line_total;

		return array(
			'contents'        => array(
				$product_id => array(
					'product_id'        => $product_id,
					'variation_id'      => $product->is_type( 'variation' ) ? $product_id : 0,
					'data'              => $product,
					'quantity'          => $quantity,
					'line_total'        => $line_total,
					'line_tax'          => 0,
					'line_subtotal'     => $line_subtotal,
					'line_subtotal_tax' => 0,
				),
			),
			'contents_cost'   => $line_total,
			'applied_coupons' => WC()->cart ? WC()->cart->get_applied_coupons() : array(),
			'user'            => array( 'ID' => get_current_user_id() ),
			'destination'     => $dest,
			'cart_subtotal'   => $line_total,
		);
	}
}
