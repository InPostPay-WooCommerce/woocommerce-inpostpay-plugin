<?php
/**
 * Phone item.
 *
 * @package Inpost_Pay
 */

declare( strict_types=1 );

namespace Ilabs\Inpost_Pay\Lib\item\order;

use Ilabs\Inpost_Pay\Lib\helpers\JsonSerializationHelper;
use Ilabs\Inpost_Pay\Lib\Item;

/**
 * Represents phone data.
 */
class Phone extends Item implements \JsonSerializable {
	use JsonSerializationHelper;

	/**
	 * Country prefix.
	 *
	 * @var string
	 */
	protected string $country_prefix;

	/**
	 * Phone number.
	 *
	 * @var string
	 */
	protected string $phone;

	/**
	 * Serialize item to array.
	 *
	 * @return array
	 */
	public function jsonSerialize(): array {
		return $this->auto_serialize();
	}

	/**
	 * Get country prefix.
	 *
	 * @return string
	 */
	public function get_country_prefix(): string {
		return $this->country_prefix;
	}

	/**
	 * Set country prefix.
	 *
	 * @param string $country_prefix Country prefix.
	 *
	 * @return self
	 */
	public function set_country_prefix( string $country_prefix ): self {
		$this->country_prefix = $country_prefix;

		return $this;
	}

	/**
	 * Get phone number.
	 *
	 * @return string
	 */
	public function get_phone(): string {
		return $this->phone;
	}

	/**
	 * Set phone number.
	 *
	 * @param string $phone Phone number.
	 *
	 * @return self
	 */
	public function set_phone( string $phone ): self {
		$this->phone = $phone;

		return $this;
	}
}
