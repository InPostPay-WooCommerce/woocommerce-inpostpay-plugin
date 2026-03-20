<?php

declare( strict_types=1 );

namespace Ilabs\Inpost_Pay\Lib\item;

use Ilabs\Inpost_Pay\Lib\helpers\JsonSerializationHelper;
use Ilabs\Inpost_Pay\Lib\interfaces\ProductInterface;
use JsonSerializable;

class RelatedProduct extends AbstractProduct implements ProductInterface, JsonSerializable {
	use JsonSerializationHelper;

	protected ?array $delivery_related_products;

	public function jsonSerialize(): array {
		return array_merge(
			parent::jsonSerialize(),
			$this->autoSerialize()
		);
	}

	public function get_delivery_related_products(): ?array {
		return $this->delivery_related_products;
	}

	public function set_delivery_related_products( ?array $delivery_related_products ): self {
		$this->delivery_related_products = $delivery_related_products;

		return $this;
	}
}
