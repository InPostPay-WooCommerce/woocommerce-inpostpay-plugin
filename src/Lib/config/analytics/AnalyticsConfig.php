<?php

namespace Ilabs\Inpost_Pay\Lib\config\analytics;

use Ilabs\Inpost_Pay\Lib\form\AbstractOption;
use Ilabs\Inpost_Pay\Lib\form\Checkbox;
use Ilabs\Inpost_Pay\Lib\form\exception\NotAllowedConfigOptionException;
use Ilabs\Inpost_Pay\Lib\form\exception\RequiredConfigOptionException;
use Ilabs\Inpost_Pay\Lib\form\FormFieldInterface;

final class AnalyticsConfig extends AbstractOption implements AnalyticsConfigInterface {


	public function __construct() {
		parent::__construct(
			self::IZI_ANALYTICS,
			self::IZI_ANALYTICS_LABEL,
			self::IZI_ANALYTICS_DESCRIPTION
		);
	}

	public function register( array $args = array() ): void {
		parent::register(
			array(
				'type'    => 'string',
				'default' => self::IZI_ANALYTICS_DEFAULT,
			)
		);
	}

	public function get( $default = false ): string {
		if ( parent::get( self::IZI_ANALYTICS_DEFAULT ) === 'on' || parent::get( self::IZI_ANALYTICS_DEFAULT ) === 'yes' ) {
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
