<?php

namespace Ilabs\Inpost_Pay\Lib\form;

class SelectOption {
	private string $name;

	private bool $selected = false;

	public function __construct( $name, $selected = false ) {
		$this->name     = $name;
		$this->selected = $selected;
	}

	public function get_name(): string {
		return $this->name;
	}

	public function get_translated_name(): ?string {
		return __($this->get_name(), 'inpost-pay');
	}

	public function is_selected(): bool {
		return $this->selected;
	}

	public function print_selected(): string {
		if ($this->is_selected()) {
			return 'selected';
		}
		return '';
	}


}
