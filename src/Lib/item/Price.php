<?php
/**
 * Price item.
 *
 * @package Ilabs\Inpost_Pay\Lib\item
 */

declare( strict_types=1 );

namespace Ilabs\Inpost_Pay\Lib\item;

use Ilabs\Inpost_Pay\Lib\helpers\JsonSerializationHelper;
use Ilabs\Inpost_Pay\Lib\Item;
use JsonSerializable;

/**
 * Price class for handling price calculations and storage.
 *
 * This class represents a price object with net, gross, and VAT values.
 * It provides methods for getting and setting price properties and implements
 * JsonSerializable for easy JSON conversion.
 *
 * @package Ilabs\Inpost_Pay\Lib\item
 */
class Price extends Item implements JsonSerializable {
	use JsonSerializationHelper;

	protected string $net;
	protected string $full_net;
	protected string $gross;
	protected string $vat;

	/**
	 * JSON serialize method.
	 * This method is used to serialize this object when it is needed to be converted to JSON.
	 * It returns an array containing all the properties of the object.
	 *
	 * @return array The array containing all the properties of the object.
	 */
	public function jsonSerialize(): array {
		return $this->auto_serialize();
	}

	/**
	 * Retrieves the net price of the item
	 *
	 * @return string The net price of the item
	 */
	public function get_net(): string {
		return $this->net;
	}

	/**
	 * Retrieves the full net price of the item without rounding or formatting
	 *
	 * This method returns the full net price of the item, which is the net price
	 * before any rounding has been applied.
	 *
	 * @return string The full net price of the item
	 */
	public function get_full_net(): string {
		return $this->full_net;
	}

	/**
	 * Sets the net price of the item
	 *
	 * @param string $net The net price of the item.
	 *
	 * @return self
	 */
	public function set_net( string $net ): self {
		$this->net = wc_format_decimal( $net, 2 );

		$this->set_full_net( $net );

		return $this;
	}

	/**
	 * Sets the full net price of the item, which is the net price
	 * before any rounding has been applied.
	 *
	 * @param string $net The full net price of the item.
	 *
	 * @return self
	 */
	public function set_full_net( string $net ): self {
		$this->full_net = $net;

		return $this;
	}

	/**
	 * Retrieves the gross price of the item
	 *
	 * @return string The gross price of the item.
	 */
	public function get_gross(): string {
		return $this->gross;
	}

	/**
	 * Sets the gross price of the item.
	 *
	 * @param string $gross The gross price of the item.
	 *
	 * @return self
	 */
	public function set_gross( string $gross ): self {
		$this->gross = $gross;

		return $this;
	}

	/**
	 * Retrieves the vat price of the item
	 *
	 * @return string The vat price of the item.
	 */
	public function get_vat(): string {
		return $this->vat;
	}

	/**
	 * Sets the vat price of the item.
	 *
	 * @param string $vat The vat price of the item.
	 *
	 * @return self
	 */
	public function set_vat( string $vat ): self {
		$this->vat = $vat;

		return $this;
	}
}
