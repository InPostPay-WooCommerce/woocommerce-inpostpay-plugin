<?php

declare( strict_types=1 );

namespace Ilabs\Inpost_Pay\Lib\item;

use Ilabs\Inpost_Pay\Lib\Item;
use Ilabs\Inpost_Pay\Lib\helpers\JsonSerializationHelper;
use JsonSerializable;

abstract class AbstractProduct extends Item implements JsonSerializable {
	use JsonSerializationHelper;

	public const TYPE_PRODUCT = 'PRODUCT';
	public const TYPE_DIGITAL = 'DIGITAL';

	protected string $product_id;
	protected ?int $product_category;
	protected string $ean;
	protected string $product_name;
	protected string $product_description;
	protected string $product_link;
	protected string $product_image;
	protected array $additional_product_images;
	protected Price $base_price;
	protected Price $promo_price;
	protected Quantity $quantity;
	protected array $product_attributes;
	protected array $variants;
	protected ?Price $lowest_price = null;
	protected string $product_type = self::TYPE_PRODUCT;

	public function jsonSerialize(): array {
		return $this->autoSerialize();
	}

	public function get_product_id(): string {
		return $this->product_id;
	}

	public function set_product_id( string $product_id ): void {
		$this->product_id = $product_id;
	}

	public function get_product_category(): ?int {
		return $this->product_category;
	}

	public function set_product_category( ?int $product_category ): void {
		$this->product_category = $product_category;
	}

	public function get_ean(): string {
		return $this->ean;
	}

	public function set_ean( string $ean ): void {
		$this->ean = $ean;
	}

	public function get_product_name(): string {
		return $this->product_name;
	}

	public function set_product_name( string $product_name ): void {
		$this->product_name = $product_name;
	}

	public function get_product_description(): string {
		return $this->product_description;
	}

	public function set_product_description( string $product_description ): void {
		$this->product_description = $product_description;
	}

	public function get_product_link(): string {
		return $this->product_link;
	}

	public function set_product_link( string $product_link ): void {
		$this->product_link = $product_link;
	}

	public function get_product_image(): string {
		return $this->product_image;
	}

	public function set_product_image( string $product_image ): void {
		$this->product_image = $product_image;
	}

	public function get_additional_product_images(): array {
		return $this->additional_product_images;
	}

	public function set_additional_product_images( array $additional_product_images ): void {
		$this->additional_product_images = $additional_product_images;
	}

	public function get_base_price(): Price {
		return $this->base_price;
	}

	public function set_base_price( Price $base_price ): void {
		$this->base_price = $base_price;
	}

	public function get_promo_price(): Price {
		return $this->promo_price;
	}

	public function set_promo_price( Price $promo_price ): void {
		$this->promo_price = $promo_price;
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

	public function set_product_attributes( array $product_attributes ): void {
		$this->product_attributes = $product_attributes;
	}

	public function get_variants(): array {
		return $this->variants;
	}

	public function set_variants( array $variants ): void {
		$this->variants = $variants;
	}

	public function get_lowest_price(): ?Price {
		return $this->lowest_price;
	}

	public function set_lowest_price( ?Price $lowest_price ): void {
		$this->lowest_price = $lowest_price;
	}

	public function get_product_type(): string {
		return $this->product_type;
	}

	public function set_product_type( string $product_type ): self {
		$this->product_type = $product_type;

		return $this;
	}
}
