<?php
/**
 * Account info item.
 *
 * @package Inpost_Pay
 */

declare( strict_types=1 );

namespace Ilabs\Inpost_Pay\Lib\item\order;

use Ilabs\Inpost_Pay\Lib\helpers\JsonSerializationHelper;
use Ilabs\Inpost_Pay\Lib\Item;

/**
 * Represents account information.
 */
class AccountInfo extends Item implements \JsonSerializable {
	use JsonSerializationHelper;

	/**
	 * Customer name.
	 *
	 * @var string
	 */
	protected string $name;

	/**
	 * Customer surname.
	 *
	 * @var string
	 */
	protected string $surname;

	/**
	 * Customer phone number.
	 *
	 * @var PhoneNumber
	 */
	protected PhoneNumber $phone_number;

	/**
	 * Customer email address.
	 *
	 * @var string
	 */
	protected string $mail;

	/**
	 * Customer address.
	 *
	 * @var ClientAddress
	 */
	protected ClientAddress $client_address;

	/**
	 * Serialize item to array.
	 *
	 * @return array
	 */
	public function jsonSerialize(): array {
		return $this->auto_serialize();
	}

	/**
	 * Get customer name.
	 *
	 * @return string
	 */
	public function get_name(): string {
		return $this->name;
	}

	/**
	 * Set customer name.
	 *
	 * @param string $name Customer name.
	 *
	 * @return self
	 */
	public function set_name( string $name ): self {
		$this->name = $name;

		return $this;
	}

	/**
	 * Get customer surname.
	 *
	 * @return string
	 */
	public function get_surname(): string {
		return $this->surname;
	}

	/**
	 * Set customer surname.
	 *
	 * @param string $surname Customer surname.
	 *
	 * @return self
	 */
	public function set_surname( string $surname ): self {
		$this->surname = $surname;

		return $this;
	}

	/**
	 * Get customer phone number.
	 *
	 * @return PhoneNumber
	 */
	public function get_phone_number(): PhoneNumber {
		return $this->phone_number;
	}

	/**
	 * Set customer phone number.
	 *
	 * @param PhoneNumber $phone_number Customer phone number.
	 *
	 * @return self
	 */
	public function set_phone_number( PhoneNumber $phone_number ): self {
		$this->phone_number = $phone_number;

		return $this;
	}

	/**
	 * Get customer email address.
	 *
	 * @return string
	 */
	public function get_mail(): string {
		return $this->mail;
	}

	/**
	 * Set customer email address.
	 *
	 * @param string $mail Customer email address.
	 *
	 * @return self
	 */
	public function set_mail( string $mail ): self {
		$this->mail = $mail;

		return $this;
	}

	/**
	 * Get customer address.
	 *
	 * @return ClientAddress
	 */
	public function get_client_address(): ClientAddress {
		return $this->client_address;
	}

	/**
	 * Set customer address.
	 *
	 * @param ClientAddress $client_address Customer address.
	 *
	 * @return self
	 */
	public function set_client_address( ClientAddress $client_address ): self {
		$this->client_address = $client_address;

		return $this;
	}
}
