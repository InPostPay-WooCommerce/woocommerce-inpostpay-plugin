<?php
/**
 * Delivery item.
 *
 * @package Ilabs\Inpost_Pay\Lib\item
 */

declare( strict_types=1 );

namespace Ilabs\Inpost_Pay\Lib\item;

use Ilabs\Inpost_Pay\Lib\helpers\JsonSerializationHelper;
use Ilabs\Inpost_Pay\Lib\Item;
use JsonSerializable;

/**
 * Represents delivery configuration for the basket.
 */
class Delivery extends Item implements JsonSerializable {
	use JsonSerializationHelper;

	protected string $delivery_type;
	protected string $delivery_date;
	protected array $delivery_options;
	protected Price $delivery_price;

	/**
	 * Serializes object to JSON-compatible array.
	 *
	 * @return array
	 */
	public function jsonSerialize(): array {
		return $this->auto_serialize();
	}

	/**
	 * Returns delivery type.
	 *
	 * @return string
	 */
	public function get_delivery_type(): string {
		return $this->delivery_type;
	}

	/**
	 * Sets delivery type.
	 *
	 * @param string $delivery_type Delivery type.
	 *
	 * @return self
	 */
	public function set_delivery_type( string $delivery_type ): self {
		$this->delivery_type = $delivery_type;

		return $this;
	}

	/**
	 * Returns delivery date.
	 *
	 * @return string
	 */
	public function get_delivery_date(): string {
		return $this->delivery_date;
	}

	/**
	 * Sets delivery date.
	 *
	 * @param string $delivery_date Delivery date.
	 *
	 * @return self
	 */
	public function set_delivery_date( string $delivery_date ): self {
		$this->delivery_date = $delivery_date;

		return $this;
	}

	/**
	 * Returns delivery options.
	 *
	 * @return array
	 */
	public function get_delivery_options(): array {
		return $this->delivery_options;
	}

	/**
	 * Sets delivery options.
	 *
	 * @param array $delivery_options Delivery options.
	 *
	 * @return self
	 */
	public function set_delivery_options( array $delivery_options ): self {
		$this->delivery_options = $delivery_options;

		return $this;
	}

	/**
	 * Returns delivery price.
	 *
	 * @return Price
	 */
	public function get_delivery_price(): Price {
		return $this->delivery_price;
	}

	/**
	 * Sets delivery price.
	 *
	 * @param Price $delivery_price Delivery price.
	 *
	 * @return self
	 */
	public function set_delivery_price( Price $delivery_price ): self {
		$this->delivery_price = $delivery_price;

		return $this;
	}
}
