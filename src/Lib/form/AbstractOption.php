<?php

namespace Ilabs\Inpost_Pay\Lib\form;

use Ilabs\Inpost_Pay\Lib\config\ConfigInterface;
use Ilabs\Inpost_Pay\Lib\form\exception\OptionNameRequired;

abstract class AbstractOption implements ConfigInterface {

	private const NO_OPTION_IN_DB = false;

	private string $option_name;

	private ?string $label       = null;
	private ?string $description = null;
	private ?string $tooltip     = null;

	/**
	 * @param string|null $option_name
	 * @param string|null $label
	 * @param string|null $description
	 * @param string|null $tooltip
	 *
	 * @throws OptionNameRequired
	 */
	public function __construct(
		string $option_name,
		?string $label = null,
		?string $description = null,
		?string $tooltip = null
	) {
		$this->option_name = $option_name;

		if ( $option_name === '' ) {
			throw new OptionNameRequired();
		}

		$this->label       = $label;
		$this->description = $description;
		$this->tooltip     = $tooltip;
	}

	/**
	 * @param array $args
	 *
	 * @return void
	 */
	public function register( array $args = array() ): void {
		register_setting( self::OPTION_GROUP, $this->option_name, $args );
	}

	public function get( $default = false ) {
		$optionValue = get_option( $this->option_name );
		if ( self::NO_OPTION_IN_DB === $optionValue ) {
			if ( $this instanceof LegacyOptionInterface ) {

				return get_option(
					$this->get_legacy_option_id(),
					$default
				);
			}

			return $default;
		}

		return $optionValue;
	}

	public function get_bool( bool $default = false ): bool {
		$val = (string) $this->get( $default );

		return in_array( $val, array( '1', 'yes', 'true', 'tak' ) );
	}

	public function update( $value ): bool {
		return update_option( $this->option_name, $value );
	}

	public function get_label(): string {
		return __( $this->label, self::TRANSLATION_DOMAIN );
	}

	public function set_field_name( string $field_name ): void {
		$this->option_name = $field_name;
	}

	public function get_field_name(): string {
		return $this->option_name;
	}

	public function get_description(): ?string {
		return $this->description;
	}

	public function get_tooltip(): ?string {
		return $this->tooltip;
	}
}
