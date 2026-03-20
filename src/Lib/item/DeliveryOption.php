<?php

declare( strict_types=1 );

namespace Ilabs\Inpost_Pay\Lib\item;

use Ilabs\Inpost_Pay\Lib\helpers\JsonSerializationHelper;
use Ilabs\Inpost_Pay\Lib\Item;
use JsonSerializable;

class DeliveryOption extends Item implements JsonSerializable {
	use JsonSerializationHelper;

	protected ?string $delivery_name;
	protected string $delivery_code_value;
	protected Price $delivery_option_price;

	public function jsonSerialize(): array {
		return $this->autoSerialize();
	}

	public function get_delivery_name(): ?string {
		return $this->delivery_name;
	}

	public function set_delivery_name( ?string $delivery_name ): self {
		$this->delivery_name = $delivery_name;

		return $this;
	}

	public function get_delivery_code_value(): string {
		return $this->delivery_code_value;
	}

	public function set_delivery_code_value( string $delivery_code_value ): self {
		$this->delivery_code_value = $delivery_code_value;

		return $this;
	}

	public function get_delivery_option_price(): Price {
		return $this->delivery_option_price;
	}

	public function set_delivery_option_price( Price $delivery_option_price ): self {
		$this->delivery_option_price = $delivery_option_price;

		return $this;
	}
}
