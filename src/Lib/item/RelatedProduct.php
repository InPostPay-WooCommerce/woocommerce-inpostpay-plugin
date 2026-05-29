<?php
/**
 * Related product item.
 *
 * @package Ilabs\Inpost_Pay\Lib\item
 */

declare( strict_types=1 );

namespace Ilabs\Inpost_Pay\Lib\item;

use Ilabs\Inpost_Pay\Lib\helpers\JsonSerializationHelper;
use Ilabs\Inpost_Pay\Lib\interfaces\ProductInterface;
use JsonSerializable;

/**
 * Represents a product related to the basket delivery.
 */
class RelatedProduct extends AbstractProduct implements ProductInterface, JsonSerializable {
	use JsonSerializationHelper;

	protected ?array $delivery_related_products;

	/**
	 * Serializes object to JSON-compatible array.
	 *
	 * @return array
	 */
	public function jsonSerialize(): array {
		return array_merge(
			parent::jsonSerialize(),
			$this->auto_serialize()
		);
	}

	/**
	 * Returns delivery related products.
	 *
	 * @return array|null
	 */
	public function get_delivery_related_products(): ?array {
		return $this->delivery_related_products;
	}

	/**
	 * Sets delivery related products.
	 *
	 * @param array|null $delivery_related_products Delivery related products.
	 *
	 * @return self
	 */
	public function set_delivery_related_products( ?array $delivery_related_products ): self {
		$this->delivery_related_products = $delivery_related_products;

		return $this;
	}
}
