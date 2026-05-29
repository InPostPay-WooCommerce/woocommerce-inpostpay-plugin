<?php
/**
 * Trait: JsonSerializationHelper.
 *
 * Provides automatic JSON serialization of object properties,
 * including UTF-8 encoding fixes and null-value filtering based on default values.
 *
 * @package Ilabs\Inpost_Pay\Lib\helpers
 */

namespace Ilabs\Inpost_Pay\Lib\helpers;

use JsonSerializable;
use ReflectionObject;

/**
 * Trait JsonSerializationHelper.
 *
 * @since 2.0.3
 */
trait JsonSerializationHelper {

	/**
	 * Automatically serializes object properties to an array.
	 *
	 * Fields starting with underscore are skipped.
	 * Null fields are only included if their default value is explicitly set to null.
	 *
	 * @return array
	 */
	public function auto_serialize(): array {
		$data     = array();
		$reflect  = new ReflectionObject( $this );
		$defaults = $reflect->getDefaultProperties();

		foreach ( get_object_vars( $this ) as $key => $value ) {
			if ( str_starts_with( $key, '_' ) ) {
				continue;
			}

			if ( null === $value && ( ! array_key_exists( $key, $defaults ) || null !== $defaults[ $key ] ) ) {
				continue;
			}

			$data[ $key ] = $this->serialize_item( $value );
		}

		return $data;
	}

	/**
	 * Serializes a single value based on its type.
	 *
	 * Strings are UTF-8 encoded.
	 * JsonSerializable objects are serialized using jsonSerialize().
	 * Arrays are recursively serialized.
	 * Objects with __toString are cast to string and encoded.
	 * Other values are returned as-is.
	 *
	 * @param mixed $item Value to serialize.
	 *
	 * @return mixed
	 */
	protected function serialize_item( $item ) {
		if ( is_string( $item ) ) {
			return mb_convert_encoding( $item, 'UTF-8', 'UTF-8' );
		}

		if ( $item instanceof JsonSerializable ) {
			return $item->jsonSerialize();
		}

		if ( is_array( $item ) ) {
			return $this->serialize_array( $item );
		}

		if ( is_object( $item ) ) {
			return method_exists( $item, '__toString' )
				? mb_convert_encoding( (string) $item, 'UTF-8', 'UTF-8' )
				: (array) $item;
		}

		return $item;
	}

	/**
	 * Serializes an array of items.
	 *
	 * @param array|null $items Array of items to serialize.
	 *
	 * @return array
	 */
	protected function serialize_array( ?array $items ): array {
		if ( ! is_array( $items ) ) {
			return array();
		}

		return array_map(
			function ( $i ) {
				return $this->serialize_item( $i );
			},
			$items
		);
	}
}
