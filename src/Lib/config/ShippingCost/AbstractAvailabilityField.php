<?php

namespace Ilabs\Inpost_Pay\Lib\config\ShippingCost;

use Ilabs\Inpost_Pay\Lib\form\AbstractOption;
use Ilabs\Inpost_Pay\Lib\form\exception\NotAllowedConfigOptionException;
use Ilabs\Inpost_Pay\Lib\form\exception\NotFoundConfigOptionException;
use Ilabs\Inpost_Pay\Lib\form\exception\RequiredConfigOptionException;
use Ilabs\Inpost_Pay\Lib\form\FormFieldInterface;
use Ilabs\Inpost_Pay\Lib\form\Select;

abstract class AbstractAvailabilityField extends AbstractZoneOption implements ShippingMappingFieldInterface {

	public function register( array $args = array() ): void {
		parent::register( $args );
	}

	/**
	 * @throws RequiredConfigOptionException
	 * @throws NotAllowedConfigOptionException
	 * @throws NotFoundConfigOptionException
	 */
	public function get_form_field(): FormFieldInterface {
		// todo change
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
