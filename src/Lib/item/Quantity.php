<?php
/**
 * Quantity item.
 *
 * @package Ilabs\Inpost_Pay\Lib\item
 */

declare( strict_types=1 );

namespace Ilabs\Inpost_Pay\Lib\item;

use Ilabs\Inpost_Pay\Lib\helpers\JsonSerializationHelper;
use Ilabs\Inpost_Pay\Lib\Item;
use JsonSerializable;

/**
 * Represents product quantity data.
 */
class Quantity extends Item implements JsonSerializable {

	use JsonSerializationHelper;

	protected int $quantity;
	protected string $quantity_type;
	protected string $quantity_unit;
	protected int $available_quantity;
	protected int $max_quantity;
	protected int $quantity_jump = 1;

	/**
	 * Serializes object to JSON-compatible array.
	 *
	 * @return array
	 */
	public function jsonSerialize(): array {
		return $this->auto_serialize();
	}

	/**
	 * Returns quantity value.
	 *
	 * @return int
	 */
	public function get_quantity(): int {
		return $this->quantity;
	}

	/**
	 * Sets quantity value.
	 *
	 * @param int $quantity Quantity.
	 *
	 * @return void
	 */
	public function set_quantity( int $quantity ): void {
		$this->quantity = $quantity;
	}

	/**
	 * Returns quantity type.
	 *
	 * @return string
	 */
	public function get_quantity_type(): string {
		return $this->quantity_type;
	}

	/**
	 * Sets quantity type.
	 *
	 * @param string $quantity_type Quantity type.
	 *
	 * @return void
	 */
	public function set_quantity_type( string $quantity_type ): void {
		$this->quantity_type = $quantity_type;
	}

	/**
	 * Returns quantity unit.
	 *
	 * @return string
	 */
	public function get_quantity_unit(): string {
		return $this->quantity_unit;
	}

	/**
	 * Sets quantity unit.
	 *
	 * @param string $quantity_unit Quantity unit.
	 *
	 * @return void
	 */
	public function set_quantity_unit( string $quantity_unit ): void {
		$this->quantity_unit = $quantity_unit;
	}

	/**
	 * Returns available quantity.
	 *
	 * @return int
	 */
	public function get_available_quantity(): int {
		return $this->available_quantity;
	}

	/**
	 * Sets available quantity.
	 *
	 * @param int $available_quantity Available quantity.
	 *
	 * @return void
	 */
	public function set_available_quantity( int $available_quantity ): void {
		$this->available_quantity = $available_quantity;
	}

	/**
	 * Returns maximum quantity.
	 *
	 * @return int
	 */
	public function get_max_quantity(): int {
		return $this->max_quantity;
	}

	/**
	 * Sets maximum quantity.
	 *
	 * @param int $max_quantity Maximum quantity.
	 *
	 * @return void
	 */
	public function set_max_quantity( int $max_quantity ): void {
		$this->max_quantity = $max_quantity;
	}

	/**
	 * Returns quantity jump value.
	 *
	 * @return int
	 */
	public function get_quantity_jump(): int {
		return $this->quantity_jump;
	}

	/**
	 * Sets quantity jump value.
	 *
	 * @param int $quantity_jump Quantity jump.
	 *
	 * @return void
	 */
	public function set_quantity_jump( int $quantity_jump ): void {
		$this->quantity_jump = $quantity_jump;
	}
}
