<?php
/**
 * Abstract hook configuration.
 *
 * @package Ilabs\Inpost_Pay\Lib\config\Hooks
 */

declare( strict_types=1 );

namespace Ilabs\Inpost_Pay\Lib\config\Hooks;

use Ilabs\Inpost_Pay\Lib\form\AbstractArrayOption;
use Ilabs\Inpost_Pay\Lib\form\Checkbox;
use Ilabs\Inpost_Pay\Lib\form\CheckboxList;
use Ilabs\Inpost_Pay\Lib\form\FormFieldInterface;
use Ilabs\Inpost_Pay\Lib\form\exception\NotAllowedConfigOptionException;
use Ilabs\Inpost_Pay\Lib\form\exception\RequiredConfigOptionException;

/**
 * Class AbstractHookConfig
 *
 * Base class for WordPress hook-based configuration sections stored as array options.
 */
abstract class AbstractHookConfig extends AbstractArrayOption {

	/**
	 * List of available hook keys mapped to their WooCommerce hook names.
	 *
	 * @var array<string, string>
	 */
	protected array $available_hooks = array();

	/**
	 * Registers the option with WordPress settings API.
	 *
	 * @param array $args Optional registration arguments.
	 *
	 * @return void
	 */
	public function register( array $args = array() ): void {
		register_setting( 'inpost-izi', static::OPTION_NAME );

		parent::register(
			array(
				'type'    => 'array',
				'default' => array(),
			)
		);
	}

	/**
	 * Returns the map of available hook keys to hook names.
	 *
	 * @return array<string, string>
	 */
	public function get_hooks(): array {
		return $this->available_hooks;
	}

	/**
	 * Returns an array of checkbox form fields for each available hook.
	 *
	 * @throws RequiredConfigOptionException   When required option data is missing.
	 * @throws NotAllowedConfigOptionException When the option value is not allowed.
	 *
	 * @return array
	 */
	public function get_form_fields(): array {
		$current_values = $this->get( array() );
		if ( ! is_array( $current_values ) ) {
			$current_values = array();
		}

		$fields = array();

		foreach ( $this->available_hooks as $key => $label ) {
			$fields[] = new Checkbox(
				$key,
				array(
					'label' => $label,
					'name'  => static::OPTION_NAME,
					'class' => 'hook-checkbox',
				),
				in_array( $key, $current_values, true )
			);
		}

		return $fields;
	}

	/**
	 * Returns the form field representation as a checkbox list.
	 *
	 * @return FormFieldInterface
	 */
	public function get_form_field(): FormFieldInterface {
		return new CheckboxList(
			$this->get_form_fields(),
			$this->get_label(),
			$this->get_description()
		);
	}
}
