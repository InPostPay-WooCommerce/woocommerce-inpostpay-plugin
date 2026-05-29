<?php
/**
 * Delivery address item.
 *
 * @package Inpost_Pay
 */

declare( strict_types=1 );

namespace Ilabs\Inpost_Pay\Lib\item\order;

use Ilabs\Inpost_Pay\Lib\helpers\JsonSerializationHelper;
use Ilabs\Inpost_Pay\Lib\Item;

/**
 * Represents delivery address.
 */
class DeliveryAddress extends Item implements \JsonSerializable {
	use JsonSerializationHelper;

	/**
	 * Recipient name.
	 *
	 * @var string
	 */
	protected string $name;

	/**
	 * Country code.
	 *
	 * @var string
	 */
	protected string $country_code;

	/**
	 * Address.
	 *
	 * @var string
	 */
	protected string $address;

	/**
	 * City.
	 *
	 * @var string
	 */
	protected string $city;

	/**
	 * Postal code.
	 *
	 * @var string
	 */
	protected string $postal_code;

	/**
	 * Serialize item to array.
	 *
	 * @return array
	 */
	public function jsonSerialize(): array {
		return $this->auto_serialize();
	}

	/**
	 * Get recipient name.
	 *
	 * @return string
	 */
	public function get_name(): string {
		return $this->name;
	}

	/**
	 * Set recipient name.
	 *
	 * @param string $name Recipient name.
	 *
	 * @return self
	 */
	public function set_name( string $name ): self {
		$this->name = $name;

		return $this;
	}

	/**
	 * Get country code.
	 *
	 * @return string
	 */
	public function get_country_code(): string {
		return $this->country_code;
	}

	/**
	 * Set country code.
	 *
	 * @param string $country_code Country code.
	 *
	 * @return self
	 */
	public function set_country_code( string $country_code ): self {
		$this->country_code = $country_code;

		return $this;
	}

	/**
	 * Get address.
	 *
	 * @return string
	 */
	public function get_address(): string {
		return $this->address;
	}

	/**
	 * Set address.
	 *
	 * @param string $address Address.
	 *
	 * @return self
	 */
	public function set_address( string $address ): self {
		$this->address = $address;

		return $this;
	}

	/**
	 * Get city.
	 *
	 * @return string
	 */
	public function get_city(): string {
		return $this->city;
	}

	/**
	 * Set city.
	 *
	 * @param string $city City.
	 *
	 * @return self
	 */
	public function set_city( string $city ): self {
		$this->city = $city;

		return $this;
	}

	/**
	 * Get postal code.
	 *
	 * @return string
	 */
	public function get_postal_code(): string {
		return $this->postal_code;
	}

	/**
	 * Set postal code.
	 *
	 * @param string $postal_code Postal code.
	 *
	 * @return self
	 */
	public function set_postal_code( string $postal_code ): self {
		$this->postal_code = $postal_code;

		return $this;
	}
}
