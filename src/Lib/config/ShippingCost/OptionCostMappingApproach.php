<?php

namespace Ilabs\Inpost_Pay\Lib\config\ShippingCost;

use Ilabs\Inpost_Pay\Lib\form\AbstractOption;
use Ilabs\Inpost_Pay\Lib\form\FormFieldInterface;
use Ilabs\Inpost_Pay\Lib\form\Select;

final class OptionCostMappingApproach extends AbstractOption {

	const OPTION_COST_MAPPING_APPROACH_SHIPPING_METHOD = 'shipping_method';

	const OPTION_COST_MAPPING_APPROACH_FEE = 'fee';

	private string $default;
	private string $label;
	private string $tooltip;

	public static function instance(
		string $id,
		string $label,
		string $tooltip,
		string $default = self::OPTION_COST_MAPPING_APPROACH_SHIPPING_METHOD
	): self {
		return new self( $id, $label, $tooltip, $default );
	}

	public function __construct(
		string $id,
		string $label,
		string $tooltip,
		string $default = ''
	) {
		$this->default = $default;
		$this->label   = $label;
		$this->tooltip = $tooltip;
		parent::__construct( $id );
	}

	public function init(): void {
		parent::register(
			array(
				'type'    => 'string',
				'default' => $this->default,
			)
		);
	}

	public function get_label(): string {
		return $this->label;
	}

	public function get_tooltip(): string {
		return $this->tooltip;
	}

	public function get_form_field(): FormFieldInterface {

		return new Select(
			array(),
			array( $this->get() ),
			array(
				'label'        => $this->get_label(),
				'name'         => $this->get_field_name(),
				'label_class'  => 'label-gray',
				'multiple'     => false,
				'value_as_key' => true,
			)
		);
	}
}
