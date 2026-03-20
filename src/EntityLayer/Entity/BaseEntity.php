<?php
/**
 * Base entity class for all database entities.
 *
 * @package Ilabs\Inpost_Pay\EntityLayer\Entity
 */

declare( strict_types=1 );

namespace Ilabs\Inpost_Pay\EntityLayer\Entity;

use Ilabs\Inpost_Pay\Exception\InvalidEntityException;

/**
 * Abstract base class for entities.
 *
 * Provides automatic getters/setters via magic methods,
 * hydration from database results, and conversion to array.
 */
abstract class BaseEntity implements EntityInterface {

	/**
	 * Table name (without prefix).
	 *
	 * Must be defined in child classes.
	 *
	 * @var string
	 */
	protected static string $table = '';

	/**
	 * Primary key column name.
	 *
	 * @var string
	 */
	protected static string $primary_key = 'id';

	/**
	 * Get the table name for this entity.
	 *
	 * @return string
	 *
	 * @throws InvalidEntityException If table name is not defined.
	 */
	public static function get_table_name(): string {
		if ( empty( static::$table ) ) {
			throw InvalidEntityException::missing_table_name( static::class );
		}

		return static::$table;
	}

	/**
	 * Get the primary key column name.
	 *
	 * @return string
	 */
	public static function get_primary_key(): string {
		return static::$primary_key;
	}

	/**
	 * Magic getter for entity properties.
	 *
	 * Automatically calls getPropertyName() if exists, otherwise returns property directly.
	 *
	 * @param string $name Property name.
	 *
	 * @return mixed
	 *
	 * @throws InvalidEntityException If property does not exist.
	 */
	public function __get( string $name ) {
		$method = 'get_' . $name;

		if ( method_exists( $this, $method ) ) {
			return $this->$method();
		}

		if ( property_exists( $this, $name ) ) {
			return $this->$name;
		}

		throw new InvalidEntityException(
			sprintf( 'Property "%s" does not exist in entity "%s".', esc_html( $name ), esc_html( static::class ) )
		);
	}

	/**
	 * Magic setter for entity properties.
	 *
	 * Automatically calls setPropertyName($value) if exists, otherwise sets property directly.
	 *
	 * @param string $name  Property name.
	 * @param mixed  $value Property value.
	 *
	 * @return void
	 *
	 * @throws InvalidEntityException If property does not exist.
	 */
	public function __set( string $name, $value ): void {
		$method = 'set_' . $name;

		if ( method_exists( $this, $method ) ) {
			$this->$method( $value );

			return;
		}

		if ( property_exists( $this, $name ) ) {
			$this->$name = $value;

			return;
		}

		throw new InvalidEntityException(
			sprintf( 'Property "%s" does not exist in entity "%s".', esc_html( $name ), esc_html( static::class ) )
		);
	}

	/**
	 * Magic isset checker for entity properties.
	 *
	 * @param string $name Property name.
	 *
	 * @return bool
	 */
	public function __isset( string $name ): bool {
		return property_exists( $this, $name ) && null !== $this->$name;
	}

	/**
	 * Convert entity to associative array.
	 *
	 * @param bool $include_null Whether to include null values.
	 *
	 * @return array
	 */
	public function to_array( bool $include_null = true ): array {
		$data       = array();
		$reflection = new \ReflectionClass( $this );

		foreach ( $reflection->getProperties() as $property ) {
			if ( $property->isStatic() ) {
				continue;
			}

			$property->setAccessible( true );
			$name  = $property->getName();
			$value = $property->getValue( $this );

			if ( ! $include_null && null === $value ) {
				continue;
			}

			$data[ $name ] = $value;
		}

		return $data;
	}

	/**
	 * Hydrate entity from database result or array.
	 *
	 * @param array|object $data Data from database.
	 *
	 * @return static
	 */
	public static function from_array( $data ): self {
		$instance   = new static();
		$data       = (array) $data;
		$reflection = new \ReflectionClass( $instance );

		foreach ( $data as $key => $value ) {
			if ( ! property_exists( $instance, $key ) ) {
				continue;
			}

			$property = $reflection->getProperty( $key );
			$property->setAccessible( true );

			$type = $property->getType();

			if ( $type && null === $value && ! $type->allowsNull() ) {
				continue; // Skip null for non-nullable properties.
			}

			if ( $type instanceof \ReflectionNamedType ) {
				$type_name = $type->getName();

				switch ( $type_name ) {
					case 'int':
						$value = null !== $value ? (int) $value : null;
						break;
					case 'float':
						$value = null !== $value ? (float) $value : null;
						break;
					case 'bool':
						$value = null !== $value ? (bool) $value : null;
						break;
					case 'string':
						$value = null !== $value ? (string) $value : null;
						break;
				}
			}

			$property->setValue( $instance, $value );
		}

		return $instance;
	}

	/**
	 * Get the primary key value of this entity.
	 *
	 * @return mixed
	 */
	public function get_id() {
		$primary_key = static::get_primary_key();

		if ( ! property_exists( $this, $primary_key ) ) {
			return null;
		}

		return $this->$primary_key;
	}

	/**
	 * Set the primary key value of this entity.
	 *
	 * @param mixed $value Primary key value.
	 *
	 * @return void
	 */
	public function set_id( $value ): void {
		$primary_key = static::get_primary_key();

		if ( property_exists( $this, $primary_key ) ) {
			$this->$primary_key = $value;
		}
	}

	/**
	 * Check if entity has been persisted (has primary key).
	 *
	 * @return bool
	 */
	public function is_persisted(): bool {
		$id = $this->get_id();

		return null !== $id && ( is_int( $id ) ? $id > 0 : ! empty( $id ) );
	}
}
