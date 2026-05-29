<?php
/**
 * Order additional parameter item.
 *
 * @package Inpost_Pay
 */

declare( strict_types=1 );

namespace Ilabs\Inpost_Pay\Lib\item\order;

use Ilabs\Inpost_Pay\Lib\helpers\JsonSerializationHelper;
use Ilabs\Inpost_Pay\Lib\Item;

/**
 * Represents order additional parameter.
 */
class OrderAdditionalParameter extends Item implements \JsonSerializable {
	use JsonSerializationHelper;

	/**
	 * Parameter key.
	 *
	 * @var string
	 */
	protected string $key;

	/**
	 * Parameter value.
	 *
	 * @var string
	 */
	protected string $value;

	/**
	 * Initialize order additional parameter.
	 *
	 * @param string $key   Parameter key.
	 * @param string $value Parameter value.
	 */
	public function __construct( string $key, string $value ) {
		$this->key   = $key;
		$this->value = $value;
	}

	/**
	 * Serialize item to array.
	 *
	 * @return array
	 */
	public function jsonSerialize(): array {
		return $this->auto_serialize();
	}

	/**
	 * Get parameter key.
	 *
	 * @return string
	 */
	public function get_key(): string {
		return $this->key;
	}

	/**
	 * Set parameter key.
	 *
	 * @param string $key Parameter key.
	 *
	 * @return self
	 */
	public function set_key( string $key ): self {
		$this->key = $key;

		return $this;
	}

	/**
	 * Get parameter value.
	 *
	 * @return string
	 */
	public function get_value(): string {
		return $this->value;
	}

	/**
	 * Set parameter value.
	 *
	 * @param string $value Parameter value.
	 *
	 * @return self
	 */
	public function set_value( string $value ): self {
		$this->value = $value;

		return $this;
	}
}
