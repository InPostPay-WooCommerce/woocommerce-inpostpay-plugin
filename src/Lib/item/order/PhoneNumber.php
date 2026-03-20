<?php

declare( strict_types=1 );

namespace Ilabs\Inpost_Pay\Lib\item\order;

use Ilabs\Inpost_Pay\Lib\helpers\JsonSerializationHelper;
use Ilabs\Inpost_Pay\Lib\Item;

class PhoneNumber extends Item implements \JsonSerializable {
	use JsonSerializationHelper;

	protected string $country_prefix;
	protected string $phone;

	public function jsonSerialize(): array {
		return $this->autoSerialize();
	}

	public function get_country_prefix(): string {
		return $this->country_prefix;
	}

	public function set_country_prefix( string $country_prefix ): self {
		$this->country_prefix = $country_prefix;

		return $this;
	}

	public function get_phone(): string {
		return $this->phone;
	}

	public function set_phone( string $phone ): self {
		$this->phone = $phone;

		return $this;
	}
}
