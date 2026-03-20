<?php

namespace Ilabs\Inpost_Pay\Lib\config\Hooks;

use Ilabs\Inpost_Pay\Lib\form\AbstractArrayOption;
use Ilabs\Inpost_Pay\Lib\form\Checkbox;
use Ilabs\Inpost_Pay\Lib\form\CheckboxList;
use Ilabs\Inpost_Pay\Lib\form\FormFieldInterface;
use Ilabs\Inpost_Pay\Lib\form\exception\NotAllowedConfigOptionException;
use Ilabs\Inpost_Pay\Lib\form\exception\RequiredConfigOptionException;

/**
 * Abstract class for hook-based config sections.
 */
abstract class AbstractHookConfig extends AbstractArrayOption {

	/**
	 * List of hook keys and labels (must be set in subclass)
	 */
	protected array $available_hooks = [];

	public function register( array $args = [] ): void {
		register_setting('inpost-izi', static::OPTION_NAME);

		parent::register( [
			'type'    => 'array',
			'default' => [],
		] );
	}

	public function get_hooks(): array {
		return $this->available_hooks;
	}

	/**
	 * @throws RequiredConfigOptionException
	 * @throws NotAllowedConfigOptionException
	 */
	public function get_form_fields(): array {
		$current_values = $this->get( [] );
		if ( ! is_array( $current_values ) ) {
			$current_values = [];
		}

		$fields = [];

		foreach ( $this->available_hooks as $key => $label ) {
			$fields[] = new Checkbox(
				$key,
				[
					'label' => $label,
					'name'  => static::OPTION_NAME,
					'class' => 'hook-checkbox',
				],
				in_array( $key, $current_values, true )
			);
		}

		return $fields;
	}

	public function get_form_field(): FormFieldInterface {
		return new CheckboxList(
			$this->get_form_fields(),
			$this->get_label(),
			$this->get_description()
		);
	}
}
