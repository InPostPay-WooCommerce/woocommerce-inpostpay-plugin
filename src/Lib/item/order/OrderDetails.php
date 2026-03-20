<?php

declare( strict_types=1 );

namespace Ilabs\Inpost_Pay\Lib\item\order;

use Ilabs\Inpost_Pay\Lib\helpers\JsonSerializationHelper;
use Ilabs\Inpost_Pay\Lib\Item;
use Ilabs\Inpost_Pay\Lib\item\Price;

class OrderDetails extends Item implements \JsonSerializable {
	use JsonSerializationHelper;

	protected string $order_comments;
	protected ?string $basket_id = null;
	protected string $order_id;
	protected string $customer_order_id;
	protected string $pos_id;
	protected string $order_creation_date;
	protected string $order_update_date;
	protected string $merchant_id;
	protected string $payment_status;
	protected string $order_status;
	protected string $order_merchant_status_description;
	protected Price $order_base_price;
	protected Price $order_final_price;
	protected array $delivery_references_list;
	protected string $currency;
	protected string $payment_type;
	protected string $order_discount;
	protected array $order_additional_parameters;

	public function jsonSerialize(): array {
		return $this->autoSerialize();
	}

	public function get_order_comments(): string {
		return $this->order_comments;
	}

	public function set_order_comments( string $order_comments ): self {
		$this->order_comments = $order_comments;

		return $this;
	}

	public function get_basket_id(): ?string {
		return $this->basket_id;
	}

	public function set_basket_id( ?string $basket_id ): self {
		$this->basket_id = $basket_id;

		return $this;
	}

	public function get_order_id(): string {
		return $this->order_id;
	}

	public function set_order_id( string $order_id ): self {
		$this->order_id = $order_id;

		return $this;
	}

	public function get_customer_order_id(): string {
		return $this->customer_order_id;
	}

	public function set_customer_order_id( string $customer_order_id ): self {
		$this->customer_order_id = $customer_order_id;

		return $this;
	}

	public function get_pos_id(): string {
		return $this->pos_id;
	}

	public function set_pos_id( string $pos_id ): self {
		$this->pos_id = $pos_id;

		return $this;
	}

	public function get_order_creation_date(): string {
		return $this->order_creation_date;
	}

	public function set_order_creation_date( string $order_creation_date ): self {
		$this->order_creation_date = $order_creation_date;

		return $this;
	}

	public function get_order_update_date(): string {
		return $this->order_update_date;
	}

	public function set_order_update_date( string $order_update_date ): self {
		$this->order_update_date = $order_update_date;

		return $this;
	}

	public function get_merchant_id(): string {
		return $this->merchant_id;
	}

	public function set_merchant_id( string $merchant_id ): self {
		$this->merchant_id = $merchant_id;

		return $this;
	}

	public function get_payment_status(): string {
		return $this->payment_status;
	}

	public function set_payment_status( string $payment_status ): self {
		$this->payment_status = $payment_status;

		return $this;
	}

	public function get_order_status(): string {
		return $this->order_status;
	}

	public function set_order_status( string $order_status ): self {
		$this->order_status = $order_status;

		return $this;
	}

	public function get_order_merchant_status_description(): string {
		return $this->order_merchant_status_description;
	}

	public function set_order_merchant_status_description( string $order_merchant_status_description ): self {
		$this->order_merchant_status_description = $order_merchant_status_description;

		return $this;
	}

	public function get_order_base_price(): Price {
		return $this->order_base_price;
	}

	public function set_order_base_price( Price $order_base_price ): self {
		$this->order_base_price = $order_base_price;

		return $this;
	}

	public function get_order_final_price(): Price {
		return $this->order_final_price;
	}

	public function set_order_final_price( Price $order_final_price ): self {
		$this->order_final_price = $order_final_price;

		return $this;
	}

	public function get_delivery_references_list(): array {
		return $this->delivery_references_list;
	}

	public function set_delivery_references_list( array $delivery_references_list ): self {
		$this->delivery_references_list = $delivery_references_list;

		return $this;
	}

	public function get_currency(): string {
		return $this->currency;
	}

	public function set_currency( string $currency ): self {
		$this->currency = $currency;

		return $this;
	}

	public function get_payment_type(): string {
		return $this->payment_type;
	}

	public function set_payment_type( string $payment_type ): self {
		$this->payment_type = $payment_type;

		return $this;
	}

	public function get_order_discount(): string {
		return $this->order_discount;
	}

	public function set_order_discount( string $order_discount ): self {
		$this->order_discount = $order_discount;

		return $this;
	}

	public function get_order_additional_parameters(): array {
		return $this->order_additional_parameters;
	}

	public function set_order_additional_parameters( array $order_additional_parameters ): self {
		$this->order_additional_parameters = $order_additional_parameters;

		return $this;
	}
}
