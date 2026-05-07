<?php
/**
 * Calculates free shipping eligibility and thresholds for InPost shipping methods.
 *
 * @package Ilabs\Inpost_Pay\Lib\Shipping
 */

declare( strict_types=1 );

namespace Ilabs\Inpost_Pay\Lib\Shipping;

use Ilabs\Inpost_Pay\Lib\config\ShippingCost\GroupInterface;
use Ilabs\Inpost_Pay\Logger;
use WC_Shipping_Method;
use WC_Shipping_Zones;

/**
 * Class FreeShippingCalculator
 *
 * Calculates free shipping eligibility and thresholds for InPost shipping methods.
 * Determines whether adding a related product would trigger free delivery.
 *
 * @package Ilabs\Inpost_Pay\Lib\Shipping
 */
class FreeShippingCalculator {

	/**
	 * Cached lowest free shipping threshold across all zones.
	 *
	 * @var float|null
	 */
	private static ?float $lowest_free_shipping_threshold = null;

	/**
	 * Cache for loaded WC_Shipping_Method instances.
	 *
	 * @var array
	 */
	private static array $shipping_method_cache = array();

	/**
	 * Gets the current cart gross total after discounts.
	 *
	 * @return float Cart total rounded to 2 decimal places, or 0.0 if cart not initialized.
	 */
	public static function get_cart_total(): float {
		// Guard: Don't access cart if not initialized.
		if ( ! WC()->cart ) {
			return 0.0;
		}

		$subtotal    = WC()->cart->get_cart_contents_total();
		$product_tax = WC()->cart->get_cart_contents_tax();
		$discount    = WC()->cart->get_discount_total();

		$gross                = $subtotal + $product_tax;
		$gross_after_discount = max( $gross - $discount, 0 );

		return round( $gross_after_discount, 2 );
	}

	/**
	 * Gets the free shipping threshold for a given InPost shipping method instance.
	 *
	 * @param string     $method_id        Shipping method ID (e.g. 'easypack_parcel_machines:6').
	 * @param float|null $lowest_threshold Current lowest known threshold.
	 *
	 * @return array Array with 'threshold' key: the resolved threshold or null.
	 */
	public static function get_inpost_free_shipping_threshold( string $method_id, ?float $lowest_threshold ): array {
		$instance_id = explode( ':', $method_id )[1] ?? null;
		if ( ! $instance_id ) {
			return array( 'threshold' => null );
		}

		$method = self::get_shipping_method( $instance_id );
		if ( ! $method instanceof WC_Shipping_Method ) {
			return array( 'threshold' => null );
		}

		$free_shipping_threshold = (float) ( $method->instance_settings['free_shipping_cost'] ?? 0 );
		Logger::log( "Method $method_id - free_shipping_cost setting: " . var_export( $method->instance_settings['free_shipping_cost'] ?? 'NOT_SET', true ) );

		if ( $free_shipping_threshold <= 0 ) {
			return array( 'threshold' => null );
		}

		if ( null === $lowest_threshold ) {
			return array( 'threshold' => $free_shipping_threshold );
		}

		if ( $lowest_threshold > 0 ) {
			return array( 'threshold' => min( $lowest_threshold, $free_shipping_threshold ) );
		}

		return array( 'threshold' => $free_shipping_threshold );
	}

