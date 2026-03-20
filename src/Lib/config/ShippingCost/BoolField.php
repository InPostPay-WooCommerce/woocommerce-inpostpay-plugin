<?php

namespace Ilabs\Inpost_Pay\Lib\config\ShippingCost;

use Ilabs\Inpost_Pay\Lib\form\AbstractOption;
use Ilabs\Inpost_Pay\Lib\form\FormFieldInterface;
use Ilabs\Inpost_Pay\Lib\form\Select;

final class BoolField extends AbstractOption {

	private bool $default;
	private string $label;
	private string $tooltip;

	public static function instance(
		string $id,
		string $label,
		string $tooltip,
		bool $default = true
	): self {
		return new self( $id, $label, $tooltip, $default );
	}

	public function __construct(
		string $id,
		string $label,
		string $tooltip,
		bool $default = true
	) {
		$this->default = $default;
		$this->label   = $label;
		$this->tooltip = $tooltip;
		parent::__construct( $id );
	}

	public function init(): void {
		parent::register( [
			'type'    => 'bool',
			'default' => $this->default,
		] );
	}

	public function get_label(): string {//
		return $this->label;
	}

	public function get_tooltip(): string {
		return $this->tooltip;
	}

	public function get_form_field(): FormFieldInterface {

		return new Select(
			[],
			[ $this->get() ],
			[
				'label'        => $this->get_label(),
				'name'         => $this->get_field_name(),
				'label_class'  => 'label-gray',
				'multiple'     => false,
				'value_as_key' => true,
			]
		);
	}
}
