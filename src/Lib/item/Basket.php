<?php

declare( strict_types=1 );

namespace Ilabs\Inpost_Pay\Lib\item;

use Ilabs\Inpost_Pay\Lib\helpers\JsonSerializationHelper;
use Ilabs\Inpost_Pay\Lib\Item;
use JsonSerializable;

class Basket extends Item implements JsonSerializable {

	use JsonSerializationHelper;

	protected string $basket_id;
	protected Summary $summary;
	protected array $delivery;
	protected array $promo_codes;
	protected array $products;
	protected array $related_products;
	protected array $consents;
	// protected ?MerchantStore $merchant_store = null;
	protected array $promotions_available;

	public function jsonSerialize(): array {
		return $this->autoSerialize();
	}

	/**
	 * @return mixed
	 */
	public function get_basket_id(): string {
		return $this->basket_id;
	}

	/**
	 * @param mixed $basket_id
	 */
	public function set_basket_id( string $basket_id ): self {
		$this->basket_id = $basket_id;

		return $this;
	}

	public function get_summary(): Summary {
		return $this->summary;
	}

	public function set_summary( Summary $summary ): self {
		$this->summary = $summary;

		return $this;
	}

	public function get_delivery(): array {
		return $this->delivery;
	}

	public function set_delivery( array $delivery ): self {
		$this->delivery = $delivery;

		return $this;
	}

	public function get_promo_codes(): array {
		return $this->promo_codes;
	}

	public function set_promo_codes( array $promo_codes ): self {
		$this->promo_codes = $promo_codes;

		return $this;
	}

	public function get_products(): array {
		return $this->products;
	}

	public function set_products( array $products ): self {
		$this->products = $products;

		return $this;
	}

	public function get_related_products(): array {
		return $this->related_products;
	}

	public function set_related_products( array $related_products ): self {
		$this->related_products = $related_products;

		return $this;
	}

	public function get_consents(): array {
		return $this->consents;
	}

	public function set_consents( array $consents ): self {
		$this->consents = $consents;

		return $this;
	}

	public function get_promotions_available(): array {
		return $this->promotions_available ?? array();
	}

	public function set_promotions_available( array $promotions_available ): self {
		$this->promotions_available = $promotions_available;

		return $this;
	}
}