	/**
	 * Determines whether adding a related product would make delivery free.
	 *
	 * Checks both free_shipping WC methods and InPost easypack_ free shipping
	 * thresholds to decide if the potential cart total would cross any threshold.
	 *
	 * @param array $mapped_courier_methods Mapped courier method IDs.
	 * @param array $mapped_apm_methods     Mapped APM method IDs.
	 * @param float $related_product_price  Price of the related product being evaluated.
	 *
	 * @return array Delivery type codes with bool indicating free delivery eligibility.
	 */
	public static function get_free_shipping_methods(
		array $mapped_courier_methods,
		array $mapped_apm_methods,
		float $related_product_price
	): array {
		$free_methods = array(
			GroupInterface::DELIVERY_TYPE_CODE_COURIER => false,
			GroupInterface::DELIVERY_TYPE_CODE_APM     => false,
		);

		$merged_methods = array_merge(
			array_fill_keys( $mapped_courier_methods, GroupInterface::DELIVERY_TYPE_CODE_COURIER ),
			array_fill_keys( $mapped_apm_methods, GroupInterface::DELIVERY_TYPE_CODE_APM )
		);

		$lowest_threshold = self::get_lowest_free_shipping_threshold();
		if ( null === $lowest_threshold ) {
			$lowest_threshold = PHP_FLOAT_MAX;
		}

		foreach ( $merged_methods as $method_id => $type ) {
			$instance_id = explode( ':', $method_id )[1] ?? null;
			if ( ! $instance_id ) {
				continue;
			}

			$method = self::get_shipping_method( $instance_id );
			if ( ! $method instanceof WC_Shipping_Method ) {
				continue;
			}

			$base_cart_total      = self::get_cart_total();
			$potential_cart_total = $base_cart_total + $related_product_price;

			switch ( true ) {
				case 0 === strpos( $method_id, 'free_shipping' ):
					if ( PHP_FLOAT_MAX !== $lowest_threshold && $potential_cart_total >= $lowest_threshold ) {
						$free_methods[ $type ] = true;
						Logger::log( "Free shipping method triggered for type: $type" );
					}
					break;
				case 0 === strpos( $method_id, 'easypack_' ):
					$result    = self::get_inpost_free_shipping_threshold( $method_id, $lowest_threshold );
					$threshold = $result['threshold'] ?? null;

					if ( null !== $threshold ) {
						$lowest_threshold = $threshold;

						$is_free  = $potential_cart_total >= $lowest_threshold;
						$was_free = $base_cart_total >= $lowest_threshold;

						if ( ! $was_free && $is_free ) {
							$free_methods[ $type ] = true;
						}
					} else {
						Logger::log( "No threshold found for method: $method_id" );
					}
					break;
			}
		}

		// Also check if a native WC free_shipping zone method would be triggered by adding this product.
		// Only mark as free if delivery is NOT already free without the product (!$was_free).
		// Availability per type is handled downstream in get_delivery_related_products().
		$native_threshold = self::get_lowest_free_shipping_threshold();
		if ( null !== $native_threshold ) {
			$base_cart_total = self::get_cart_total();
			$potential_total = $base_cart_total + $related_product_price;
			$was_free        = $base_cart_total >= $native_threshold;
			$will_be_free    = $potential_total >= $native_threshold;

			if ( ! $was_free && $will_be_free ) {
				foreach ( $free_methods as $type => $is_free ) {
					if ( ! $is_free ) {
						$free_methods[ $type ] = true;
					}
				}
			}
		}

		return $free_methods;
	}

	/**
	 * Gets the lowest free shipping threshold across all configured shipping zones.
	 *
	 * Scans all zones for enabled free_shipping methods and returns the minimum
	 * qualifying amount. Result is cached for the duration of the request.
	 *
	 * @return float|null Lowest threshold, or null if no free shipping methods found.
	 */
	private static function get_lowest_free_shipping_threshold(): ?float {
		if ( null !== self::$lowest_free_shipping_threshold ) {
			return self::$lowest_free_shipping_threshold;
		}

		$lowest_threshold = PHP_FLOAT_MAX;
		$shipping_zones   = WC_Shipping_Zones::get_zones();
		$found_method     = false;

		foreach ( $shipping_zones as $zone ) {
			foreach ( $zone['shipping_methods'] as $method ) {
				if ( 'free_shipping' !== $method->id || 'yes' !== $method->enabled ) {
					continue;
				}
				$found_method = true;
				$requires     = $method->instance_settings['requires'] ?? '';
				$min_amount   = (float) ( $method->instance_settings['min_amount'] ?? 0 );

				if ( '' === $requires ) {
					$lowest_threshold = 0;
					continue;
				}
				if ( in_array( $requires, array( 'min_amount', 'either', 'both' ), true ) ) {
					$lowest_threshold = min( $lowest_threshold, $min_amount );
				}
			}
		}

		self::$lowest_free_shipping_threshold = $found_method ? $lowest_threshold : null;

		return self::$lowest_free_shipping_threshold;
	}

	/**
	 * Loads a WooCommerce shipping method by its instance ID.
	 *
	 * @param string|null $method_id Shipping method instance ID.
	 *
	 * @return WC_Shipping_Method|null Shipping method or null if not found.
	 */
	private static function get_shipping_method( ?string $method_id ): ?WC_Shipping_Method {
		if ( ! $method_id ) {
			return null;
		}

		$method = WC_Shipping_Zones::get_shipping_method( $method_id );
		return $method instanceof WC_Shipping_Method ? $method : null;
	}

	/**
	 * Checks whether a shipping method has taxable tax status.
	 *
	 * @param WC_Shipping_Method $method Shipping method.
	 *
	 * @return bool True if method tax_status is 'taxable'.
	 */
	private static function is_method_status_taxable( WC_Shipping_Method $method ): bool {
		$tax_status = $method->instance_settings['tax_status'];

		return 'taxable' === $tax_status;
	}
}
