<?php

declare( strict_types=1 );

namespace Ilabs\Inpost_Pay\Lib\helpers;

class EANHelper {
	/**
	 * Validates if given value is a numeric string of length 12–13.
	 *
	 * Intentionally does not perform full EAN checksum validation.
	 *
	 * @param string $ean
	 *
	 * @return bool
	 */
	public static function isValid( string $ean ): bool {
		return (bool) preg_match( '/^\d{12,13}$/', $ean );
	}

	public static function prepareEan( \WC_Product $product ): string {
		static $cache = array();
		$id           = (int) $product->get_id();

		if ( isset( $cache[ $id ] ) ) {
			return $cache[ $id ];
		}

		if ( method_exists( $product, 'get_global_unique_id' ) ) {
			$val = (string) $product->get_global_unique_id();
			if ( $val !== '' ) {
				return $cache[ $id ] = $val;
			}
		}

		if ( $product->is_type( 'variation' ) ) {
			$parentId = (int) $product->get_parent_id();
			if ( $parentId > 0 ) {
				$parent = wc_get_product( $parentId );
				if ( $parent instanceof \WC_Product ) {
					$v = self::prepareEan( $parent );
					if ( $v !== '0' ) {
						return $cache[ $id ] = $v;
					}
				}
			}
		}

		return $cache[ $id ] = '0';
	}
}
