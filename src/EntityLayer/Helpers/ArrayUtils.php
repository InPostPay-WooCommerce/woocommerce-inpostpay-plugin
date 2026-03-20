<?php
/**
 * Array utility helpers.
 *
 * @package Ilabs\Inpost_Pay\EntityLayer\Helpers
 */

declare( strict_types=1 );

namespace Ilabs\Inpost_Pay\EntityLayer\Helpers;

/**
 * Helper class for array operations.
 */
class ArrayUtils {

	/**
	 * Calculate deep diff between two arrays.
	 *
	 * Returns only keys that exist in $modified and have different values than $original.
	 *
	 * @param array $original Original array.
	 * @param array $modified Modified array.
	 *
	 * @return array Array with changed keys: ['key' => ['old' => value, 'new' => value]].
	 */
	public static function diff( array $original, array $modified ): array {
		$changes = array();

		foreach ( $modified as $key => $new_value ) {
			$old_value = $original[ $key ] ?? null;

			if ( ! array_key_exists( $key, $original ) ) {
				$changes[ $key ] = array(
					'old' => null,
					'new' => $new_value,
				);
				continue;
			}

			if ( self::values_differ( $old_value, $new_value ) ) {
				$changes[ $key ] = array(
					'old' => $old_value,
					'new' => $new_value,
				);
			}
		}

		return $changes;
	}

	/**
	 * Check if two values differ.
	 *
	 * Handles type-safe comparison including null checks.
	 *
	 * @param mixed $old_value Old value.
	 * @param mixed $new_value New value.
	 *
	 * @return bool True if values differ, false otherwise.
	 */
	private static function values_differ( $old_value, $new_value ): bool {
		if ( null === $old_value && null === $new_value ) {
			return false;
		}

		if ( null === $old_value || null === $new_value ) {
			return true;
		}

		if ( is_array( $old_value ) && is_array( $new_value ) ) {
			// phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.serialize_serialize
			return serialize( $old_value ) !== serialize( $new_value );
		}

		if ( is_object( $old_value ) && is_object( $new_value ) ) {
			// phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.serialize_serialize
			return serialize( $old_value ) !== serialize( $new_value );
		}

		return (string) $old_value !== (string) $new_value;
	}

	/**
	 * Extract only changed values from diff result.
	 *
	 * @param array $diff Diff result from self::diff().
	 *
	 * @return array Array with only new values: ['key' => new_value].
	 */
	public static function extract_new_values( array $diff ): array {
		return array_map(
			static function ( $change ) {
				return $change['new'];
			},
			$diff
		);
	}

	/**
	 * Filter array to include only specified keys.
	 *
	 * @param array $data Source array.
	 * @param array $keys Keys to keep.
	 *
	 * @return array Filtered array.
	 */
	public static function only( array $data, array $keys ): array {
		return array_intersect_key( $data, array_flip( $keys ) );
	}

	/**
	 * Filter array to exclude specified keys.
	 *
	 * @param array $data Source array.
	 * @param array $keys Keys to remove.
	 *
	 * @return array Filtered array.
	 */
	public static function except( array $data, array $keys ): array {
		return array_diff_key( $data, array_flip( $keys ) );
	}
}
