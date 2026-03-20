<?php

namespace Ilabs\Inpost_Pay\Lib\Authentication;

class Credentials {
	private ?int $id = null;

	private ?string $email = null;

	private ?string $phone_number = null;

	public function get_id(): ?int {
		return $this->id;
	}

	public function set_id( ?int $id ): void {
		$this->id = $id;
	}

	public function get_email(): ?string {
		return $this->email;
	}

	public function set_email( ?string $email ): void {
		$this->email = $email;
	}

	public function get_phone_number(): ?string {
		return $this->phone_number;
	}

	public function set_phone_number( ?string $phone_number ): void {
		$this->phone_number = $phone_number;
	}


}
