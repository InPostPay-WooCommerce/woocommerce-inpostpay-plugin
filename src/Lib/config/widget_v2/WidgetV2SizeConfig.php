<?php
/**
 * Widget V2 size configuration.
 *
 * @package Ilabs\Inpost_Pay\Lib\config\widget_v2
 */

declare( strict_types=1 );

namespace Ilabs\Inpost_Pay\Lib\config\widget_v2;

use Ilabs\Inpost_Pay\Lib\form\AbstractOption;
use Ilabs\Inpost_Pay\Lib\form\exception\NotAllowedConfigOptionException;
use Ilabs\Inpost_Pay\Lib\form\exception\NotFoundConfigOptionException;
use Ilabs\Inpost_Pay\Lib\form\exception\RequiredConfigOptionException;
use Ilabs\Inpost_Pay\Lib\form\FormFieldInterface;
use Ilabs\Inpost_Pay\Lib\form\Select;

/**
 * Class WidgetV2SizeConfig
 *
 * WordPress option storing the size class for the InPost Pay widget V2.
 */
final class WidgetV2SizeConfig extends AbstractOption implements WidgetV2SizeConfigInterface {


	/**
	 * Constructor.
	 */
	public function __construct() {
		parent::__construct(
			self::IZI_WIDGET_V2_SIZE,
			__( 'Widget size', 'inpost-pay' ),
			__( 'Defines the size of the widget', 'inpost-pay' )
		);
	}

	/**
	 * Registers the option with WordPress settings API.
	 *
	 * @param array $args Optional registration arguments.
	 *
	 * @return void
	 */
	public function register( array $args = array() ): void {
		parent::register(
			array(
				'type'    => 'array',
				'default' => self::IZI_WIDGET_V2_SIZE_DEFAULT,
			)
		);
	}

	/**
	 * Returns the current size value as an array.
	 *
	 * @param mixed $default_value Default value when the option is absent.
	 *
	 * @return array
	 */
	public function get( $default_value = false ): array {
		if ( is_array( parent::get( $default_value ) ) ) {
			return parent::get( $default_value );
		}

		return array( parent::get( $default_value ) );
	}


	/**
	 * Returns the select form field for this option.
	 *
	 * @throws RequiredConfigOptionException   When required option data is missing.
	 * @throws NotAllowedConfigOptionException When the option value is not allowed.
	 * @throws NotFoundConfigOptionException   When the option cannot be found.
	 *
	 * @return FormFieldInterface
	 */
	public function get_form_field(): FormFieldInterface {
		return new Select(
			self::IZI_WIDGET_V2_SIZE_OPTIONS,
			$this->get(),
			array(
				'label'       => $this->get_label(),
				'name'        => $this->get_field_name(),
				'label_class' => 'label-gray',
				'class'       => 'mobileToggle',
			)
		);
	}

	/**
	 * Returns the size value as a space-separated string.
	 *
	 * @return string
	 */
	public function get_array_as_string(): string {
		$array = $this->get( self::IZI_WIDGET_V2_SIZE_DEFAULT );
		return implode( ' ', $array );
	}
}
