<?php
/**
 * Plugin Settings Collector.
 *
 * @package    Ilabs\Inpost_Pay
 * @subpackage Lib\Utils\Logs
 */

namespace Ilabs\Inpost_Pay\Lib\Utils\Logs;

use Ilabs\Inpost_Pay\Lib\config\analytics\AnalyticsConfig;
use Ilabs\Inpost_Pay\Lib\config\attribution\AttributionConfig;
use Ilabs\Inpost_Pay\Lib\config\attribution\AttributionOverridesConfig;
use Ilabs\Inpost_Pay\Lib\config\widget_v2\WidgetV2SizeConfigInterface;
use Ilabs\Inpost_Pay\Lib\form\exception\NotAllowedConfigOptionException;
use Ilabs\Inpost_Pay\Lib\form\exception\RequiredConfigOptionException;
use Ilabs\Inpost_Pay\Logger;

/**
 * PluginSettingsCollector class.
 */
class PluginSettingsCollector {

	/**
	 * Sensitive configuration keys to mask.
	 *
	 * @var string[]
	 */
	private array $sensitive_keys = array(
		'client_id',
		'client_secret',
		'merchant_id',
		'pos_id',
	);

	/**
	 * Collects all plugin configuration sections.
	 *
	 * @return array
	 */
	public function collect(): array {
		return array(
			'settings'   => $this->collect_settings_section(),
			'consents'   => $this->collect_consents_section(),
			'shipping'   => $this->collect_shipping_section(),
			'appearance' => $this->collect_appearance_section(),
			'marketing'  => $this->collect_marketing_section(),
			'support'    => $this->collect_support_section(),
		);
	}

	/**
	 * Section: Settings (environment, payments, statuses, advanced).
	 *
	 * @return array
	 */
	private function collect_settings_section(): array {
		$settings = array(
			'izi_environment'                    => get_option( 'izi_environment' ),
			'izi_hide_functionality'             => get_option( 'izi_hide_functionality' ),
			'izi_client_id'                      => get_option( 'izi_client_id' ),
			'izi_client_secret'                  => get_option( 'izi_client_secret' ),
			'izi_pos_id'                         => get_option( 'izi_pos_id' ),
			'izi_merchant_id'                    => get_option( 'izi_merchant_id' ),
			'izi_payment_aion'                   => get_option( 'izi_payment_aion' ),
			'izi_payment_inpost'                 => get_option( 'izi_payment_inpost' ),
			'izi_status_authorized'              => get_option( 'izi_status_authorized' ),
			'izi_status_cod_authorized'          => get_option( 'izi_status_cod_authorized' ),
			'izi_status_map'                     => get_option( 'izi_status_map' ),
			'izi_product_desc_map'               => get_option( 'izi_product_desc_map' ),
			'izi_related_count'                  => get_option( 'izi_related_count' ),
			'izi_refresh_after_add_to_cart'      => get_option( 'izi_refresh_after_add_to_cart' ),
			'izi_main_image_only'                => get_option( 'izi_main_image_only' ),
			'izi_custom_basket_response_enabled' => get_option( 'izi_custom_basket_response_enabled' ),
			'izi_custom_response_enabled'        => get_option( 'izi_custom_response_enabled' ),
			'izi_order_hooks_config'             => get_option( 'izi_order_hooks_config' ),
			'izi_cart_hooks_config'              => get_option( 'izi_cart_hooks_config' ),
		);

		foreach ( $settings as $key => &$value ) {
			if ( $this->is_sensitive_key( $key ) ) {
				$value = $this->mask_value( $value );
			}
		}

		return $settings;
	}

	/**
	 * Section: Consents (merchant-defined).
	 *
	 * @return array
	 */
	private function collect_consents_section(): array {
		$consents = get_option( 'izi_consents', array() );
		if ( ! is_array( $consents ) ) {
			$consents = array();
		}

		return $consents;
	}

