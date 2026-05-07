<?php

declare( strict_types=1 );

namespace Ilabs\Inpost_Pay\Lib\item;

use Ilabs\Inpost_Pay\Lib\helpers\JsonSerializationHelper;
use Ilabs\Inpost_Pay\Lib\Item;
use JsonSerializable;

class Delivery extends Item implements JsonSerializable {
	use JsonSerializationHelper;

	protected string $delivery_type;
	protected string $delivery_date;
	protected array $delivery_options;
	protected Price $delivery_price;

	// protected $free_delivery_minimum_gross_price;

	public function jsonSerialize(): array {
		return $this->autoSerialize();
	}

	public function get_delivery_type(): string {
		return $this->delivery_type;
	}

	public function set_delivery_type( string $delivery_type ): self {
		$this->delivery_type = $delivery_type;

		return $this;
	}

	public function get_delivery_date(): string {
		return $this->delivery_date;
	}

	public function set_delivery_date( string $delivery_date ): self {
		$this->delivery_date = $delivery_date;

		return $this;
	}

	public function get_delivery_options(): array {
		return $this->delivery_options;
	}

	public function set_delivery_options( array $delivery_options ): self {
		$this->delivery_options = $delivery_options;

		return $this;
	}

	public function get_delivery_price(): Price {
		return $this->delivery_price;
	}

	public function set_delivery_price( Price $delivery_price ): self {
		$this->delivery_price = $delivery_price;

		return $this;
	}
}
