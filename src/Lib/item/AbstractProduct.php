<?php
/**
 * Abstract product item.
 *
 * @package Ilabs\Inpost_Pay\Lib\item
 */

declare( strict_types=1 );

namespace Ilabs\Inpost_Pay\Lib\item;

use Ilabs\Inpost_Pay\Lib\Item;
use Ilabs\Inpost_Pay\Lib\helpers\JsonSerializationHelper;
use JsonSerializable;

/**
 * Base class for product items with common product fields.
 */
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

	/**
	 * Serializes object to JSON-compatible array.
	 *
	 * @return array
	 */
	public function jsonSerialize(): array {
		return $this->auto_serialize();
	}

	/**
	 * Returns product ID.
	 *
	 * @return string
	 */
	public function get_product_id(): string {
		return $this->product_id;
	}

	/**
	 * Sets product ID.
	 *
	 * @param string $product_id Product ID.
	 *
	 * @return void
	 */
	public function set_product_id( string $product_id ): void {
		$this->product_id = $product_id;
	}

	/**
	 * Returns product category ID.
	 *
	 * @return int|null
	 */
	public function get_product_category(): ?int {
		return $this->product_category;
	}

	/**
	 * Sets product category ID.
	 *
	 * @param int|null $product_category Product category ID.
	 *
	 * @return void
	 */
	public function set_product_category( ?int $product_category ): void {
		$this->product_category = $product_category;
	}

	/**
	 * Returns EAN code.
	 *
	 * @return string
	 */
	public function get_ean(): string {
		return $this->ean;
	}

	/**
	 * Sets EAN code.
	 *
	 * @param string $ean EAN code.
	 *
	 * @return void
	 */
	public function set_ean( string $ean ): void {
		$this->ean = $ean;
	}

	/**
	 * Returns product name.
	 *
	 * @return string
	 */
	public function get_product_name(): string {
		return $this->product_name;
	}

	/**
	 * Sets product name.
	 *
	 * @param string $product_name Product name.
	 *
	 * @return void
	 */
	public function set_product_name( string $product_name ): void {
		$this->product_name = $product_name;
	}

	/**
	 * Returns product description.
	 *
	 * @return string
	 */
	public function get_product_description(): string {
		return $this->product_description;
	}

	/**
	 * Sets product description.
	 *
	 * @param string $product_description Product description.
	 *
	 * @return void
	 */
	public function set_product_description( string $product_description ): void {
		$this->product_description = $product_description;
	}

	/**
	 * Returns product link URL.
	 *
	 * @return string
	 */
	public function get_product_link(): string {
		return $this->product_link;
	}

	/**
	 * Sets product link URL.
	 *
	 * @param string $product_link Product link URL.
	 *
	 * @return void
	 */
	public function set_product_link( string $product_link ): void {
		$this->product_link = $product_link;
	}

	/**
	 * Returns product image URL.
	 *
	 * @return string
	 */
	public function get_product_image(): string {
		return $this->product_image;
	}

	/**
	 * Sets product image URL.
	 *
	 * @param string $product_image Product image URL.
	 *
	 * @return void
	 */
	public function set_product_image( string $product_image ): void {
		$this->product_image = $product_image;
	}

	/**
	 * Returns additional product images.
	 *
	 * @return array
	 */
	public function get_additional_product_images(): array {
		return $this->additional_product_images;
	}

	/**
	 * Sets additional product images.
	 *
	 * @param array $additional_product_images Additional product images.
	 *
	 * @return void
	 */
	public function set_additional_product_images( array $additional_product_images ): void {
		$this->additional_product_images = $additional_product_images;
	}

	/**
	 * Returns base price.
	 *
	 * @return Price
	 */
	public function get_base_price(): Price {
		return $this->base_price;
	}

	/**
	 * Sets base price.
	 *
	 * @param Price $base_price Base price.
	 *
	 * @return void
	 */
	public function set_base_price( Price $base_price ): void {
		$this->base_price = $base_price;
	}

	/**
	 * Returns promo price.
	 *
	 * @return Price
	 */
	public function get_promo_price(): Price {
		return $this->promo_price;
	}

	/**
	 * Sets promo price.
	 *
	 * @param Price $promo_price Promo price.
	 *
	 * @return void
	 */
	public function set_promo_price( Price $promo_price ): void {
		$this->promo_price = $promo_price;
	}

	/**
	 * Returns product quantity.
	 *
	 * @return Quantity
	 */
	public function get_quantity(): Quantity {
		return $this->quantity;
	}

	/**
	 * Sets product quantity.
	 *
	 * @param Quantity $quantity Product quantity.
	 *
	 * @return self
	 */
	public function set_quantity( Quantity $quantity ): self {
		$this->quantity = $quantity;

		return $this;
	}

	/**
	 * Returns product attributes.
	 *
	 * @return array
	 */
	public function get_product_attributes(): array {
		return $this->product_attributes;
	}

	/**
	 * Sets product attributes.
	 *
	 * @param array $product_attributes Product attributes.
	 *
	 * @return void
	 */
	public function set_product_attributes( array $product_attributes ): void {
		$this->product_attributes = $product_attributes;
	}

	/**
	 * Returns product variants.
	 *
	 * @return array
	 */
	public function get_variants(): array {
		return $this->variants;
	}

	/**
	 * Sets product variants.
	 *
	 * @param array $variants Product variants.
	 *
	 * @return void
	 */
	public function set_variants( array $variants ): void {
		$this->variants = $variants;
	}

	/**
	 * Returns lowest price.
	 *
	 * @return Price|null
	 */
	public function get_lowest_price(): ?Price {
		return $this->lowest_price;
	}

	/**
	 * Sets lowest price.
	 *
	 * @param Price|null $lowest_price Lowest price.
	 *
	 * @return void
	 */
	public function set_lowest_price( ?Price $lowest_price ): void {
		$this->lowest_price = $lowest_price;
	}

	/**
	 * Returns product type.
	 *
	 * @return string
	 */
	public function get_product_type(): string {
		return $this->product_type;
	}

	/**
	 * Sets product type.
	 *
	 * @param string $product_type Product type.
	 *
	 * @return self
	 */
	public function set_product_type( string $product_type ): self {
		$this->product_type = $product_type;

		return $this;
	}
}
