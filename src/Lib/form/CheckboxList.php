<?php

namespace Ilabs\Inpost_Pay\Lib\form;

use Ilabs\Inpost_Pay\Lib\form\exception\NotAllowedConfigOptionException;
use Ilabs\Inpost_Pay\Lib\form\exception\NotFoundConfigOptionException;
use Ilabs\Inpost_Pay\Lib\form\exception\RequiredConfigOptionException;

class CheckboxList extends AbstractFormField {

	/** @var Checkbox[] */
	private array $checkboxes;

	protected array $configOptions = [
		'label'       => [ 'required' => false ],
		'description' => [ 'required' => false ],
		'class'       => [ 'default' => 'checkbox-list', 'required' => false ],
	];

	/**
	 * @param Checkbox[] $checkboxes
	 * @param string $label
	 * @param string $description
	 *
	 * @throws NotAllowedConfigOptionException
	 * @throws RequiredConfigOptionException
	 */
	public function __construct(array $checkboxes, string $label = '', string $description = '') {
		parent::__construct($this->configOptions, [
			'label'       => $label,
			'description' => $description,
		]);

		$this->checkboxes = $checkboxes;
	}

	/**
	 * Główna metoda wyświetlania listy checkboxów
	 *
	 * @throws NotFoundConfigOptionException
	 */
	public function print_field(): void {
		$class = esc_attr($this->get_config_option('class')->get_value());
		echo '<div class="' . $class . '">';
		$this->print_checkboxes();
		echo '</div>';
	}

	/**
	 * Wyświetla <legend> z label
	 *
	 * @throws NotFoundConfigOptionException
	 */
	public function print_legend(): void {
		$label = $this->get_config_option('label')->get_value();
		if ($label) {
			echo '<legend class="text-bold">' . esc_html($label) . '</legend>';
		}
	}

	/**
	 * Renderuje każdy checkbox
	 *
	 * @throws NotFoundConfigOptionException
	 */
	private function print_checkboxes(): void {
		foreach ($this->checkboxes as $checkbox) {
			$value = esc_attr($checkbox->get_value());
			$label = esc_html($checkbox->get_config_option('label')->get_value());
			$class = esc_attr($checkbox->get_config_option('class')->get_value());
			$checked = $checkbox->is_checked() ? 'checked' : '';

			echo '<label style="display: block; margin: 4px 0;">';
			printf(
				'<input type="checkbox" name="%s[]" value="%s" class="%s" %s />',
				esc_attr($checkbox->get_config_option('name')->get_value()),
				$value,
				$class,
				$checked
			);
			echo ' <code>' . $label . '</code>';
			echo '</label>';
		}
	}
}