	/**
	 * Section: Shipping configuration
	 *
	 * @return array
	 */
	private function collect_shipping_section(): array {
		if ( ! function_exists( 'WC' ) ) {
			return array();
		}

		$zones_data = array();
		$zones      = \WC_Shipping_Zones::get_zones();
		$zones[0]   = new \WC_Shipping_Zone( 0 );

		foreach ( $zones as $zone_id => $zone ) {
			if ( $zone instanceof \WC_Shipping_Zone ) {
				$zone_name = $zone->get_zone_name();
			} else {
				$zone_name = $zone['zone_name'] ?? 'Default';
			}

			$settings = inpost_pay()->shipping_cost_settings( (int) $zone_id );

			try {
				$courier         = $settings->getCourierSettingsGroup();
				$courier_enabled = $courier->getIsActiveField()->get_bool();
				$courier_data    = array( 'enabled' => $courier_enabled );

				if ( $courier_enabled ) {
					$courier_cod = $settings->getCodCourierSettingsGroup();

					$courier_data['shipping_method'] = $this->extract_shipping_group_data( $courier, array( 'shipping_method' ) )['shipping_method'];
					$courier_data['cod']             = $this->extract_shipping_group_data( $courier_cod );
				}
			} catch ( \Throwable $e ) {
				$courier_data = array( 'error' => 'Courier section failed: ' . $e->getMessage() );
				Logger::log( '[InPost Pay Collector] Courier section error in zone ' . $zone_id . ': ' . $e->getMessage() );
			}

			try {
				$apm         = $settings->getApmSettingsGroup();
				$apm_enabled = $apm->getIsActiveField()->get_bool();
				$apm_data    = array( 'enabled' => $apm_enabled );

				if ( $apm_enabled ) {
					$apm_cod = $settings->getCodApmSettingsGroup();
					$apm_pww = $settings->getPwwApmSettingsGroup();

					$apm_data['shipping_method'] = $this->extract_shipping_group_data( $apm, array( 'shipping_method' ) )['shipping_method'];
					$apm_data['cod']             = $this->extract_shipping_group_data( $apm_cod );
					$apm_data['pww']             = $this->extract_shipping_group_data( $apm_pww );

					try {
						$apm_data['pww']['available_from'] = array(
							'day'  => $apm_pww->getAvailableFromDayField() ? $apm_pww->getAvailableFromDayField()->get() : null,
							'hour' => $apm_pww->getAvailableFromHourField() ? $apm_pww->getAvailableFromHourField()->get() : null,
						);
						$apm_data['pww']['available_to']   = array(
							'day'  => $apm_pww->getAvailableToDayField() ? $apm_pww->getAvailableToDayField()->get() : null,
							'hour' => $apm_pww->getAvailableToHourField() ? $apm_pww->getAvailableToHourField()->get() : null,
						);
					} catch ( \Throwable $e ) {
						Logger::log( '[InPost Pay Collector] PWW availability extraction error in zone ' . $zone_id . ': ' . $e->getMessage() );
					}
				}
			} catch ( \Throwable $e ) {
				$apm_data = array( 'error' => 'APM section failed: ' . $e->getMessage() );
				Logger::log( '[InPost Pay Collector] APM section error in zone ' . $zone_id . ': ' . $e->getMessage() );
			}

			$zones_data[ $zone_id ] = array(
				'zone_name' => $zone_name,
				'courier'   => $courier_data,
				'apm'       => $apm_data,
			);
		}

		try {
			$global_settings             = inpost_pay()->shipping_cost_settings();
			$global_availability_field   = $global_settings->getCheckShippingAvailabilityField();
			$check_shipping_availability = $global_availability_field->get_bool();
		} catch ( \Throwable $e ) {
			$check_shipping_availability = false;
			Logger::log( '[InPost Pay Collector] Global availability field error: ' . $e->getMessage() );
		}

		return array(
			'zones'      => $zones_data,
			'additional' => array(
				'check_shipping_availability' => $check_shipping_availability,
			),
		);
	}

	/**
	 * Helper for extracting common shipping group data.
	 *
	 * @param mixed $group Shipping group object.
	 * @param array $include_fields List of fields to include.
	 *
	 * @return array
	 */
	private function extract_shipping_group_data(
		$group,
		array $include_fields = array(
			'enabled',
			'mapping_type',
			'shipping_method',
			'additional_fee',
		)
	): array {
		if ( ! $group ) {
			return array();
		}

		$data = array();

		try {
			if ( in_array( 'enabled', $include_fields, true ) ) {
				$field           = $group->getIsActiveField();
				$data['enabled'] = $field && $field->get_bool();
			}

			if ( in_array( 'mapping_type', $include_fields, true ) ) {
				$obj                  = $group->getOptionCostMappingApproachObj();
				$data['mapping_type'] = $obj ? $obj->get() : null;
			}

			if ( in_array( 'shipping_method', $include_fields, true ) ) {
				$field                   = $group->getShippingMethodField();
				$data['shipping_method'] = $field ? $field->get() : null;
			}

			if ( in_array( 'additional_fee', $include_fields, true ) ) {
				$field                  = $group->getPriceField();
				$data['additional_fee'] = $field ? $field->get() : null;
			}
		} catch ( \Throwable $e ) {
			Logger::log( '[InPost Pay Collector] extractShippingGroupData error: ' . $e->getMessage() );
		}

		return $data;
	}


