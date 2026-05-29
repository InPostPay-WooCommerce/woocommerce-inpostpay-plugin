<?php
/**
 * Plugin configuration interface.
 *
 * @package Ilabs\Inpost_Pay\Lib\config
 */

declare( strict_types=1 );

namespace Ilabs\Inpost_Pay\Lib\config;

use Ilabs\Inpost_Pay\Lib\form\FormFieldInterface;

/**
 * Interface ConfigInterface
 *
 * Defines the contract for all plugin configuration options.
 */
interface ConfigInterface {

	public const OPTION_GROUP = 'inpost-izi';

	public const TRANSLATION_DOMAIN = 'inpost-pay';

	/**
	 * Registers the option with WordPress settings API.
	 *
	 * @return void
	 */
	public function register();

	/**
	 * Returns the current value of the option.
	 *
	 * @return mixed
	 */
	public function get();

	/**
	 * Updates the option value.
	 *
	 * @param mixed $value New value to save.
	 *
	 * @return bool
	 */
	public function update( $value ): bool;

	/**
	 * Returns the field label.
	 *
	 * @return string
	 */
	public function get_label(): string;

	/**
	 * Returns the HTML field name attribute.
	 *
	 * @return string
	 */
	public function get_field_name(): string;

	/**
	 * Returns the form field representation.
	 *
	 * @return FormFieldInterface
	 */
	public function get_form_field(): FormFieldInterface;

	/**
	 * Returns the field description.
	 *
	 * @return string|null
	 */
	public function get_description(): ?string;

	/**
	 * Returns the field tooltip.
	 *
	 * @return string|null
	 */
	public function get_tooltip(): ?string;
}
