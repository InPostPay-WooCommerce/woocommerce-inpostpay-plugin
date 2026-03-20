<?php

declare( strict_types=1 );

namespace Ilabs\Inpost_Pay\Lib\item;

use Ilabs\Inpost_Pay\Lib\helpers\JsonSerializationHelper;
use Ilabs\Inpost_Pay\Lib\Item;
use JsonSerializable;

class Variant extends Item implements JsonSerializable {
	use JsonSerializationHelper;

	protected int $variant_id;
	protected string $variant_name;
	protected string $variant_description;
	protected string $variant_type;
	protected string $variant_values;

	public function jsonSerialize(): array {
		return $this->autoSerialize();
	}

	public function get_variant_id(): int {
		return $this->variant_id;
	}

	public function set_variant_id( int $variant_id ): self {
		$this->variant_id = $variant_id;

		return $this;
	}

	public function get_variant_name(): string {
		return $this->variant_name;
	}

	public function set_variant_name( string $variant_name ): self {
		$this->variant_name = $variant_name;

		return $this;
	}

	public function get_variant_description(): string {
		return $this->variant_description;
	}

	public function set_variant_description( string $variant_description ): self {
		$this->variant_description = $variant_description;

		return $this;
	}

	public function get_variant_type(): string {
		return $this->variant_type;
	}

	public function set_variant_type( string $variant_type ): self {
		$this->variant_type = $variant_type;

		return $this;
	}

	public function get_variant_values(): string {
		return $this->variant_values;
	}

	public function set_variant_values( string $variant_values ): self {
		$this->variant_values = $variant_values;

		return $this;
	}

}
