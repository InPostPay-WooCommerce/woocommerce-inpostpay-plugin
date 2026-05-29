<?php
/**
 * Promo code item.
 *
 * @package Ilabs\Inpost_Pay\Lib\item
 */

declare( strict_types=1 );

namespace Ilabs\Inpost_Pay\Lib\item;

use Ilabs\Inpost_Pay\Lib\helpers\JsonSerializationHelper;
use Ilabs\Inpost_Pay\Lib\Item;
use JsonSerializable;

/**
 * Represents a promo code applied to the basket.
 */
class PromoCode extends Item implements JsonSerializable {
	use JsonSerializationHelper;

	protected string $name;
	protected string $promo_code_value;
	protected string $regulation_type;

	/**
	 * Serializes object to JSON-compatible array.
	 *
	 * @return array
	 */
	public function jsonSerialize(): array {
		return $this->auto_serialize();
	}

	/**
	 * Returns promo code name.
	 *
	 * @return string
	 */
	public function get_name(): string {
		return $this->name;
	}

	/**
	 * Sets promo code name.
	 *
	 * @param string $name Promo code name.
	 *
	 * @return self
	 */
	public function set_name( string $name ): self {
		$this->name = $name;

		return $this;
	}

	/**
	 * Returns promo code value.
	 *
	 * @return string
	 */
	public function get_promo_code_value(): string {
		return $this->promo_code_value;
	}

	/**
	 * Sets promo code value.
	 *
	 * @param string $promo_code_value Promo code value.
	 *
	 * @return self
	 */
	public function set_promo_code_value( string $promo_code_value ): self {
		$this->promo_code_value = $promo_code_value;

		return $this;
	}

	/**
	 * Returns regulation type.
	 *
	 * @return string
	 */
	public function get_regulation_type(): string {
		return $this->regulation_type;
	}

	/**
	 * Sets regulation type.
	 *
	 * @param string $regulation_type Regulation type.
	 *
	 * @return self
	 */
	public function set_regulation_type( string $regulation_type ): self {
		$this->regulation_type = $regulation_type;

		return $this;
	}
}
