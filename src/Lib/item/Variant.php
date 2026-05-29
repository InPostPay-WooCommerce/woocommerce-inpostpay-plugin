<?php
/**
 * Variant item.
 *
 * @package Ilabs\Inpost_Pay\Lib\item
 */

declare( strict_types=1 );

namespace Ilabs\Inpost_Pay\Lib\item;

use Ilabs\Inpost_Pay\Lib\helpers\JsonSerializationHelper;
use Ilabs\Inpost_Pay\Lib\Item;
use JsonSerializable;

/**
 * Represents a product variant.
 */
class Variant extends Item implements JsonSerializable {
	use JsonSerializationHelper;

	protected int $variant_id;
	protected string $variant_name;
	protected string $variant_description;
	protected string $variant_type;
	protected string $variant_values;

	/**
	 * Serializes object to JSON-compatible array.
	 *
	 * @return array
	 */
	public function jsonSerialize(): array {
		return $this->auto_serialize();
	}

	/**
	 * Returns variant ID.
	 *
	 * @return int
	 */
	public function get_variant_id(): int {
		return $this->variant_id;
	}

	/**
	 * Sets variant ID.
	 *
	 * @param int $variant_id Variant ID.
	 *
	 * @return self
	 */
	public function set_variant_id( int $variant_id ): self {
		$this->variant_id = $variant_id;

		return $this;
	}

	/**
	 * Returns variant name.
	 *
	 * @return string
	 */
	public function get_variant_name(): string {
		return $this->variant_name;
	}

	/**
	 * Sets variant name.
	 *
	 * @param string $variant_name Variant name.
	 *
	 * @return self
	 */
	public function set_variant_name( string $variant_name ): self {
		$this->variant_name = $variant_name;

		return $this;
	}

	/**
	 * Returns variant description.
	 *
	 * @return string
	 */
	public function get_variant_description(): string {
		return $this->variant_description;
	}

	/**
	 * Sets variant description.
	 *
	 * @param string $variant_description Variant description.
	 *
	 * @return self
	 */
	public function set_variant_description( string $variant_description ): self {
		$this->variant_description = $variant_description;

		return $this;
	}

	/**
	 * Returns variant type.
	 *
	 * @return string
	 */
	public function get_variant_type(): string {
		return $this->variant_type;
	}

	/**
	 * Sets variant type.
	 *
	 * @param string $variant_type Variant type.
	 *
	 * @return self
	 */
	public function set_variant_type( string $variant_type ): self {
		$this->variant_type = $variant_type;

		return $this;
	}

	/**
	 * Returns variant values.
	 *
	 * @return string
	 */
	public function get_variant_values(): string {
		return $this->variant_values;
	}

	/**
	 * Sets variant values.
	 *
	 * @param string $variant_values Variant values.
	 *
	 * @return self
	 */
	public function set_variant_values( string $variant_values ): self {
		$this->variant_values = $variant_values;

		return $this;
	}
}
