<?php
/**
 * Hot product item.
 *
 * @package Ilabs\Inpost_Pay\Lib\item
 */

declare( strict_types=1 );

namespace Ilabs\Inpost_Pay\Lib\item;

use Ilabs\Inpost_Pay\Lib\Item;
use Ilabs\Inpost_Pay\Lib\helpers\JsonSerializationHelper;
use JsonSerializable;

/**
 * Represents a hot product with full product details and availability.
 */
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
	 * @return self
	 */
	public function set_product_id( string $product_id ): self {
		$this->product_id = $product_id;

		return $this;
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
	 * @return self
	 */
	public function set_ean( string $ean ): self {
		$this->ean = $ean;

		return $this;
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
	 * @return self
	 */
	public function set_product_name( string $product_name ): self {
		$this->product_name = $product_name;

		return $this;
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
	 * @return self
	 */
	public function set_product_description( string $product_description ): self {
		$this->product_description = $product_description;

		return $this;
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
	 * @return self
	 */
	public function set_product_image( string $product_image ): self {
		$this->product_image = $product_image;

		return $this;
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
	 * @return self
	 */
	public function set_product_link( string $product_link ): self {
		$this->product_link = $product_link;

		return $this;
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
	 * @return self
	 */
	public function set_additional_product_images( array $additional_product_images ): self {
		$this->additional_product_images = $additional_product_images;

		return $this;
	}

	/**
	 * Returns product price.
	 *
	 * @return Price
	 */
	public function get_price(): Price {
		return $this->price;
	}

	/**
	 * Sets product price.
	 *
	 * @param Price $price Product price.
	 *
	 * @return self
	 */
	public function set_price( Price $price ): self {
		$this->price = $price;

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
	 * @return self
	 */
	public function set_product_attributes( array $product_attributes ): self {
		$this->product_attributes = $product_attributes;

		return $this;
	}

	/**
	 * Returns product availability.
	 *
	 * @return HotProductAvailability
	 */
	public function get_product_availability(): HotProductAvailability {
		return $this->product_availability;
	}

	/**
	 * Sets product availability.
	 *
	 * @param HotProductAvailability $product_availability Product availability.
	 *
	 * @return self
	 */
	public function set_product_availability( HotProductAvailability $product_availability ): self {
		$this->product_availability = $product_availability;

		return $this;
	}
}
