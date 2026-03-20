<?php

namespace Ilabs\Inpost_Pay\Lib\form;

use Ilabs\Inpost_Pay\Lib\form\exception\NotAllowedConfigOptionException;
use Ilabs\Inpost_Pay\Lib\form\exception\NotFoundConfigOptionException;
use Ilabs\Inpost_Pay\Lib\form\exception\RequiredConfigOptionException;

class Checkbox extends AbstractFormField {

	private array $configOptions = [
		'label'       => [
			'required' => true
		],
		'label_class' => [
			'default'  => 'label',
			'required' => false
		],
		'name'        => [
			'required' => true
		],
		'class'       => [
			'required' => false,
			'default'  => 'checkbox'
		],
	];

	private bool $checked;

	private string $value;

	/**
	 * @param string $value
	 * @param array $config
	 * @param bool $checked
	 *
	 * @throws NotAllowedConfigOptionException
	 * @throws RequiredConfigOptionException
	 */
	public function __construct(
		string $value,
		array $config,
		bool $checked = false
	) {
		parent::__construct( $this->configOptions, $config );

		$this->value = $value;
		$this->checked = $checked;
	}

	/**
	 * Get the value associated with this checkbox.
	 *
	 * @return string
	 */
	public function get_value(): string {
		return $this->value;
	}

	/**
	 *  Determines if the checkbox should be checked.
	 *
	 * @return bool True if checkbox should be checked, false otherwise.
	 */
	public function is_checked(): bool {
		return $this->checked;
	}

	/**
	 * @throws NotFoundConfigOptionException
	 */
	public function print_field(): void {
		$this->print_checkbox();
	}

	public function get_bool(): bool {
		$val = $this->value;

		return in_array( $val, [ '1', 'yes', 'true', 'tak' ] );
	}

	public function print_checked(): string {
		return ! empty( $this->get_bool() ) ? 'checked' : '';
	}

	/**
	 * @throws NotFoundConfigOptionException
	 */
	public function print_checkbox(): void {

		echo sprintf(
			'<input type="checkbox" value="yes" name="%s" id="%s" class="%s" %s>',
			$this->get_field_name(),
			$this->get_config_option( 'name' )->get_value(),
			$this->get_config_option( 'class' )->get_value(),
			$this->print_checked(),
		);

	}

	/**
	 * @throws NotFoundConfigOptionException
	 */
	public function get_field_name() {
		return $this->get_config_option( 'name' )->get_value();
	}
}
