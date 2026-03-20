<?php

declare( strict_types=1 );

namespace Ilabs\Inpost_Pay\Lib\item;

use Ilabs\Inpost_Pay\Lib\Item;
use Ilabs\Inpost_Pay\Lib\helpers\JsonSerializationHelper;
use JsonSerializable;

class HotProduct extends Item implements JsonSerializable {
	use JsonSerializationHelper;

	protected string $product_id;
	protected string $ean;
	protected string $product_name;
	protected string $product_description;
	protected string $product_image;
	protected string $product_link;
	protected array $additional_product_images;
	protected Price $price;
	protected string $currency;
	protected Quantity $quantity;
	protected array $product_attributes;
	protected HotProductAvailability $product_availability;

	public function jsonSerialize(): array {
		return $this->autoSerialize();
	}

	public function get_product_id(): string {
		return $this->product_id;
	}

	public function set_product_id( string $product_id ): self {
		$this->product_id = $product_id;

		return $this;
	}

	public function get_ean(): string {
		return $this->ean;
	}

	public function set_ean( string $ean ): self {
		$this->ean = $ean;

		return $this;
	}

	public function get_product_name(): string {
		return $this->product_name;
	}

	public function set_product_name( string $product_name ): self {
		$this->product_name = $product_name;

		return $this;
	}

	public function get_product_description(): string {
		return $this->product_description;
	}

	public function set_product_description( string $product_description ): self {
		$this->product_description = $product_description;

		return $this;
	}

	public function get_product_image(): string {
		return $this->product_image;
	}

	public function set_product_image( string $product_image ): self {
		$this->product_image = $product_image;

		return $this;
	}

	public function get_product_link(): string {
		return $this->product_link;
	}

	public function set_product_link( string $product_link ): self {
		$this->product_link = $product_link;

		return $this;
	}

	public function get_additional_product_images(): array {
		return $this->additional_product_images;
	}

	public function set_additional_product_images( array $additional_product_images ): self {
		$this->additional_product_images = $additional_product_images;

		return $this;
	}

	public function get_price(): Price {
		return $this->price;
	}

	public function set_price( Price $price ): self {
		$this->price = $price;

		return $this;
	}

	public function get_currency(): string {
		return $this->currency;
	}

	public function set_currency( string $currency ): self {
		$this->currency = $currency;

		return $this;
	}

	public function get_quantity(): Quantity {
		return $this->quantity;
	}

	public function set_quantity( Quantity $quantity ): self {
		$this->quantity = $quantity;

		return $this;
	}

	public function get_product_attributes(): array {
		return $this->product_attributes;
	}

	public function set_product_attributes( array $product_attributes ): self {
		$this->product_attributes = $product_attributes;

		return $this;
	}

	public function get_product_availability(): HotProductAvailability {
		return $this->product_availability;
	}

	public function set_product_availability( HotProductAvailability $product_availability ): self {
		$this->product_availability = $product_availability;

		return $this;
	}
}
