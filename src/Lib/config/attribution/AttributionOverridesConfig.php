<?php

namespace Ilabs\Inpost_Pay\Lib\config\attribution;

use Ilabs\Inpost_Pay\Lib\form\AbstractOption;
use Ilabs\Inpost_Pay\Lib\form\Checkbox;
use Ilabs\Inpost_Pay\Lib\form\exception\NotAllowedConfigOptionException;
use Ilabs\Inpost_Pay\Lib\form\exception\RequiredConfigOptionException;
use Ilabs\Inpost_Pay\Lib\form\FormFieldInterface;

final class AttributionOverridesConfig extends AbstractOption implements AttributionOverridesConfigInterface {


	public function __construct() {
		parent::__construct(
			self::IZI_ATTRIBUTION_OVERRIDES,
			self::IZI_ATTRIBUTION_OVERRIDES_LABEL,
			self::IZI_ATTRIBUTION_OVERRIDES_DESCRIPTION,
		);
	}

	public function register( array $args = [] ): void {
		parent::register( [
			'type'    => 'string',
			'default' => self::IZI_ATTRIBUTION_OVERRIDES_DEFAULT,
		] );
	}

	public function get( $default = false ): string {
		if ( parent::get( self::IZI_ATTRIBUTION_OVERRIDES_DEFAULT ) === 'on' || parent::get( self::IZI_ATTRIBUTION_OVERRIDES_DEFAULT ) === 'yes' ) {
			return 'yes';
		}

		return 'no';
	}

	public function is_enabled(): bool {
		return $this->get() === 'yes';
	}


	/**
	 * @throws RequiredConfigOptionException
	 * @throws NotAllowedConfigOptionException
	 */
	public function get_form_field(): FormFieldInterface {
		return new Checkbox( $this->get(), [
			'label'       => $this->get_label(),
			'name'        => $this->get_field_name(),
			'label_class' => 'label-gray',
			'class'       => 'mobileToggle'
		] );
	}
}
