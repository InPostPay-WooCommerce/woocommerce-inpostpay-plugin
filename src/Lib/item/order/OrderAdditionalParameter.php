<?php

declare( strict_types=1 );

namespace Ilabs\Inpost_Pay\Lib\item\order;

use Ilabs\Inpost_Pay\Lib\helpers\JsonSerializationHelper;
use Ilabs\Inpost_Pay\Lib\Item;

class OrderAdditionalParameter extends Item implements \JsonSerializable {
	use JsonSerializationHelper;

	protected string $key;
	protected string $value;

	public function __construct( string $key, string $value ) {
		$this->key   = $key;
		$this->value = $value;
	}

	public function jsonSerialize(): array {
		return $this->autoSerialize();
	}

	public function get_key(): string {
		return $this->key;
	}

	public function set_key( string $key ): self {
		$this->key = $key;

		return $this;
	}

	public function get_value(): string {
		return $this->value;
	}

	public function set_value( string $value ): self {
		$this->value = $value;

		return $this;
	}
}
