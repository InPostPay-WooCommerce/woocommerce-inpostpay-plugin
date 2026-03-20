<?php

declare( strict_types=1 );

namespace Ilabs\Inpost_Pay\Lib\item;

use Ilabs\Inpost_Pay\Lib\helpers\JsonSerializationHelper;
use Ilabs\Inpost_Pay\Lib\Item;
use JsonSerializable;

class Quantity extends Item implements JsonSerializable {

	use JsonSerializationHelper;

	protected int $quantity;
	protected string $quantity_type;
	protected string $quantity_unit;
	protected int $available_quantity;
	protected int $max_quantity;
	protected int $quantity_jump = 1;

	public function jsonSerialize(): array {
		return $this->autoSerialize();
	}

	public function get_quantity(): int {
		return $this->quantity;
	}

	public function set_quantity( int $quantity ): void {
		$this->quantity = $quantity;
	}

	public function get_quantity_type(): string {
		return $this->quantity_type;
	}

	public function set_quantity_type( string $quantity_type ): void {
		$this->quantity_type = $quantity_type;
	}

	public function get_quantity_unit(): string {
		return $this->quantity_unit;
	}

	public function set_quantity_unit( string $quantity_unit ): void {
		$this->quantity_unit = $quantity_unit;
	}

	public function get_available_quantity(): int {
		return $this->available_quantity;
	}

	public function set_available_quantity( int $available_quantity ): void {
		$this->available_quantity = $available_quantity;
	}

	public function get_max_quantity(): int {
		return $this->max_quantity;
	}

	public function set_max_quantity( int $max_quantity ): void {
		$this->max_quantity = $max_quantity;
	}

	public function get_quantity_jump(): int {
		return $this->quantity_jump;
	}

	public function set_quantity_jump( int $quantity_jump ): void {
		$this->quantity_jump = $quantity_jump;
	}
}
