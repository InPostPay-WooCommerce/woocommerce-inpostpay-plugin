<?php
/**
 * Resolves InPost product-level shipping method restrictions.
 *
 * @package Ilabs\Inpost_Pay\Lib\Shipping
 */

declare( strict_types=1 );

namespace Ilabs\Inpost_Pay\Lib\Shipping;

use Ilabs\Inpost_Pay\Lib\config\ShippingCost\GroupInterface;
use WC_Product;

/**
 * Class InpostProductRestrictions
 *
 * Resolves InPost-specific shipping restrictions defined on a product
 * via the woo_inpost_shipping_methods_allowed product meta.
 *
 * Only active when both izi_shipping_check_shipping_availability is enabled
 * and the inpost-for-woocommerce plugin is active.
 *
 * @package Ilabs\Inpost_Pay\Lib\Shipping
 */
class InpostProductRestrictions {

	/**
	 * Gets InPost-specific shipping restrictions for a product.
	 *
	 * Returns per-type restriction values:
	 * - false: Method is explicitly blocked (type not in allowed list).
	 * - true: Method is explicitly allowed for this product.
	 * - null: No restrictions apply (meta not set, or non-easypack methods).
	 *
	 * Logic:
	 * - Meta not set ('', non-array): product never configured → null (all available).
	 *   Mirrors InPost plugin own logic: null config = all methods checked by default.
	 * - Meta is empty array []: all methods explicitly unchecked → blocks all easypack_
	 *   mapped methods (false), leaves non-easypack unrestricted (null).
	 * - Meta has values: allows only listed easypack_ types (true), blocks others (false).
	 * - Non-easypack methods are never affected — is_easypack_method() check returns false.
	 *
	 * @param WC_Product $product Product.
	 *
	 * @return array Restrictions keyed by 'APM'/'COURIER' with values false/true/null.
	 */
	public static function get_inpost_restrictions( WC_Product $product ): array {
		$inpost_methods = ShippingMethodMapper::get_inpost_methods();

		foreach ( $inpost_methods as $type => &$method ) {
			$method = array_filter( array_map( 'trim', explode( ',', $method ) ) );
		}
		unset( $method );

		$is_inpost_mapped = array(
			GroupInterface::DELIVERY_TYPE_CODE_APM     => ! empty( $inpost_methods[ GroupInterface::DELIVERY_TYPE_CODE_APM ] ),
			GroupInterface::DELIVERY_TYPE_CODE_COURIER => ! empty( $inpost_methods[ GroupInterface::DELIVERY_TYPE_CODE_COURIER ] ),
		);

		$allowed_methods = get_post_meta( $product->get_id(), 'woo_inpost_shipping_methods_allowed', true );

		if ( ! is_array( $allowed_methods ) && $product->is_type( 'variation' ) ) {
			$parent_id       = $product->get_parent_id();
			$allowed_methods = get_post_meta( $parent_id, 'woo_inpost_shipping_methods_allowed', true );
		}

		if ( ! is_array( $allowed_methods ) ) {
			// Meta not set (product never configured in InPost settings) → all methods available by default.
			// Mirrors InPost plugin logic: null config = all methods shown as checked.
			return array_map(
				static function ( $mapped ) {
					return $mapped ? null : false;
				},
				$is_inpost_mapped
			);
		}

		if ( empty( $allowed_methods ) ) {
			// Meta is empty array (all methods explicitly unchecked) → block all easypack_ mapped methods.
			$restrictions = array();
			foreach ( $is_inpost_mapped as $type => $mapped ) {
				if ( ! $mapped ) {
					$restrictions[ $type ] = false;
				} else {
					$only_inpost = true;
					foreach ( $inpost_methods[ $type ] as $method_id ) {
						if ( ! self::is_easypack_method( $method_id ) ) {
							$only_inpost = false;
							break;
						}
					}
					$restrictions[ $type ] = $only_inpost ? false : null;
				}
			}
			return $restrictions;
		}

		$mapped_types = array();
		foreach ( $allowed_methods as $method ) {
			$type = ShippingMethodMapper::get_delivery_type_from_method( $method );
			if ( $type ) {
				$mapped_types[] = $type;
			}
		}

		$restrictions = array();
		foreach ( $is_inpost_mapped as $type => $mapped ) {

			$only_inpost = true;
			foreach ( $inpost_methods[ $type ] as $method_id ) {
				if ( ! self::is_easypack_method( $method_id ) ) {
					$only_inpost = false;
					break;
				}
			}

			if ( ! $mapped ) {
				$restrictions[ $type ] = false;
			} elseif ( ! $only_inpost ) {
				$restrictions[ $type ] = null;
			} else {
				$restrictions[ $type ] = in_array( $type, $mapped_types, true );
			}
		}

		return $restrictions;
	}

	/**
	 * Checks if a shipping method ID belongs to the InPost easypack_ family.
	 *
	 * Only easypack_ methods are subject to InPost product restrictions
	 * (woo_inpost_shipping_methods_allowed meta). Non-easypack methods such as
	 * flat_rate, free_shipping, or flexible_shipping are never restricted.
	 *
	 * @param string $method Shipping method identifier.
	 *
	 * @return bool True if the method starts with 'easypack_'.
	 */
	public static function is_easypack_method( string $method ): bool {
		return 0 === strpos( $method, 'easypack_' );
	}
}
