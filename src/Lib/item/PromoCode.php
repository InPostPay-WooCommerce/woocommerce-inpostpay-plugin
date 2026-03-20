<?php

declare( strict_types=1 );

namespace Ilabs\Inpost_Pay\Lib\item;

use Ilabs\Inpost_Pay\Lib\helpers\JsonSerializationHelper;
use Ilabs\Inpost_Pay\Lib\Item;
use JsonSerializable;

class PromoCode extends Item implements JsonSerializable {
	use JsonSerializationHelper;

	protected string $name;
	protected string $promo_code_value;
	protected string $regulation_type;

	public function jsonSerialize(): array {
		return $this->autoSerialize();
	}

	public function get_name(): string {
		return $this->name;
	}

	public function set_name( string $name ): self {
		$this->name = $name;

		return $this;
	}

	public function get_promo_code_value(): string {
		return $this->promo_code_value;
	}

	public function set_promo_code_value( string $promo_code_value ): self {
		$this->promo_code_value = $promo_code_value;

		return $this;
	}

	public function get_regulation_type(): string {
		return $this->regulation_type;
	}

	public function set_regulation_type( string $regulation_type ): self {
		$this->regulation_type = $regulation_type;

		return $this;
	}
}
