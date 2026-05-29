<?php
/**
 * Abstract availability field.
 *
 * @package Ilabs\Inpost_Pay\Lib\config\ShippingCost
 */

declare( strict_types=1 );

namespace Ilabs\Inpost_Pay\Lib\config\ShippingCost;

use Ilabs\Inpost_Pay\Lib\form\AbstractOption;
use Ilabs\Inpost_Pay\Lib\form\exception\NotAllowedConfigOptionException;
use Ilabs\Inpost_Pay\Lib\form\exception\NotFoundConfigOptionException;
use Ilabs\Inpost_Pay\Lib\form\exception\RequiredConfigOptionException;
use Ilabs\Inpost_Pay\Lib\form\FormFieldInterface;
use Ilabs\Inpost_Pay\Lib\form\Select;

/**
 * Class AbstractAvailabilityField
 *
 * Base class for shipping availability day/hour window fields.
 */
abstract class AbstractAvailabilityField extends AbstractZoneOption implements ShippingMappingFieldInterface {

	/**
	 * Registers the availability field option.
	 *
	 * @param array $args Optional registration arguments.
	 *
	 * @return void
	 */
	public function register( array $args = array() ): void {
		parent::register( $args );
	}

	/**
	 * Returns the form field for this availability field.
	 *
	 * @throws RequiredConfigOptionException   When required option data is missing.
	 * @throws NotAllowedConfigOptionException When the option value is not allowed.
	 * @throws NotFoundConfigOptionException   When the option cannot be found.
	 *
	 * @return FormFieldInterface
	 */
	public function get_form_field(): FormFieldInterface {
		// todo: change.
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
