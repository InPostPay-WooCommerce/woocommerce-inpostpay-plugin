<?php

namespace Ilabs\Inpost_Pay\Lib\form\error;

use Ilabs\Inpost_Pay\Lib\InPostIzi;

abstract class AbstractErrorMessage {
	protected string $messages;

	protected array $values;

	public function __construct( string $messages, array $values ) {
		$this->messages = $messages;
		$this->values   = $values;
	}

	public function get_messages(): string {
		return $this->messages;
	}
	public function get_values(): array {
		return $this->values;
	}

	public function print(): ?string {
		if ( empty($this->values) ) {
			return __($this->messages, InPostIzi::TRANSLATION_DOMAIN);
		}

		return sprintf(__($this->messages, InPostIzi::TRANSLATION_DOMAIN), $this->values);
	}
}
