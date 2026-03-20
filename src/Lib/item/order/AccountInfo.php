<?php

declare( strict_types=1 );

namespace Ilabs\Inpost_Pay\Lib\item\order;

use Ilabs\Inpost_Pay\Lib\helpers\JsonSerializationHelper;
use Ilabs\Inpost_Pay\Lib\Item;

class AccountInfo extends Item implements \JsonSerializable {
	use JsonSerializationHelper;

	protected string $name;
	protected string $surname;
	protected PhoneNumber $phone_number;
	protected string $mail;
	protected ClientAddress $client_address;

	public function jsonSerialize(): array {
		return $this->autoSerialize();
	}

	public function get_name(): string {
		return $this->name;
	}

	public function set_name( string $name ): self {
		$this->name = $name;

		return $this;
	}

	public function get_surname(): string {
		return $this->surname;
	}

	public function set_surname( string $surname ): self {
		$this->surname = $surname;

		return $this;
	}

	public function get_phone_number(): PhoneNumber {
		return $this->phone_number;
	}

	public function set_phone_number( PhoneNumber $phone_number ): self {
		$this->phone_number = $phone_number;

		return $this;
	}

	public function get_mail(): string {
		return $this->mail;
	}

	public function set_mail( string $mail ): self {
		$this->mail = $mail;

		return $this;
	}

	public function get_client_address(): ClientAddress {
		return $this->client_address;
	}

	public function set_client_address( ClientAddress $client_address ): self {
		$this->client_address = $client_address;

		return $this;
	}
}
