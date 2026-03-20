<?php
/**
 * Entity representing order alias mapping.
 *
 * @package Ilabs\Inpost_Pay\EntityLayer\Entity
 */

declare( strict_types=1 );

namespace Ilabs\Inpost_Pay\EntityLayer\Entity;

/**
 * Class OrderAliasEntity
 *
 * Represents alias mapping between external and internal order IDs.
 */
class OrderAliasEntity extends BaseEntity {

	/**
	 * Table name for order aliases.
	 *
	 * @var string
	 */
	protected static string $table = 'izi_order_aliases';

	/**
	 * Primary key column name.
	 *
	 * @var string
	 */
	protected static string $primary_key = 'id';

	/**
	 * Unique identifier.
	 *
	 * @var int|null
	 */
	protected ?int $id = null;

	/**
	 * External alias order identifier.
	 *
	 * @var string|null
	 */
	protected ?string $alias_order_id = null;

	/**
	 * Internal WooCommerce order ID.
	 *
	 * @var int|null
	 */
	protected ?int $order_id = null;

	/**
	 * Record creation timestamp.
	 *
	 * @var string|null
	 */
	protected ?string $created_at = null;

	/**
	 * Get the ID.
	 *
	 * @return int|null Entity ID.
	 */
	public function get_id(): ?int {
		return $this->id;
	}

	/**
	 * Set the ID.
	 *
	 * @param int|null $value Entity ID.
	 *
	 * @return void
	 */
	public function set_id( $value ): void {
		$this->id = $value;
	}

	/**
	 * Get the alias order ID.
	 *
	 * @return string|null Alias order ID.
	 */
	public function get_alias_order_id(): ?string {
		return $this->alias_order_id;
	}

	/**
	 * Set the alias order ID.
	 *
	 * @param string|null $value Alias order ID.
	 *
	 * @return void
	 */
	public function set_alias_order_id( ?string $value ): void {
		$this->alias_order_id = $value;
	}

	/**
	 * Get the internal order ID.
	 *
	 * @return int|null Internal order ID.
	 */
	public function get_order_id(): ?int {
		return $this->order_id;
	}

	/**
	 * Set the internal order ID.
	 *
	 * @param int|null $value Internal order ID.
	 *
	 * @return void
	 */
	public function set_order_id( ?int $value ): void {
		$this->order_id = $value;
	}

	/**
	 * Get record creation timestamp.
	 *
	 * @return string|null Creation date.
	 */
	public function get_created_at(): ?string {
		return $this->created_at;
	}

	/**
	 * Set record creation timestamp.
	 *
	 * @param string|null $value Creation date.
	 *
	 * @return void
	 */
	public function set_created_at( ?string $value ): void {
		$this->created_at = $value;
	}
}
