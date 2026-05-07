<?php

namespace Ilabs\Inpost_Pay\Lib\config\ShippingCost;

use Ilabs\Inpost_Pay\Lib\form\AbstractOption;
use Ilabs\Inpost_Pay\Lib\form\FormFieldInterface;
use Ilabs\Inpost_Pay\Lib\form\LegacyOptionInterface;
use Ilabs\Inpost_Pay\Lib\form\Select;

final class CheckShippingAvailability extends AbstractOption implements LegacyOptionInterface {

	public static function instance(): self {
		return new self();
	}

	public function __construct() {

		parent::__construct( 'izi_shipping_check_shipping_availability' );
	}

	public function init(): void {
		parent::register(
			array(
				'type'    => 'bool',
				'default' => false,
			)
		);
	}

	public function get_label(): string {
		// todo uściślić, że dla produktu
		return __(
			'Prices and courier shipping availability map with:',
			'inpost-pay'
		);
	}

	public function get_legacy_option_id(): string {
		return 'izi_check_shipping_availability';
	}

	public function has_legacy_option_priority(): bool {
		return true;
	}

	public function get_tooltip(): string {
		return __(
			'Determines which shipping method is to be associated',
			'inpost-pay'
		);
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
