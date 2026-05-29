<?php
/**
 * Product item.
 *
 * @package Ilabs\Inpost_Pay\Lib\item
 */

declare( strict_types=1 );

namespace Ilabs\Inpost_Pay\Lib\item;

use Ilabs\Inpost_Pay\Lib\helpers\JsonSerializationHelper;
use Ilabs\Inpost_Pay\Lib\interfaces\ProductInterface;
use JsonSerializable;

/**
 * Represents a product with optional delivery product data.
 */
class Product extends AbstractProduct implements ProductInterface, JsonSerializable {

	use JsonSerializationHelper;

	protected ?array $delivery_product;

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
	 * Returns delivery product data.
	 *
	 * @return array|null
	 */
	public function get_delivery_product(): ?array {
		return $this->delivery_product;
	}

	/**
	 * Sets delivery product data.
	 *
	 * @param array|null $delivery_product Delivery product data.
	 *
	 * @return self
	 */
	public function set_delivery_product( ?array $delivery_product ): self {
		$this->delivery_product = $delivery_product;

		return $this;
	}
}
