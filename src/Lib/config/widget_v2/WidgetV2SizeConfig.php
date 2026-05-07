<?php

namespace Ilabs\Inpost_Pay\Lib\config\widget_v2;

use Ilabs\Inpost_Pay\Lib\form\AbstractOption;
use Ilabs\Inpost_Pay\Lib\form\exception\NotAllowedConfigOptionException;
use Ilabs\Inpost_Pay\Lib\form\exception\NotFoundConfigOptionException;
use Ilabs\Inpost_Pay\Lib\form\exception\RequiredConfigOptionException;
use Ilabs\Inpost_Pay\Lib\form\FormFieldInterface;
use Ilabs\Inpost_Pay\Lib\form\Select;

final class WidgetV2SizeConfig extends AbstractOption implements WidgetV2SizeConfigInterface {


	public function __construct() {
		parent::__construct(
			self::IZI_WIDGET_V2_SIZE,
			__( self::IZI_WIDGET_V2_SIZE_LABEL, 'inpost-pay' ),
			__( self::IZI_WIDGET_V2_SIZE_DESCRIPTION, 'inpost-pay' )
		);
	}

	public function register( array $args = array() ): void {
		parent::register(
			array(
				'type'    => 'array',
				'default' => self::IZI_WIDGET_V2_SIZE_DEFAULT,
			)
		);
	}

	public function get( $default = false ): array {
		if ( is_array( parent::get( $default ) ) ) {
			return parent::get( $default );
		}

		return array( parent::get( $default ) );
	}


	/**
	 * @throws RequiredConfigOptionException
	 * @throws NotAllowedConfigOptionException
	 * @throws NotFoundConfigOptionException
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

	public function getArrayAsString(): string {
		$array = $this->get( self::IZI_WIDGET_V2_SIZE_DEFAULT );
		return implode( ' ', $array );
	}
}
