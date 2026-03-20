<?php

declare( strict_types=1 );

namespace Ilabs\Inpost_Pay\Lib\item\order;

use Ilabs\Inpost_Pay\Lib\helpers\JsonSerializationHelper;
use Ilabs\Inpost_Pay\Lib\Item;

class DeliveryAddress extends Item implements \JsonSerializable {
	use JsonSerializationHelper;

	protected string $name;
	protected string $country_code;
	protected string $address;
	protected string $city;
	protected string $postal_code;

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

	public function get_country_code(): string {
		return $this->country_code;
	}

	public function set_country_code( string $country_code ): self {
		$this->country_code = $country_code;

		return $this;
	}

	public function get_address(): string {
		return $this->address;
	}

	public function set_address( string $address ): self {
		$this->address = $address;

		return $this;
	}

	public function get_city(): string {
		return $this->city;
	}

	public function set_city( string $city ): self {
		$this->city = $city;

		return $this;
	}

	public function get_postal_code(): string {
		return $this->postal_code;
	}

	public function set_postal_code( string $postal_code ): self {
		$this->postal_code = $postal_code;

		return $this;
	}
}
