<?php
/**
 * Basket item.
 *
 * @package Ilabs\Inpost_Pay\Lib\item
 */

declare( strict_types=1 );

namespace Ilabs\Inpost_Pay\Lib\item;

use Ilabs\Inpost_Pay\Lib\helpers\JsonSerializationHelper;
use Ilabs\Inpost_Pay\Lib\Item;
use JsonSerializable;

/**
 * Represents the full basket payload sent to InPost Pay API.
 */
class Basket extends Item implements JsonSerializable {

	use JsonSerializationHelper;

	protected string $basket_id;
	protected Summary $summary;
	protected array $delivery;
	protected array $promo_codes;
	protected array $products;
	protected array $related_products;
	protected array $consents;
	protected array $promotions_available;

	/**
	 * Serializes object to JSON-compatible array.
	 *
	 * @return array
	 */
	public function jsonSerialize(): array {
		return $this->auto_serialize();
	}

	/**
	 * Returns basket ID.
	 *
	 * @return string
	 */
	public function get_basket_id(): string {
		return $this->basket_id;
	}

	/**
	 * Sets basket ID.
	 *
	 * @param string $basket_id Basket ID.
	 *
	 * @return self
	 */
	public function set_basket_id( string $basket_id ): self {
		$this->basket_id = $basket_id;

		return $this;
	}

	/**
	 * Returns basket summary.
	 *
	 * @return Summary
	 */
	public function get_summary(): Summary {
		return $this->summary;
	}

	/**
	 * Sets basket summary.
	 *
	 * @param Summary $summary Basket summary.
	 *
	 * @return self
	 */
	public function set_summary( Summary $summary ): self {
		$this->summary = $summary;

		return $this;
	}

	/**
	 * Returns delivery data.
	 *
	 * @return array
	 */
	public function get_delivery(): array {
		return $this->delivery;
	}

	/**
	 * Sets delivery data.
	 *
	 * @param array $delivery Delivery data.
	 *
	 * @return self
	 */
	public function set_delivery( array $delivery ): self {
		$this->delivery = $delivery;

		return $this;
	}

	/**
	 * Returns promo codes.
	 *
	 * @return array
	 */
	public function get_promo_codes(): array {
		return $this->promo_codes;
	}

	/**
	 * Sets promo codes.
	 *
	 * @param array $promo_codes Promo codes.
	 *
	 * @return self
	 */
	public function set_promo_codes( array $promo_codes ): self {
		$this->promo_codes = $promo_codes;

		return $this;
	}

	/**
	 * Returns products.
	 *
	 * @return array
	 */
	public function get_products(): array {
		return $this->products;
	}

	/**
	 * Sets products.
	 *
	 * @param array $products Products.
	 *
	 * @return self
	 */
	public function set_products( array $products ): self {
		$this->products = $products;

		return $this;
	}

	/**
	 * Returns related products.
	 *
	 * @return array
	 */
	public function get_related_products(): array {
		return $this->related_products;
	}

	/**
	 * Sets related products.
	 *
	 * @param array $related_products Related products.
	 *
	 * @return self
	 */
	public function set_related_products( array $related_products ): self {
		$this->related_products = $related_products;

		return $this;
	}

	/**
	 * Returns consents.
	 *
	 * @return array
	 */
	public function get_consents(): array {
		return $this->consents;
	}

	/**
	 * Sets consents.
	 *
	 * @param array $consents Consents.
	 *
	 * @return self
	 */
	public function set_consents( array $consents ): self {
		$this->consents = $consents;

		return $this;
	}

	/**
	 * Returns available promotions.
	 *
	 * @return array
	 */
	public function get_promotions_available(): array {
		return $this->promotions_available ?? array();
	}

	/**
	 * Sets available promotions.
	 *
	 * @param array $promotions_available Available promotions.
	 *
	 * @return self
	 */
	public function set_promotions_available( array $promotions_available ): self {
		$this->promotions_available = $promotions_available;

		return $this;
	}
}
