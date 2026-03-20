<?php

declare( strict_types=1 );

namespace Ilabs\Inpost_Pay\Lib;

use ErrorException;
use JsonSerializable;

class Item implements JsonSerializable {
	/**
	 * Magic setter with validation.
	 *
	 * @param string $property Property name.
	 * @param mixed $value Value to set.
	 *
	 * @throws ErrorException If property does not exist.
	 */
	public function __set( $property, $value ) {
		if ( property_exists( $this, $property ) ) {
			switch ( $property ) {
				case 'quantity':
				case 'available_quantity':
				case 'max_quantity':
				case 'quantity_jump':
					$this->$property = (int) $value;
					break;
				default:
					$this->$property = $value;
			}
		} else {
			$this->throwNonExistent( $property );
		}
	}

	/**
	 * Magic isset checker.
	 *
	 * @param string $property Property name.
	 *
	 * @return bool
	 */
	public function __isset( $property ): bool {
		return property_exists( $this, $property );
	}

	/**
	 * Magic getter with validation.
	 *
	 * @param string $property Property name.
	 *
	 * @return mixed
	 * @throws ErrorException If property does not exist.
	 */
	public function __get( $property ) {
		if ( property_exists( $this, $property ) ) {
			return $this->$property;
		}

		$this->throwNonExistent( $property );
	}

	/**
	 * Converts the object to array using autoSerialize if available.
	 *
	 * @return array
	 */
	public function toArray(): array {
		return method_exists( $this, 'autoSerialize' )
			? $this->autoSerialize()
			: get_object_vars( $this );
	}

	/**
	 * Encodes the object as JSON using wp_json_encode().
	 *
	 * @return string JSON representation.
	 */
	public function encode(): string {
		return wp_json_encode( $this );
	}

	/**
	 * Returns the 'products' property from the object, if defined.
	 *
	 * @return mixed
	 */
	public function getProducts() {
		$data = $this->toArray();

		return $data['products'] ?? [];
	}

	/**
	 * Compares given product array to the current one.
	 *
	 * @param mixed $product Product array to compare.
	 *
	 * @return bool
	 */
	public function compareProduct( $product ): bool {
		return wp_json_encode( $this->getProducts() ) === wp_json_encode( $product );
	}

	/**
	 * Throws exception for undefined properties.
	 *
	 * @param string $property Property name.
	 *
	 * @throws ErrorException
	 */
	protected function throwNonExistent( string $property ): void {
		$class = get_class( $this );
		throw new ErrorException( "Property not existing {$property} in {$class}" );
	}

	/**
	 * Returns data to be serialized by json_encode().
	 *
	 * @return array
	 */
	public function jsonSerialize(): array {
		return $this->toArray();
	}
}
