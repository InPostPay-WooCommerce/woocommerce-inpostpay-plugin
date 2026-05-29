<?php
/**
 * Summary item.
 *
 * @package Ilabs\Inpost_Pay\Lib\item
 */

declare( strict_types=1 );

namespace Ilabs\Inpost_Pay\Lib\item;

use Ilabs\Inpost_Pay\Lib\helpers\JsonSerializationHelper;
use Ilabs\Inpost_Pay\Lib\Item;
use JsonSerializable;

/**
 * Represents basket summary with pricing and metadata.
 */
class Summary extends Item implements JsonSerializable {
	use JsonSerializationHelper;

	protected Price $basket_base_price;
	protected Price $basket_final_price;
	protected Price $basket_promo_price;
	protected string $currency;
	protected string $basket_expiration_date;
	protected string $basket_additional_information;
	protected array $payment_type;
	protected array $basket_notice;

	/**
	 * Serializes object to JSON-compatible array.
	 *
	 * @return array
	 */
	public function jsonSerialize(): array {
		return $this->auto_serialize();
	}

	/**
	 * Returns basket base price.
	 *
	 * @return Price
	 */
	public function get_basket_base_price(): Price {
		return $this->basket_base_price;
	}

	/**
	 * Sets basket base price.
	 *
	 * @param Price $basket_base_price Basket base price.
	 *
	 * @return self
	 */
	public function set_basket_base_price( Price $basket_base_price ): self {
		$this->basket_base_price = $basket_base_price;

		return $this;
	}

	/**
	 * Returns basket final price.
	 *
	 * @return Price
	 */
	public function get_basket_final_price(): Price {
		return $this->basket_final_price;
	}

	/**
	 * Sets basket final price.
	 *
	 * @param Price $basket_final_price Basket final price.
	 *
	 * @return self
	 */
	public function set_basket_final_price( Price $basket_final_price ): self {
		$this->basket_final_price = $basket_final_price;

		return $this;
	}

	/**
	 * Returns basket promo price.
	 *
	 * @return Price
	 */
	public function get_basket_promo_price(): Price {
		return $this->basket_promo_price;
	}

	/**
	 * Sets basket promo price.
	 *
	 * @param Price $basket_promo_price Basket promo price.
	 *
	 * @return self
	 */
	public function set_basket_promo_price( Price $basket_promo_price ): self {
		$this->basket_promo_price = $basket_promo_price;

		return $this;
	}

	/**
	 * Returns currency code.
	 *
	 * @return string
	 */
	public function get_currency(): string {
		return $this->currency;
	}

	/**
	 * Sets currency code.
	 *
	 * @param string $currency Currency code.
	 *
	 * @return self
	 */
	public function set_currency( string $currency ): self {
		$this->currency = $currency;

		return $this;
	}

	/**
	 * Returns basket expiration date.
	 *
	 * @return string
	 */
	public function get_basket_expiration_date(): string {
		return $this->basket_expiration_date;
	}

	/**
	 * Sets basket expiration date.
	 *
	 * @param string $basket_expiration_date Basket expiration date.
	 *
	 * @return self
	 */
	public function set_basket_expiration_date( string $basket_expiration_date ): self {
		$this->basket_expiration_date = $basket_expiration_date;

		return $this;
	}

	/**
	 * Returns basket additional information.
	 *
	 * @return string
	 */
	public function get_basket_additional_information(): string {
		return $this->basket_additional_information;
	}

	/**
	 * Sets basket additional information.
	 *
	 * @param string $basket_additional_information Basket additional information.
	 *
	 * @return self
	 */
	public function set_basket_additional_information( string $basket_additional_information ): self {
		$this->basket_additional_information = $basket_additional_information;

		return $this;
	}

	/**
	 * Returns available payment types.
	 *
	 * @return array
	 */
	public function get_payment_type(): array {
		return $this->payment_type;
	}

	/**
	 * Sets available payment types.
	 *
	 * @param array $payment_type Payment types.
	 *
	 * @return self
	 */
	public function set_payment_type( array $payment_type ): self {
		$this->payment_type = $payment_type;

		return $this;
	}

	/**
	 * Returns basket notice data.
	 *
	 * @return array
	 */
	public function get_basket_notice(): array {
		return $this->basket_notice;
	}

	/**
	 * Sets basket notice data.
	 *
	 * @param array $basket_notice Basket notice data.
	 *
	 * @return self
	 */
	public function set_basket_notice( array $basket_notice ): self {
		$this->basket_notice = $basket_notice;

		return $this;
	}
}
