<?php
/**
 * Entity interface.
 *
 * @package Ilabs\WpEntityLayer\Entity
 */

declare( strict_types=1 );

namespace Ilabs\Inpost_Pay\EntityLayer\Entity;

/**
 * Interface for all entities.
 */
interface EntityInterface {

	/**
	 * Get the table name for this entity.
	 *
	 * @return string
	 */
	public static function get_table_name(): string;

	/**
	 * Get the primary key column name.
	 *
	 * @return string
	 */
	public static function get_primary_key(): string;

	/**
	 * Convert entity to associative array.
	 *
	 * @param bool $include_null Whether to include null values.
	 * @return array
	 */
	public function to_array( bool $include_null = true ): array;

	/**
	 * Hydrate entity from database result or array.
	 *
	 * @param array|object $data Data from database.
	 * @return static
	 */
	public static function from_array( $data ): self;

	/**
	 * Get the primary key value of this entity.
	 *
	 * @return mixed
	 */
	public function get_id();

	/**
	 * Set the primary key value of this entity.
	 *
	 * @param mixed $value Primary key value.
	 * @return void
	 */
	public function set_id( $value ): void;

	/**
	 * Check if entity has been persisted (has primary key).
	 *
	 * @return bool
	 */
	public function is_persisted(): bool;
}
