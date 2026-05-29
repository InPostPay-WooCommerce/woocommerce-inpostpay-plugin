<?php
/**
 * Delivery option item.
 *
 * @package Ilabs\Inpost_Pay\Lib\item
 */

declare( strict_types=1 );

namespace Ilabs\Inpost_Pay\Lib\item;

use Ilabs\Inpost_Pay\Lib\helpers\JsonSerializationHelper;
use Ilabs\Inpost_Pay\Lib\Item;
use JsonSerializable;

/**
 * Represents a single delivery option with name, code, and price.
 */
class DeliveryOption extends Item implements JsonSerializable {
	use JsonSerializationHelper;

	protected ?string $delivery_name;
	protected string $delivery_code_value;
	protected Price $delivery_option_price;

	/**
	 * Serializes object to JSON-compatible array.
	 *
	 * @return array
	 */
	public function jsonSerialize(): array {
		return $this->auto_serialize();
	}

	/**
	 * Returns delivery name.
	 *
	 * @return string|null
	 */
	public function get_delivery_name(): ?string {
		return $this->delivery_name;
	}

	/**
	 * Sets delivery name.
	 *
	 * @param string|null $delivery_name Delivery name.
	 *
	 * @return self
	 */
	public function set_delivery_name( ?string $delivery_name ): self {
		$this->delivery_name = $delivery_name;

		return $this;
	}

	/**
	 * Returns delivery code value.
	 *
	 * @return string
	 */
	public function get_delivery_code_value(): string {
		return $this->delivery_code_value;
	}

	/**
	 * Sets delivery code value.
	 *
	 * @param string $delivery_code_value Delivery code value.
	 *
	 * @return self
	 */
	public function set_delivery_code_value( string $delivery_code_value ): self {
		$this->delivery_code_value = $delivery_code_value;

		return $this;
	}

	/**
	 * Returns delivery option price.
	 *
	 * @return Price
	 */
	public function get_delivery_option_price(): Price {
		return $this->delivery_option_price;
	}

	/**
	 * Sets delivery option price.
	 *
	 * @param Price $delivery_option_price Delivery option price.
	 *
	 * @return self
	 */
	public function set_delivery_option_price( Price $delivery_option_price ): self {
		$this->delivery_option_price = $delivery_option_price;

		return $this;
	}
}
