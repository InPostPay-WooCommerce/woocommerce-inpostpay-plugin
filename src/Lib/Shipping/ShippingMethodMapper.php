<?php
/**
 * Maps WooCommerce shipping methods to InPost delivery types.
 *
 * @package Ilabs\Inpost_Pay\Lib\Shipping
 */

declare( strict_types=1 );

namespace Ilabs\Inpost_Pay\Lib\Shipping;

use Ilabs\Inpost_Pay\Lib\config\ShippingCost\Apm\ApmGroup;
use Ilabs\Inpost_Pay\Lib\config\ShippingCost\Courier\CourierGroup;
use Ilabs\Inpost_Pay\Lib\config\ShippingCost\GroupInterface;
use Ilabs\Inpost_Pay\Lib\config\ShippingCost\OptionCostMappingApproach;
use Ilabs\Inpost_Pay\Lib\config\ShippingCost\ShippingMappingSettingsManager;

/**
 * Class ShippingMethodMapper
 *
 * Resolves which WooCommerce shipping methods map to InPost delivery types
 * (APM / Courier) based on the plugin's shipping cost configuration.
 *
 * @package Ilabs\Inpost_Pay\Lib\Shipping
 */
class ShippingMethodMapper {

	/**
	 * Manager for shipping mapping settings.
	 *
	 * @var ShippingMappingSettingsManager|null
	 */
	private static ?ShippingMappingSettingsManager $manager = null;

	/**
	 * Last resolved method code per delivery type, populated during mapping.
	 *
	 * @var array
	 */
	private static array $mapped_methods = array(
		GroupInterface::DELIVERY_TYPE_CODE_APM     => null,
		GroupInterface::DELIVERY_TYPE_CODE_COURIER => null,
	);

	/**
	 * Cache for resolved InPost method strings keyed by delivery type.
	 *
	 * @var array|null
	 */
	private static ?array $inpost_methods_cache = null;

	/**
	 * Initializes the mapper with a shipping settings manager instance.
	 *
	 * Must be called before any mapping methods are used.
	 *
	 * @param ShippingMappingSettingsManager $mgr Settings manager.
	 *
	 * @return void
	 */
	public static function init( ShippingMappingSettingsManager $mgr ): void {
		self::$manager = $mgr;
	}

	/**
	 * Gets all mapped WooCommerce shipping method IDs for courier and APM types.
	 *
	 * @return array Two-element array: [ mapped courier methods, mapped APM methods ].
	 */
	public static function get_mapped_shipping_methods(): array {
		$mgr = self::$manager;

		$courier_settings_group = $mgr->getCourierSettingsGroup();
		$apm_settings_group     = $mgr->getApmSettingsGroup();

		$mapped_courier_methods = $courier_settings_group->getIsActiveField()->get_bool()
			? self::get_courier_mapped_shipping_methods( $courier_settings_group )
			: array();

		$mapped_apm_methods = $apm_settings_group->getIsActiveField()->get_bool()
			? self::get_apm_mapped_shipping_methods( $apm_settings_group )
			: array();

		return array( $mapped_courier_methods, $mapped_apm_methods );
	}

	/**
	 * Returns the last resolved method code per delivery type after mapping.
	 *
	 * Populated as a side-effect of get_mapped_shipping_methods().
	 *
	 * @return array Map of delivery type code to method string or null.
	 */
	public static function get_mapped_methods(): array {
		return self::$mapped_methods;
	}

	/**
	 * Gets the raw configured InPost shipping method strings by delivery type.
	 *
	 * Result is cached for the duration of the request.
	 *
	 * @return array InPost shipping methods keyed by delivery type code.
	 */
	public static function get_inpost_methods(): array {
		if ( null !== self::$inpost_methods_cache ) {
			return self::$inpost_methods_cache;
		}

		$mgr = self::$manager;

		self::$inpost_methods_cache = array(
			GroupInterface::DELIVERY_TYPE_CODE_APM     => $mgr->getApmSettingsGroup()->getShippingMethodField()->get(),
			GroupInterface::DELIVERY_TYPE_CODE_COURIER => $mgr->getCourierSettingsGroup()->getShippingMethodField()->get(),
		);

		return self::$inpost_methods_cache;
	}

	/**
	 * Gets the InPost delivery type code for a given WooCommerce shipping method ID.
	 *
	 * Matches the base method ID (without instance suffix) against configured groups.
	 *
	 * @param string $method_id WooCommerce method ID (e.g. 'easypack_parcel_machines:6').
	 *
	 * @return string|null Delivery type code or null if not matched.
	 */
	public static function get_delivery_type_from_method( string $method_id ): ?string {
		$mgr = self::$manager;

		$groups = array(
			$mgr->getApmSettingsGroup(),
			$mgr->getCourierSettingsGroup(),
		);

		$base_method = explode( ':', $method_id )[0] ?? $method_id;

		foreach ( $groups as $group ) {
			if ( empty( $group ) || ! $group->getIsActiveField()->get_bool() ) {
				continue;
			}
			$configured_methods = array_filter( array_map( 'trim', explode( ',', $group->getShippingMethodField()->get() ) ) );
			foreach ( $configured_methods as $configured_method ) {
				$configured_base = explode( ':', $configured_method )[0] ?? $configured_method;
				if ( $base_method === $configured_base ) {
					return $group->getDeliveryTypeCode();
				}
			}
		}

		return null;
	}

