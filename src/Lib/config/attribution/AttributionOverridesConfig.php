<?php
/**
 * Attribution overrides configuration.
 *
 * @package Ilabs\Inpost_Pay\Lib\config\attribution
 */

declare( strict_types=1 );

namespace Ilabs\Inpost_Pay\Lib\config\attribution;

use Ilabs\Inpost_Pay\Lib\form\AbstractOption;
use Ilabs\Inpost_Pay\Lib\form\Checkbox;
use Ilabs\Inpost_Pay\Lib\form\exception\NotAllowedConfigOptionException;
use Ilabs\Inpost_Pay\Lib\form\exception\RequiredConfigOptionException;
use Ilabs\Inpost_Pay\Lib\form\FormFieldInterface;

/**
 * Class AttributionOverridesConfig
 *
 * WordPress option that controls whether InPost Pay attribution overwrites the original order attribution.
 */
final class AttributionOverridesConfig extends AbstractOption implements AttributionOverridesConfigInterface {


	/**
	 * Constructor.
	 */
	public function __construct() {
		parent::__construct(
			self::IZI_ATTRIBUTION_OVERRIDES,
			self::IZI_ATTRIBUTION_OVERRIDES_LABEL,
			self::IZI_ATTRIBUTION_OVERRIDES_DESCRIPTION,
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
				'type'    => 'string',
				'default' => self::IZI_ATTRIBUTION_OVERRIDES_DEFAULT,
			)
		);
	}

	/**
	 * Returns the option value normalised to 'yes' or 'no'.
	 *
	 * @param mixed $default_value Default value when the option is absent.
	 *
	 * @return string
	 */
	public function get( $default_value = false ): string {
		if ( parent::get( self::IZI_ATTRIBUTION_OVERRIDES_DEFAULT ) === 'on' || parent::get( self::IZI_ATTRIBUTION_OVERRIDES_DEFAULT ) === 'yes' ) { // phpcs:ignore
			return 'yes';
		}

		return 'no';
	}

	/**
	 * Returns true when attribution overrides are enabled.
	 *
	 * @return bool
	 */
	public function is_enabled(): bool {
		return $this->get() === 'yes';
	}


	/**
	 * Returns the checkbox form field for this option.
	 *
	 * @throws RequiredConfigOptionException   When required option data is missing.
	 * @throws NotAllowedConfigOptionException When the option value is not allowed.
	 *
	 * @return FormFieldInterface
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
