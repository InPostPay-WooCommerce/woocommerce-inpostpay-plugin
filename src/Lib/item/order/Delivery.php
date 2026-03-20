<?php

declare( strict_types=1 );

namespace Ilabs\Inpost_Pay\Lib\item\order;

use Ilabs\Inpost_Pay\Lib\helpers\JsonSerializationHelper;
use Ilabs\Inpost_Pay\Lib\Item;

class Delivery extends Item implements \JsonSerializable {
	use JsonSerializationHelper;

	protected string $delivery_type;
	protected array $delivery_price;
//	protected $delivery_date;
	protected array $delivery_options;
	protected string $mail;
	protected Phone $phone;
	protected string $delivery_point;
	protected DeliveryAddress $delivery_address;
	protected string $courier_note;

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

	public function get_delivery_price(): array {
		return $this->delivery_price;
	}

	public function set_delivery_price( array $delivery_price ): self {
		$this->delivery_price = $delivery_price;

		return $this;
	}

	public function get_delivery_options(): array {
		return $this->delivery_options;
	}

	public function set_delivery_options( array $delivery_options ): self {
		$this->delivery_options = $delivery_options;

		return $this;
	}

	public function get_mail(): string {
		return $this->mail;
	}

	public function set_mail( string $mail ): self {
		$this->mail = $mail;

		return $this;
	}

	public function get_phone(): Phone {
		return $this->phone;
	}

	public function set_phone( Phone $phone ): self {
		$this->phone = $phone;

		return $this;
	}

	public function get_delivery_point(): string {
		return $this->delivery_point;
	}

	public function set_delivery_point( string $delivery_point ): self {
		$this->delivery_point = $delivery_point;

		return $this;
	}

	public function get_delivery_address(): DeliveryAddress {
		return $this->delivery_address;
	}

	public function set_delivery_address( DeliveryAddress $delivery_address ): self {
		$this->delivery_address = $delivery_address;

		return $this;
	}

	public function get_courier_note(): string {
		return $this->courier_note;
	}

	public function set_courier_note( string $courier_note ): self {
		$this->courier_note = $courier_note;

		return $this;
	}
}
