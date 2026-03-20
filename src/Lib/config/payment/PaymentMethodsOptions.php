<?php
/**
 * Payment methods options configuration
 *
 * @package InPost\Lib\config\payment
 */

namespace Ilabs\Inpost_Pay\Lib\config\payment;

use Ilabs\Inpost_Pay\Lib\Authorization;
use Ilabs\Inpost_Pay\Lib\config\ConfigInterface;
use Ilabs\Inpost_Pay\Lib\exception\AuthorizationException;
use Ilabs\Inpost_Pay\Lib\form\AbstractOption;
use Ilabs\Inpost_Pay\Lib\form\exception\NotAllowedConfigOptionException;
use Ilabs\Inpost_Pay\Lib\form\exception\NotFoundConfigOptionException;
use Ilabs\Inpost_Pay\Lib\form\exception\OptionNameRequired;
use Ilabs\Inpost_Pay\Lib\form\exception\RequiredConfigOptionException;
use Ilabs\Inpost_Pay\Lib\form\FormFieldInterface;
use Ilabs\Inpost_Pay\Lib\form\Select;

/**
 * Handles payment methods configuration options
 *
 * Provides functionality to manage and display available payment methods
 * as a selectable option in the plugin settings.
 *
 * @final
 */
final class PaymentMethodsOptions extends AbstractOption implements PaymentMethodsInterface {
	private array $payment_methods;

	/**
	 * Constructs the PaymentMethodsOptions object.
	 *
	 * Retrieves the payment methods stored in the database and sets them as the default value.
	 * Then, it calls the parent constructor with the option name and label.
	 *
	 * @return void
	 * @throws OptionNameRequired If the option name is not set.
	 */
	public function __construct() {
		$this->payment_methods = ( new PaymentMethodsGet() )->get();
		parent::__construct( self::IZI_PAYMENT_METHODS, 'Payment methods' );
	}

	/**
	 * Registers the payment methods stored in the option.
	 *
	 * If the option has no value, it will register the default value.
	 * Otherwise, it will register the value stored in the option.
	 *
	 * @param array $args The arguments passed to the register method.
	 * @return void
	 */
	public function register( array $args = array() ): void {
		parent::register();
		if ( $this->get() === false ) {
			if ( ! empty( $this->payment_methods ) ) {
				$this->update( $this->default() );
			} else {
				$this->update( array() );
			}
		}
	}

	/**
	 * Retrieves the payment methods stored in the option.
	 *
	 * If the option has no value, it will return an empty array.
	 * Otherwise, it will return the value stored in the option.
	 *
	 * @param bool $default Whether to return an empty array if the option has no value.
	 * @return array The payment methods stored in the option.
	 */
	public function get( $default = false ) {
		$payment_methods = parent::get();
		if ( is_string( $payment_methods ) ) {
			return array();
		}

		return $payment_methods;
	}

	/**
	 * Retrieves the payment methods stored in the option.
	 * If the option has value, it will return the value.
	 * Otherwise, it will return the payment methods retrieved from the payment API.
	 *
	 * @return array The payment methods.
	 */
	public function get_payment_methods(): array {
		if ( count( $this->get() ) ) {
			return $this->get();
		}

		return ( new PaymentMethodsGet() )->get();
	}


	/**
	 * Default value for option
	 *
	 * @return array
	 */
	private function default(): array {
		return array();
	}


	/**
	 * Returns a form field for the payment methods option.
	 *
	 * @return FormFieldInterface The form field.
	 * @throws NotAllowedConfigOptionException Not allowed config option.
	 * @throws NotFoundConfigOptionException Not found config option.
	 * @throws RequiredConfigOptionException Required config option.
	 */
	public function get_form_field(): FormFieldInterface {
		return new Select(
			$this->payment_methods,
			$this->get(),
			array(
				'label'        => $this->get_label(),
				'name'         => $this->get_field_name(),
				'label_class'  => 'label-gray',
				'multiple'     => true,
				'value_as_key' => true,
			)
		);
	}

	/**
	 * Checks whether the payment methods option can be shown in the form.
	 *
	 * This function checks whether an access token is available. If an access token is available, the payment methods option can be shown in the form.
	 *
	 * @return bool True if the payment methods option can be shown in the form, false otherwise.
	 */
	public function can_show_in_form(): bool {
		$authorization = new Authorization();
		try {
			$authorization->getToken();

			return true;
		} catch ( AuthorizationException $ex ) {
			return false;
		}
	}
}