	/**
	 * Gets mapped WooCommerce shipping method IDs for the courier delivery type.
	 *
	 * Merges base courier methods with COD courier methods when applicable.
	 *
	 * @param CourierGroup $courier_settings_group Courier settings group.
	 *
	 * @return array Mapped courier shipping method IDs.
	 */
	private static function get_courier_mapped_shipping_methods( CourierGroup $courier_settings_group ): array {
		$mgr = self::$manager;

		$cod_courier_settings_group = $mgr->getCodCourierSettingsGroup();
		$mapped_courier_methods     = array_filter( array_map( 'trim', explode( ',', $courier_settings_group->getShippingMethodField()->get() ) ) );
		$mapped_cod_courier_methods = array_filter( array_map( 'trim', explode( ',', $cod_courier_settings_group->getShippingMethodField()->get() ) ) );

		$option_cost_mapping_approach        = $cod_courier_settings_group->getOptionCostMappingApproach();
		$is_option_cost_mapping_approach_fee = ( OptionCostMappingApproach::OPTION_COST_MAPPING_APPROACH_FEE === $option_cost_mapping_approach );

		if ( ! $is_option_cost_mapping_approach_fee && $cod_courier_settings_group->getIsActiveField()->get_bool() ) {
			$mapped_courier_methods = array_unique( array_merge( $mapped_courier_methods, $mapped_cod_courier_methods ) );
			if ( ! empty( $mapped_courier_methods ) ) {
				self::$mapped_methods[ GroupInterface::DELIVERY_TYPE_CODE_COURIER ] = end( $mapped_courier_methods );
			}
		}

		return $mapped_courier_methods;
	}

	/**
	 * Gets mapped WooCommerce shipping method IDs for the APM (parcel locker) delivery type.
	 *
	 * Merges base APM methods with COD and PWW APM variants when applicable.
	 *
	 * @param ApmGroup $apm_settings_group APM settings group.
	 *
	 * @return array Mapped APM shipping method IDs.
	 */
	private static function get_apm_mapped_shipping_methods( ApmGroup $apm_settings_group ): array {
		$mgr = self::$manager;

		$cod_apm_settings_group = $mgr->getCodApmSettingsGroup();
		$pww_apm_settings_group = $mgr->getPwwApmSettingsGroup();

		$mapped_apm_methods     = array_filter(
			array_map( 'trim', explode( ',', $apm_settings_group->getShippingMethodField()->get() ) )
		);
		$mapped_cod_apm_methods = array_filter(
			array_map( 'trim', explode( ',', $cod_apm_settings_group->getShippingMethodField()->get() ) )
		);
		$mapped_pww_apm_methods = array_filter(
			array_map( 'trim', explode( ',', $pww_apm_settings_group->getShippingMethodField()->get() ) )
		);

		$option_cost_mapping_approach        = $cod_apm_settings_group->getOptionCostMappingApproach();
		$is_option_cost_mapping_approach_fee = (
			OptionCostMappingApproach::OPTION_COST_MAPPING_APPROACH_SHIPPING_METHOD === $option_cost_mapping_approach
		);

		if ( ! empty( $mapped_apm_methods ) ) {
			self::$mapped_methods[ GroupInterface::DELIVERY_TYPE_CODE_APM ] = end( $mapped_apm_methods );
		}

		if ( ! $is_option_cost_mapping_approach_fee && $cod_apm_settings_group->getIsActiveField()->get_bool() ) {
			$mapped_apm_methods = array_merge( $mapped_apm_methods, $mapped_cod_apm_methods );
			if ( ! empty( $mapped_apm_methods ) ) {
				self::$mapped_methods[ GroupInterface::DELIVERY_TYPE_CODE_APM ] = end( $mapped_apm_methods );
			}
		}

		$option_cost_mapping_approach        = $pww_apm_settings_group->getOptionCostMappingApproach();
		$is_option_cost_mapping_approach_fee = ( OptionCostMappingApproach::OPTION_COST_MAPPING_APPROACH_SHIPPING_METHOD === $option_cost_mapping_approach );

		if ( ! $is_option_cost_mapping_approach_fee && $pww_apm_settings_group->getIsActiveField()->get_bool() ) {
			$mapped_apm_methods = array_merge( $mapped_apm_methods, $mapped_pww_apm_methods );
			if ( ! empty( $mapped_apm_methods ) ) {
				self::$mapped_methods[ GroupInterface::DELIVERY_TYPE_CODE_APM ] = end( $mapped_apm_methods );
			}
		}

		return $mapped_apm_methods;
	}
}
