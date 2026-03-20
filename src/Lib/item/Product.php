<?php

declare( strict_types=1 );

namespace Ilabs\Inpost_Pay\Lib\item;

use Ilabs\Inpost_Pay\Lib\helpers\JsonSerializationHelper;
use Ilabs\Inpost_Pay\Lib\interfaces\ProductInterface;
use JsonSerializable;

class Product extends AbstractProduct implements ProductInterface, JsonSerializable
{
	use JsonSerializationHelper;

	protected ?array $delivery_product;

	public function jsonSerialize(): array {
		return array_merge(
			parent::jsonSerialize(),
			$this->autoSerialize()
		);
	}

	public function get_delivery_product(): ?array {
		return $this->delivery_product;
	}

	public function set_delivery_product( ?array $delivery_product ): self {
		$this->delivery_product = $delivery_product;

		return $this;
	}
}
