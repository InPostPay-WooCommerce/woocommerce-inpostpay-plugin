<?php

namespace Ilabs\Inpost_Pay\Lib\form;

use Ilabs\Inpost_Pay\Lib\form\exception\NotAllowedConfigOptionException;
use Ilabs\Inpost_Pay\Lib\form\exception\NotFoundConfigOptionException;
use Ilabs\Inpost_Pay\Lib\form\exception\RequiredConfigOptionException;

abstract class AbstractFormField extends AbstractConfig implements FormFieldInterface {


	/**
	 * @throws NotAllowedConfigOptionException
	 * @throws RequiredConfigOptionException
	 */
	public function __construct(
		array $config,
		?array $configOptions = null
	) {
		parent::__construct( $config );

		if ( $configOptions ) {
			$this->set_config( $configOptions );
		}

	}

	/**
	 * @throws NotFoundConfigOptionException
	 */
	public function print_label(): void {
		echo sprintf(
			'<label for="%s" class="%s">%s</label>',
			$this->get_config_option( 'name' )->get_value(),
			$this->get_config_option( 'label_class' )->get_value(),
			$this->get_config_option( 'label' )->get_value()
		);
	}

	/**
	 * @throws NotFoundConfigOptionException
	 */
	public function print_label_text(): void {
		echo __($this->get_config_option( 'label' )->get_value(), 'inpost-pay');
	}

	/**
	 * @throws NotFoundConfigOptionException
	 */
	public function get_label_name(): string {
		return $this->get_config_option( 'name' )->get_value();
	}

}
