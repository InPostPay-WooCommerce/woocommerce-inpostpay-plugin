<?php
/**
 * Inpost Pay - Virtual Payment Gateway Method Configuration
 *
 * Class that configures the virtual payment method option in the Inpost Pay system.
 *
 * @package    Ilabs\Inpost_Pay
 * @subpackage Lib/config/payment
 * @author     Ilabs
 * @since      2.0.6
 */

namespace Ilabs\Inpost_Pay\Lib\config\payment;

use Ilabs\Inpost_Pay\Lib\form\AbstractOption;
use Ilabs\Inpost_Pay\Lib\form\Checkbox;
use Ilabs\Inpost_Pay\Lib\form\exception\NotAllowedConfigOptionException;
use Ilabs\Inpost_Pay\Lib\form\exception\RequiredConfigOptionException;
use Ilabs\Inpost_Pay\Lib\form\FormFieldInterface;

/**
 * Class Virtual_Payment_Gateway_Config
 *
 * Handles configuration for the virtual payment method in Inpost Pay.
 *
 * @package Ilabs\Inpost_Pay\Lib\config\payment
 * @since   2.0.6
 */
final class Virtual_Payment_Gateway_Config extends AbstractOption implements Virtual_Payment_Gateway_Config_Interface {

	/**
	 * Initializes the configuration for the virtual payment method.
	 *
	 * Sets the configuration values for the virtual payment method.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		parent::__construct(
			self::IZI_VIRTUAL_PAYMENT_METHOD_CONFIG,
			__( 'Virtual Payment Gateway', 'inpost-pay' ),
			__( 'Enable virtual payment method gateway. For more information about this integration, refer to the documentation.', 'inpost-pay' )
		);
	}

	/**
	 * Registers the virtual payment method configuration in the system.
	 *
	 * Accepts optional arguments to define the type and default value.
	 *
	 * @param array $args Optional configuration arguments.
	 *                 Default: ['type' => 'string', 'default' => 'no'].
	 * @return void
	 * @since 1.0.0
	 */
	public function register( array $args = array() ): void {
		parent::register(
			array(
				'type'    => 'string',
				'default' => self::IZI_VIRTUAL_PAYMENT_METHOD_CONFIG_DEFAULT,
			)
		);
	}

	/**
	 * Checks whether the virtual payment method is enabled.
	 *
	 * Returns true if the configuration value is 'yes', otherwise false.
	 *
	 * @return bool True if enabled, false otherwise.
	 * @since 2.0.6
	 */
	public function is_enabled(): bool {
		return $this->get() === 'yes';
	}

	/**
	 * Retrieves the value of the virtual payment method configuration.
	 *
	 * Returns 'yes' if the value is 'on' or 'yes', otherwise returns 'no'.
	 *
	 * @param bool $default Default value to return if the value is not set.
	 * @return string 'yes' or 'no' based on the configuration setting.
	 * @since 1.0.0
	 */
	public function get( $default = false ): string {
		if ( parent::get( self::IZI_VIRTUAL_PAYMENT_METHOD_CONFIG_DEFAULT ) === 'on' || parent::get( self::IZI_VIRTUAL_PAYMENT_METHOD_CONFIG_DEFAULT ) === 'yes' ) {
			return 'yes';
		}

		return 'no';
	}

	/**
	 * Creates a form field for the virtual payment method.
	 *
	 * Returns a Checkbox form field with appropriate labels and attributes.
	 *
	 * @throws RequiredConfigOptionException If the configuration is required.
	 * @throws NotAllowedConfigOptionException If the value is not allowed.
	 * @return FormFieldInterface The checkbox forms a field instance.
	 * @since 2.0.6
	 */
	public function get_form_field(): FormFieldInterface {
		return new Checkbox(
			$this->get(),
			array(
				'label'       => $this->get_label(),
				'name'        => $this->get_field_name(),
				'label_class' => 'label-gray',
				'class'       => 'mobileToggle',
			)
		);
	}
}
