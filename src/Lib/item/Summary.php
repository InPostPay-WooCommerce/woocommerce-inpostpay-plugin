<?php

declare( strict_types=1 );

namespace Ilabs\Inpost_Pay\Lib\item;

use Ilabs\Inpost_Pay\Lib\helpers\JsonSerializationHelper;
use Ilabs\Inpost_Pay\Lib\Item;
use JsonSerializable;

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

	public function jsonSerialize(): array {
		return $this->autoSerialize();
	}

	public function get_basket_base_price(): Price {
		return $this->basket_base_price;
	}

	public function set_basket_base_price( Price $basket_base_price ): self {
		$this->basket_base_price = $basket_base_price;

		return $this;
	}

	public function get_basket_final_price(): Price {
		return $this->basket_final_price;
	}

	public function set_basket_final_price( Price $basket_final_price ): self {
		$this->basket_final_price = $basket_final_price;

		return $this;
	}

	public function get_basket_promo_price(): Price {
		return $this->basket_promo_price;
	}

	public function set_basket_promo_price( Price $basket_promo_price ): self {
		$this->basket_promo_price = $basket_promo_price;

		return $this;
	}

	public function get_currency(): string {
		return $this->currency;
	}

	public function set_currency( string $currency ): self {
		$this->currency = $currency;

		return $this;
	}

	public function get_basket_expiration_date(): string {
		return $this->basket_expiration_date;
	}

	public function set_basket_expiration_date( string $basket_expiration_date ): self {
		$this->basket_expiration_date = $basket_expiration_date;

		return $this;
	}

	public function get_basket_additional_information(): string {
		return $this->basket_additional_information;
	}

	public function set_basket_additional_information( string $basket_additional_information ): self {
		$this->basket_additional_information = $basket_additional_information;

		return $this;
	}

	public function get_payment_type(): array {
		return $this->payment_type;
	}

	public function set_payment_type( array $payment_type ): self {
		$this->payment_type = $payment_type;

		return $this;
	}

	public function get_basket_notice(): array {
		return $this->basket_notice;
	}

	public function set_basket_notice( array $basket_notice ): self {
		$this->basket_notice = $basket_notice;

		return $this;
	}
}
