<?php
/**
 * Order delivery item.
 *
 * @package Inpost_Pay
 */

declare( strict_types=1 );

namespace Ilabs\Inpost_Pay\Lib\item\order;

use Ilabs\Inpost_Pay\Lib\helpers\JsonSerializationHelper;
use Ilabs\Inpost_Pay\Lib\Item;

/**
 * Represents order delivery.
 */
class Delivery extends Item implements \JsonSerializable {
	use JsonSerializationHelper;

	/**
	 * Delivery type.
	 *
	 * @var string
	 */
	protected string $delivery_type;

	/**
	 * Delivery price.
	 *
	 * @var array
	 */
	protected array $delivery_price;

	/**
	 * Delivery options.
	 *
	 * @var array
	 */
	protected array $delivery_options;

	/**
	 * Email address.
	 *
	 * @var string
	 */
	protected string $mail;

	/**
	 * Phone.
	 *
	 * @var Phone
	 */
	protected Phone $phone;

	/**
	 * Delivery point.
	 *
	 * @var string
	 */
	protected string $delivery_point;

	/**
	 * Delivery address.
	 *
	 * @var DeliveryAddress
	 */
	protected DeliveryAddress $delivery_address;

	/**
	 * Courier note.
	 *
	 * @var string
	 */
	protected string $courier_note;

	/**
	 * Serialize item to array.
	 *
	 * @return array
	 */
	public function jsonSerialize(): array {
		return $this->auto_serialize();
	}

	/**
	 * Get delivery type.
	 *
	 * @return string
	 */
	public function get_delivery_type(): string {
		return $this->delivery_type;
	}

	/**
	 * Set delivery type.
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
	 * Get delivery price.
	 *
	 * @return array
	 */
	public function get_delivery_price(): array {
		return $this->delivery_price;
	}

	/**
	 * Set delivery price.
	 *
	 * @param array $delivery_price Delivery price.
	 *
	 * @return self
	 */
	public function set_delivery_price( array $delivery_price ): self {
		$this->delivery_price = $delivery_price;

		return $this;
	}

	/**
	 * Get delivery options.
	 *
	 * @return array
	 */
	public function get_delivery_options(): array {
		return $this->delivery_options;
	}

	/**
	 * Set delivery options.
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
	 * Get email address.
	 *
	 * @return string
	 */
	public function get_mail(): string {
		return $this->mail;
	}

	/**
	 * Set email address.
	 *
	 * @param string $mail Email address.
	 *
	 * @return self
	 */
	public function set_mail( string $mail ): self {
		$this->mail = $mail;

		return $this;
	}

	/**
	 * Get phone.
	 *
	 * @return Phone
	 */
	public function get_phone(): Phone {
		return $this->phone;
	}

	/**
	 * Set phone.
	 *
	 * @param Phone $phone Phone.
	 *
	 * @return self
	 */
	public function set_phone( Phone $phone ): self {
		$this->phone = $phone;

		return $this;
	}

	/**
	 * Get delivery point.
	 *
	 * @return string
	 */
	public function get_delivery_point(): string {
		return $this->delivery_point;
	}

	/**
	 * Set delivery point.
	 *
	 * @param string $delivery_point Delivery point.
	 *
	 * @return self
	 */
	public function set_delivery_point( string $delivery_point ): self {
		$this->delivery_point = $delivery_point;

		return $this;
	}

	/**
	 * Get delivery address.
	 *
	 * @return DeliveryAddress
	 */
	public function get_delivery_address(): DeliveryAddress {
		return $this->delivery_address;
	}

	/**
	 * Set delivery address.
	 *
	 * @param DeliveryAddress $delivery_address Delivery address.
	 *
	 * @return self
	 */
	public function set_delivery_address( DeliveryAddress $delivery_address ): self {
		$this->delivery_address = $delivery_address;

		return $this;
	}

	/**
	 * Get courier note.
	 *
	 * @return string
	 */
	public function get_courier_note(): string {
		return $this->courier_note;
	}

	/**
	 * Set courier note.
	 *
	 * @param string $courier_note Courier note.
	 *
	 * @return self
	 */
	public function set_courier_note( string $courier_note ): self {
		$this->courier_note = $courier_note;

		return $this;
	}
}
