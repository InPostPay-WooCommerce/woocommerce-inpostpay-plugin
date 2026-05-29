<?php
/**
 * Order details item.
 *
 * @package Inpost_Pay
 */

declare( strict_types=1 );

namespace Ilabs\Inpost_Pay\Lib\item\order;

use Ilabs\Inpost_Pay\Lib\helpers\JsonSerializationHelper;
use Ilabs\Inpost_Pay\Lib\Item;
use Ilabs\Inpost_Pay\Lib\item\Price;

/**
 * Represents order details.
 */
class OrderDetails extends Item implements \JsonSerializable {
	use JsonSerializationHelper;

	/**
	 * Order comments.
	 *
	 * @var string
	 */
	protected string $order_comments;

	/**
	 * Basket ID.
	 *
	 * @var string|null
	 */
	protected ?string $basket_id = null;

	/**
	 * Order ID.
	 *
	 * @var string
	 */
	protected string $order_id;

	/**
	 * Customer order ID.
	 *
	 * @var string
	 */
	protected string $customer_order_id;

	/**
	 * POS ID.
	 *
	 * @var string
	 */
	protected string $pos_id;

	/**
	 * Order creation date.
	 *
	 * @var string
	 */
	protected string $order_creation_date;

	/**
	 * Order update date.
	 *
	 * @var string
	 */
	protected string $order_update_date;

	/**
	 * Merchant ID.
	 *
	 * @var string
	 */
	protected string $merchant_id;

	/**
	 * Payment status.
	 *
	 * @var string
	 */
	protected string $payment_status;

	/**
	 * Order status.
	 *
	 * @var string
	 */
	protected string $order_status;

	/**
	 * Order merchant status description.
	 *
	 * @var string
	 */
	protected string $order_merchant_status_description;

	/**
	 * Order base price.
	 *
	 * @var Price
	 */
	protected Price $order_base_price;

	/**
	 * Order final price.
	 *
	 * @var Price
	 */
	protected Price $order_final_price;

	/**
	 * Delivery references list.
	 *
	 * @var array
	 */
	protected array $delivery_references_list;

	/**
	 * Currency.
	 *
	 * @var string
	 */
	protected string $currency;

	/**
	 * Payment type.
	 *
	 * @var string
	 */
	protected string $payment_type;

	/**
	 * Order discount.
	 *
	 * @var string
	 */
	protected string $order_discount;

	/**
	 * Order additional parameters.
	 *
	 * @var array
	 */
	protected array $order_additional_parameters;

	/**
	 * Serialize item to array.
	 *
	 * @return array
	 */
	public function jsonSerialize(): array {
		return $this->auto_serialize();
	}

	/**
	 * Get order comments.
	 *
	 * @return string
	 */
	public function get_order_comments(): string {
		return $this->order_comments;
	}

	/**
	 * Set order comments.
	 *
	 * @param string $order_comments Order comments.
	 *
	 * @return self
	 */
	public function set_order_comments( string $order_comments ): self {
		$this->order_comments = $order_comments;

		return $this;
	}

	/**
	 * Get basket ID.
	 *
	 * @return string|null
	 */
	public function get_basket_id(): ?string {
		return $this->basket_id;
	}

	/**
	 * Set basket ID.
	 *
	 * @param string|null $basket_id Basket ID.
	 *
	 * @return self
	 */
	public function set_basket_id( ?string $basket_id ): self {
		$this->basket_id = $basket_id;

		return $this;
	}

	/**
	 * Get order ID.
	 *
	 * @return string
	 */
	public function get_order_id(): string {
		return $this->order_id;
	}

	/**
	 * Set order ID.
	 *
	 * @param string $order_id Order ID.
	 *
	 * @return self
	 */
	public function set_order_id( string $order_id ): self {
		$this->order_id = $order_id;

		return $this;
	}

	/**
	 * Get customer order ID.
	 *
	 * @return string
	 */
	public function get_customer_order_id(): string {
		return $this->customer_order_id;
	}

	/**
	 * Set customer order ID.
	 *
	 * @param string $customer_order_id Customer order ID.
	 *
	 * @return self
	 */
	public function set_customer_order_id( string $customer_order_id ): self {
		$this->customer_order_id = $customer_order_id;

		return $this;
	}

	/**
	 * Get POS ID.
	 *
	 * @return string
	 */
	public function get_pos_id(): string {
		return $this->pos_id;
	}

	/**
	 * Set POS ID.
	 *
	 * @param string $pos_id POS ID.
	 *
	 * @return self
	 */
	public function set_pos_id( string $pos_id ): self {
		$this->pos_id = $pos_id;

		return $this;
	}

	/**
	 * Get order creation date.
	 *
	 * @return string
	 */
	public function get_order_creation_date(): string {
		return $this->order_creation_date;
	}

	/**
	 * Set order creation date.
	 *
	 * @param string $order_creation_date Order creation date.
	 *
	 * @return self
	 */
	public function set_order_creation_date( string $order_creation_date ): self {
		$this->order_creation_date = $order_creation_date;

		return $this;
	}