	/**
	 * Section: Widget appearance and placement
	 *
	 * @return array
	 */
	private function collect_appearance_section(): array {
		return array(
			'button_style' => array(
				'background' => get_option( 'izi_background' ),
				'variant'    => get_option( 'izi_variant' ),
				'frame'      => get_option( 'izi_frame_style' ),
				'size'       => get_option( WidgetV2SizeConfigInterface::IZI_WIDGET_V2_SIZE ),
			),

			'cart'         => array(
				'enabled'   => (bool) get_option( 'izi_show_basket' ),
				'placement' => get_option( 'izi_place_basket' ),
				'alignment' => get_option( 'izi_align_basket' ),
			),

			'order'        => array(
				'enabled'   => (bool) get_option( 'izi_show_order' ),
				'placement' => get_option( 'izi_place_order' ),
				'alignment' => get_option( 'izi_align_order' ),
			),

			'checkout'     => array(
				'enabled'   => (bool) get_option( 'izi_show_checkout' ),
				'placement' => get_option( 'izi_place_checkout' ),
				'alignment' => get_option( 'izi_align_checkout' ),
			),

			'login_page'   => array(
				'enabled'   => (bool) get_option( 'izi_show_login_page' ),
				'placement' => get_option( 'izi_place_login_page' ),
				'alignment' => get_option( 'izi_align_login_page' ),
			),

			'minicart'     => array(
				'enabled'   => (bool) get_option( 'izi_show_minicart' ),
				'placement' => get_option( 'izi_place_minicart' ),
				'alignment' => get_option( 'izi_align_minicart' ),
			),

			'product_card' => array(
				'enabled'   => (bool) get_option( 'izi_show_details' ),
				'placement' => get_option( 'izi_place_details' ),
				'alignment' => get_option( 'izi_align_details' ),
			),
		);
	}


	/**
	 * Section: Marketing integrations.
	 *
	 * @return array
	 */
	private function collect_marketing_section(): array {
		$attribution_config         = new AttributionConfig();
		$attribution_overrides_conf = new AttributionOverridesConfig();
		$analytics_config           = new AnalyticsConfig();

		try {
			$attribution_enabled         = $attribution_config->get_form_field()->get_bool();
			$attribution_overrides_state = $attribution_overrides_conf->get_form_field()->get_bool();
			$analytics_enabled           = $analytics_config->get_form_field()->get_bool();
		} catch ( NotAllowedConfigOptionException | RequiredConfigOptionException $e ) {
			return array();
		}

		return array(
			'attribution' => array(
				'enabled'   => $attribution_enabled,
				'overrides' => $attribution_overrides_state,
			),
			'analytics'   => array(
				'enabled' => $analytics_enabled,
			),
		);
	}


	/**
	 * Section: Support/debug configuration
	 *
	 * @return array
	 */
	private function collect_support_section(): array {
		return array(
			'debug_mode' => get_option( 'izi_debug', false ),
		);
	}

	/**
	 * Checks if the given key is sensitive.
	 *
	 * @param string $key Configuration key.
	 *
	 * @return bool
	 */
	private function is_sensitive_key( string $key ): bool {
		foreach ( $this->sensitive_keys as $sensitive ) {
			if ( str_contains( strtolower( $key ), $sensitive ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Masks sensitive value.
	 *
	 * @param mixed $value Configuration value.
	 *
	 * @return string
	 */
	private function mask_value( $value ): string {
		if ( ! is_string( $value ) || '' === $value ) {
			return '***masked***';
		}

		$len = strlen( $value );
		if ( $len <= 7 ) {
			return '***masked***';
		}

		return substr( $value, 0, 4 ) . str_repeat( '*', $len - 7 ) . substr( $value, - 3 );
	}
}
