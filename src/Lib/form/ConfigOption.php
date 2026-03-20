<?php

namespace Ilabs\Inpost_Pay\Lib\form;

use Ilabs\Inpost_Pay\Lib\form\exception\ValueNotSetInRequiredOptionException;

class ConfigOption {
	private string $name;
	private bool $required = false;

	private $default_value;

	private $value = null;

	public function __construct( $name, $required = false, $default_value = null ) {
		$this->name = $name;
		$this->set_required( $required );
		$this->default_value = $default_value;
	}

	private function set_required( $required ): void {
		if ( is_bool( $required ) ) {
			$this->required = $required;
		} else {
			$this->required = false;
		}

	}

	public function is_required(): bool {
		return $this->required;
	}

	public function get_default_value() {
		return $this->default_value;
	}

	/**
	 * @return null
	 * @throws ValueNotSetInRequiredOptionException
	 */
	public function get_value() {
		if ( $this->value === null ) {
			if ( ! $this->is_required() ) {
				return $this->get_default_value();
			} else {
				throw new ValueNotSetInRequiredOptionException( $this->name );
			}
		}

		return $this->value;
	}

	/**
	 * @param null $value
	 */
	public function set_value( $value ): void {
		$this->value = $value;
	}


}
