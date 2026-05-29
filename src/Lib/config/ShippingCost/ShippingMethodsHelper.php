<?php
/**
 * Shipping methods helper.
 *
 * @package Ilabs\Inpost_Pay\Lib\config\ShippingCost
 */

declare( strict_types=1 );

namespace Ilabs\Inpost_Pay\Lib\config\ShippingCost;

/**
 * Class ShippingMethodsHelper
 *
 * Provides helpers for retrieving and resolving configured shipping methods.
 */
class ShippingMethodsHelper {

	private ShippingMappingSettingsManager $shipping_cost_options;


	/**
	 * Constructor.
	 *
	 * @param ShippingMappingSettingsManager $shipping_cost_options The settings manager.
	 */
	public function __construct(
		ShippingMappingSettingsManager $shipping_cost_options
	) {
		$this->shipping_cost_options = $shipping_cost_options;
	}

	/**
	 * Returns all configured (non-empty) shipping method values.
	 *
	 * @return array
	 */
	public function get_configured_shipping_methods(): array {
		$return = array();
		foreach ( $this->get_shipping_method_fields() as $field ) {
			$val = $field->get();
			if ( ! empty( $val ) ) {
				$return[] = $val;
			}
		}

		return $return;
	}

	/**
	 * Returns configured shipping methods with the instance suffix stripped.
	 *
	 * @return array
	 */
	public function get_configured_shipping_methods_exploded(): array {
		$return = array();
		foreach ( $this->get_configured_shipping_methods() as $value ) {
			$return[] = explode( ':', esc_attr( $value ) )[0];
		}

		return $return;
	}

	/**
	 * Returns all shipping method fields from the configured groups.
	 *
	 * @return AbstractShippingMethodField[]
	 */
	public function get_shipping_method_fields(): array {
		return array(
			$this->shipping_cost_options->get_apm_settings_group()
										->get_shipping_method_field(),
			$this->shipping_cost_options->get_cod_apm_settings_group()
										->get_shipping_method_field(),
			$this->shipping_cost_options->get_pww_apm_settings_group()
										->get_shipping_method_field(),
			$this->shipping_cost_options->get_courier_settings_group()
										->get_shipping_method_field(),
			$this->shipping_cost_options->get_cod_courier_settings_group()
										->get_shipping_method_field(),
		);
	}
}
