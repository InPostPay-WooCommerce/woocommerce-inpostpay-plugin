<?php

namespace Ilabs\Inpost_Pay\Lib\form;

use Ilabs\Inpost_Pay\Lib\form\exception\NotAllowedConfigOptionException;
use Ilabs\Inpost_Pay\Lib\form\exception\NotFoundConfigOptionException;
use Ilabs\Inpost_Pay\Lib\form\exception\RequiredConfigOptionException;

class Select extends AbstractFormField {

	private array $options = [];
	private array $available_options = [];

	private array $selected_options = [];

	private array $configOptions = [
		'label'    => [
			'required' => true
		],
		'label_class' => [
			'default'  => 'label',
			'required' => false
		],
		'name'     => [
			'required' => true
		],
		'class'    => [
			'required' => false,
			'default'  => 'select'
		],
		'value_as_key' => [
			'required' => false,
			'default'  => false
		],
		'multiple' => false
	];

	/**
	 * @param array $available_options
	 * @param array $selected_options
	 * @param array $config
	 *
	 * @throws NotAllowedConfigOptionException
	 * @throws RequiredConfigOptionException
	 * @throws NotFoundConfigOptionException
	 */
	public function __construct(
		array $available_options,
		array $selected_options,
		array $config
	) {
		$this->available_options = $available_options;
		$this->selected_options  = $selected_options;

		parent::__construct( $this->configOptions, $config );
		$this->map_options($this->get_config_option('value_as_key')->get_value());
	}

	/**
	 * @return array
	 */
	public function get_options(): array {
		return $this->options;
	}

	public function get_available_options(): array {
		return $this->available_options;
	}

	public function get_selected_options(): array {
		return $this->selected_options;
	}

	private function map_options($value_as_key = false) {
		foreach ( $this->available_options as $key => $value ) {
			if ( in_array( $value_as_key? $value : $key, $this->selected_options ) ) {
				$this->options[ $value_as_key? $value : $key ] = new SelectOption( $value, true );
			} else {
				$this->options[ $value_as_key? $value : $key ] = new SelectOption( $value, false );
			}
		}
	}



	/**
	 * @throws NotFoundConfigOptionException
	 */
	public function print_field(): void {
		$this->print_select();
	}

	/**
	 * @throws NotFoundConfigOptionException
	 */
	public function print_select() {

		$multiple_val = $this->get_config_option( 'multiple' )->get_value();

		echo sprintf(
			'<select name="%s" id="%s" class="%s" %s>',
			$this->get_field_name(),
			$this->get_config_option( 'name' )->get_value(),
			$this->get_config_option( 'class' )->get_value(),
			$multiple_val ? sprintf( 'multiple="%s"', $multiple_val ) : ''
		);

		foreach ( $this->get_options() as $option ) {
			echo sprintf(
				'<option value="%s" %s >%s</option>',
				$option->get_name(),
				$option->print_selected(),
				$option->get_translated_name(),
			);
		}

		echo '</select>';
	}

	/**
	 * @throws NotFoundConfigOptionException
	 */
	private function get_field_name() {
		$name = $this->get_config_option( 'name' )->get_value();
		if ($this->get_config_option( 'multiple' )->get_value()) {
			$name .= '[]';
		}
		return $name;
	}


}