	/**
	 * Get order update date.
	 *
	 * @return string
	 */
	public function get_order_update_date(): string {
		return $this->order_update_date;
	}

	/**
	 * Set order update date.
	 *
	 * @param string $order_update_date Order update date.
	 *
	 * @return self
	 */
	public function set_order_update_date( string $order_update_date ): self {
		$this->order_update_date = $order_update_date;

		return $this;
	}

	/**
	 * Get merchant ID.
	 *
	 * @return string
	 */
	public function get_merchant_id(): string {
		return $this->merchant_id;
	}

	/**
	 * Set merchant ID.
	 *
	 * @param string $merchant_id Merchant ID.
	 *
	 * @return self
	 */
	public function set_merchant_id( string $merchant_id ): self {
		$this->merchant_id = $merchant_id;

		return $this;
	}

	/**
	 * Get payment status.
	 *
	 * @return string
	 */
	public function get_payment_status(): string {
		return $this->payment_status;
	}

	/**
	 * Set payment status.
	 *
	 * @param string $payment_status Payment status.
	 *
	 * @return self
	 */
	public function set_payment_status( string $payment_status ): self {
		$this->payment_status = $payment_status;

		return $this;
	}

	/**
	 * Get order status.
	 *
	 * @return string
	 */
	public function get_order_status(): string {
		return $this->order_status;
	}

	/**
	 * Set order status.
	 *
	 * @param string $order_status Order status.
	 *
	 * @return self
	 */
	public function set_order_status( string $order_status ): self {
		$this->order_status = $order_status;

		return $this;
	}

	/**
	 * Get order merchant status description.
	 *
	 * @return string
	 */
	public function get_order_merchant_status_description(): string {
		return $this->order_merchant_status_description;
	}

	/**
	 * Set order merchant status description.
	 *
	 * @param string $order_merchant_status_description Order merchant status description.
	 *
	 * @return self
	 */
	public function set_order_merchant_status_description( string $order_merchant_status_description ): self {
		$this->order_merchant_status_description = $order_merchant_status_description;

		return $this;
	}

	/**
	 * Get order base price.
	 *
	 * @return Price
	 */
	public function get_order_base_price(): Price {
		return $this->order_base_price;
	}

	/**
	 * Set order base price.
	 *
	 * @param Price $order_base_price Order base price.
	 *
	 * @return self
	 */
	public function set_order_base_price( Price $order_base_price ): self {
		$this->order_base_price = $order_base_price;

		return $this;
	}

	/**
	 * Get order final price.
	 *
	 * @return Price
	 */
	public function get_order_final_price(): Price {
		return $this->order_final_price;
	}

	/**
	 * Set order final price.
	 *
	 * @param Price $order_final_price Order final price.
	 *
	 * @return self
	 */
	public function set_order_final_price( Price $order_final_price ): self {
		$this->order_final_price = $order_final_price;

		return $this;
	}

	/**
	 * Get delivery references list.
	 *
	 * @return array
	 */
	public function get_delivery_references_list(): array {
		return $this->delivery_references_list;
	}

	/**
	 * Set delivery references list.
	 *
	 * @param array $delivery_references_list Delivery references list.
	 *
	 * @return self
	 */
	public function set_delivery_references_list( array $delivery_references_list ): self {
		$this->delivery_references_list = $delivery_references_list;

		return $this;
	}

	/**
	 * Get currency.
	 *
	 * @return string
	 */
	public function get_currency(): string {
		return $this->currency;
	}

	/**
	 * Set currency.
	 *
	 * @param string $currency Currency.
	 *
	 * @return self
	 */
	public function set_currency( string $currency ): self {
		$this->currency = $currency;

		return $this;
	}

	/**
	 * Get payment type.
	 *
	 * @return string
	 */
	public function get_payment_type(): string {
		return $this->payment_type;
	}

	/**
	 * Set payment type.
	 *
	 * @param string $payment_type Payment type.
	 *
	 * @return self
	 */
	public function set_payment_type( string $payment_type ): self {
		$this->payment_type = $payment_type;

		return $this;
	}

	/**
	 * Get order discount.
	 *
	 * @return string
	 */
	public function get_order_discount(): string {
		return $this->order_discount;
	}

	/**
	 * Set order discount.
	 *
	 * @param string $order_discount Order discount.
	 *
	 * @return self
	 */
	public function set_order_discount( string $order_discount ): self {
		$this->order_discount = $order_discount;

		return $this;
	}

	/**
	 * Get order additional parameters.
	 *
	 * @return array
	 */
	public function get_order_additional_parameters(): array {
		return $this->order_additional_parameters;
	}

	/**
	 * Set order additional parameters.
	 *
	 * @param array $order_additional_parameters Order additional parameters.
	 *
	 * @return self
	 */
	public function set_order_additional_parameters( array $order_additional_parameters ): self {
		$this->order_additional_parameters = $order_additional_parameters;

		return $this;
	}
}
