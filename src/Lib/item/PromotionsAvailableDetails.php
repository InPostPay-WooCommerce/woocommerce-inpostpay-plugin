<?php

declare( strict_types=1 );

namespace Ilabs\Inpost_Pay\Lib\item;

use Ilabs\Inpost_Pay\Lib\helpers\JsonSerializationHelper;
use Ilabs\Inpost_Pay\Lib\Item;
use JsonSerializable;

class PromotionsAvailableDetails extends Item implements JsonSerializable {
	use JsonSerializationHelper;

	protected string $link;

	public function jsonSerialize(): array {
		return $this->autoSerialize();
	}

	public function get_link(): string {
		return $this->link;
	}

	public function set_link( string $link ): self {
		$this->link = $link;

		return $this;
	}
}
