<?php

declare( strict_types=1 );

namespace Ilabs\Inpost_Pay\Integration\FlexibleShipping;

use WC_Product;
use WC_Shipping_Method;

/**
 * Class FlexibleShippingProductValidator
 *
 * Provides static helpers to validate whether a product potentially matches
 * Flexible Shipping method rules without running a full calculate_shipping() call.
 *
 * Used as part of the zone-based availability fallback in ShippingRatesResolver
 * when calculate_shipping() returns no rates.
 *
 * @package Ilabs\Inpost_Pay\Integration\FlexibleShipping
 */
class FlexibleShippingProductValidator {

	/**
	 * Checks if a shipping method is a Flexible Shipping method.
	 *
	 * @param WC_Shipping_Method $method Shipping method.
	 *
	 * @return bool True if the method ID is a known Flexible Shipping identifier.
	 */
	public static function is_flexible_shipping_method( WC_Shipping_Method $method ): bool {
		return in_array(
			$method->id,
			array( 'flexible_shipping_single', 'flexible_shipping' ),
			true
		);
	}

	/**
	 * Performs basic rule validation for a product against a Flexible Shipping method.
	 *
	 * Checks if the product can potentially match any rule without running the full
	 * calculate_shipping() pipeline. Uses a conservative approach: if all rules have
	 * blocking conditions the method returns false (prefer "not available" over false positive).
	 *
	 * Only shipping_class conditions are evaluated; all other condition types are ignored
	 * and treated as non-blocking.
	 *
	 * @param WC_Product         $product Product.
	 * @param WC_Shipping_Method $method  Shipping method.
	 *
	 * @return bool True if the product might match at least one Flexible Shipping rule.
	 */
	public static function product_matches_flexible_shipping_basic_rules(
		WC_Product $product,
		WC_Shipping_Method $method
	): bool {
		// Get method rules.
		$method_rules = $method->get_option( 'method_rules', array() );
		if ( ! is_array( $method_rules ) ) {
			$method_rules = json_decode( $method_rules, true );
			if ( ! is_array( $method_rules ) ) {
				$method_rules = array();
			}
		}

		// If no rules defined, assume available (method will handle its own logic).
		if ( empty( $method_rules ) ) {
			return true;
		}

		$product_shipping_class_id = $product->get_shipping_class_id();

		// Check if any rule could potentially match.
		foreach ( $method_rules as $rule ) {
			if ( empty( $rule ) || ! is_array( $rule ) ) {
				continue;
			}

			// Check shipping class condition if present.
			if ( isset( $rule['conditions'] ) && is_array( $rule['conditions'] ) ) {
				$has_blocking_condition = false;

				foreach ( $rule['conditions'] as $condition ) {
					if ( ! isset( $condition['condition_id'] ) ) {
						continue;
					}

					// Check shipping class condition.
					if ( 'shipping_class' === $condition['condition_id'] ) {
						$allowed_classes = $condition['shipping_class'] ?? array();
						if ( ! empty( $allowed_classes ) &&
							! in_array( $product_shipping_class_id, $allowed_classes, true ) &&
							! in_array( 0, $allowed_classes, true ) // 0 = no shipping class
						) {
							$has_blocking_condition = true;
							break;
						}
					}
				}

				// If this rule doesn't block, consider method available.
				if ( ! $has_blocking_condition ) {
					return true;
				}
			} else {
				// Rule has no conditions, so it's available.
				return true;
			}
		}

		// If all rules have blocking conditions, assume not available.
		// (Conservative approach - better to show "not available" than false positive).
		return false;
	}
}
