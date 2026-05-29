<?php
/**
 * Promotions available details item.
 *
 * @package Ilabs\Inpost_Pay\Lib\item
 */

declare( strict_types=1 );

namespace Ilabs\Inpost_Pay\Lib\item;

use Ilabs\Inpost_Pay\Lib\helpers\JsonSerializationHelper;
use Ilabs\Inpost_Pay\Lib\Item;
use JsonSerializable;

/**
 * Represents detail data for a promotion.
 */
class PromotionsAvailableDetails extends Item implements JsonSerializable {
	use JsonSerializationHelper;

	protected string $link;

	/**
	 * Serializes object to JSON-compatible array.
	 *
	 * @return array
	 */
	public function jsonSerialize(): array {
		return $this->auto_serialize();
	}

	/**
	 * Returns promotion details link.
	 *
	 * @return string
	 */
	public function get_link(): string {
		return $this->link;
	}

	/**
	 * Sets promotion details link.
	 *
	 * @param string $link Promotion details link.
	 *
	 * @return self
	 */
	public function set_link( string $link ): self {
		$this->link = $link;

		return $this;
	}
}
