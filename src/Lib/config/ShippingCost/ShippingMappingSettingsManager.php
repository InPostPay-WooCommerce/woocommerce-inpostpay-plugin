<?php
/**
 * Shipping mapping settings manager.
 *
 * @package Ilabs\Inpost_Pay\Lib\config\ShippingCost
 */

declare( strict_types=1 );

namespace Ilabs\Inpost_Pay\Lib\config\ShippingCost;

use Ilabs\Inpost_Pay\Lib\config\ShippingCost\Apm\ApmGroup;
use Ilabs\Inpost_Pay\Lib\config\ShippingCost\CodApm\CodApmGroup;
use Ilabs\Inpost_Pay\Lib\config\ShippingCost\CodCourier\CodCourierGroup;
use Ilabs\Inpost_Pay\Lib\config\ShippingCost\Courier\CourierGroup;
use Ilabs\Inpost_Pay\Lib\config\ShippingCost\PwwApm\PwwApmGroup;
use Ilabs\Inpost_Pay\Lib\helpers\ShippingZoneHelper;


/**
 * Class ShippingMappingSettingsManager
 *
 * Manages all shipping cost configuration groups for a given shipping zone.
 */
class ShippingMappingSettingsManager {

	private ShippingMethodsHelper $shipping_methods_helper;
	private ?int $zone_id = null;

	/**
	 * Registered shipping cost groups.
	 *
	 * @var GroupInterface[]
	 */
	private array $groups;

	/**
	 * Constructor.
	 *
	 * @param int|null $zone_id Optional shipping zone ID.
	 */
	public function __construct( ?int $zone_id = null ) {
		$this->shipping_methods_helper = new ShippingMethodsHelper( $this );

		$this->zone_id = $zone_id;

		$this->groups = array(
			$this->get_apm_settings_group(),
			$this->get_cod_apm_settings_group(),
			$this->get_courier_settings_group(),
			$this->get_cod_courier_settings_group(),
			$this->get_pww_apm_settings_group(),
		);
	}

	/**
	 * Registers all groups and initialises their fields.
	 *
	 * @return void
	 */
	public function register() {
		foreach ( $this->groups as $group ) {
			$group->register_group();
			$group->init_is_active_field();
			$group->init_option_cost_mapping_approach();

		}
		// $this->get_shipping_add_tax_field()->init();
		$this->get_check_shipping_availability_field()->init();
	}

	/**
	 * Finds a group matching the given delivery type and option codes.
	 *
	 * @param string $delivery_type_code Delivery type code.
	 * @param string $option_code        Delivery option code.
	 *
	 * @return GroupInterface|null
	 */
	public function find_group(
		string $delivery_type_code,
		string $option_code = GroupInterface::DELIVERY_OPTION_CODE_NONE
	): ?GroupInterface {

		foreach ( $this->groups as $group ) {
			if ( $option_code !== $group->get_delivery_option_code()
				|| $delivery_type_code !== $group->get_delivery_type_code() ) {
				continue;
			}

			return $group;

		}

		return null;
	}

	/**
	 * Returns courier groups that have additional options.
	 *
	 * @return GroupInterface[]
	 */
	public function find_courier_groups_with_options(): array {
		return array(
			$this->get_cod_courier_settings_group(),
		);
	}

	/**
	 * Returns APM groups that have additional options.
	 *
	 * @return GroupInterface[]
	 */
	public function find_apm_groups_with_options(): array {
		return array(
			$this->get_pww_apm_settings_group(),
			$this->get_cod_apm_settings_group(),
		);
	}

	/**
	 * Returns all fields from APM groups.
	 *
	 * @return ShippingMappingFieldInterface[]
	 */
	public function get_apm_fields() {
		return array_merge(
			$this->get_apm_settings_group()->get_fields(),
			$this->get_pww_apm_settings_group()->get_fields(),
			$this->get_cod_apm_settings_group()->get_fields()
		);
	}

	/**
	 * Returns all fields from courier groups.
	 *
	 * @return ShippingMappingFieldInterface[]
	 */
	public function get_courier_fields() {
		return array_merge(
			$this->get_courier_settings_group()->get_fields(),
			$this->get_cod_courier_settings_group()->get_fields()
		);
	}

	/**
	 * Returns the COD APM settings group.
	 *
	 * @return CodApmGroup
	 */
	public function get_cod_apm_settings_group(): CodApmGroup {
		return new CodApmGroup( $this->zone_id );
	}

	/**
	 * Returns the COD courier settings group.
	 *
	 * @return CodCourierGroup
	 */
	public function get_cod_courier_settings_group(): CodCourierGroup {
		return new CodCourierGroup( $this->zone_id );
	}

	/**
	 * Returns the standard courier settings group.
	 *
	 * @return CourierGroup
	 */
	public function get_courier_settings_group(): CourierGroup {
		return new CourierGroup( $this->zone_id );
	}

	/**
	 * Returns the PWW APM settings group.
	 *
	 * @return PwwApmGroup
	 */
	public function get_pww_apm_settings_group(): PwwApmGroup {
		return new PwwApmGroup( $this->zone_id );
	}

	/**
	 * Returns the standard APM settings group.
	 *
	 * @return ApmGroup
	 */
	public function get_apm_settings_group(): ApmGroup {
		return new ApmGroup( $this->zone_id );
	}

	/**
	 * Returns the check shipping availability field.
	 *
	 * @return CheckShippingAvailability
	 */
	public function get_check_shipping_availability_field(): CheckShippingAvailability {
		return CheckShippingAvailability::instance();
	}

	/**
	 * Returns the shipping methods helper.
	 *
	 * @return ShippingMethodsHelper
	 */
	public function get_shipping_methods_helper(): ShippingMethodsHelper {
		return $this->shipping_methods_helper;
	}

	/**
	 * Checks whether the given group has delivery options.
	 *
	 * @param GroupInterface $settings_group_interface The group to check.
	 *
	 * @return bool
	 */
	public function is_group_with_options(
		GroupInterface $settings_group_interface
	): bool {
		return $settings_group_interface->get_delivery_option_code() !== GroupInterface::DELIVERY_OPTION_CODE_NONE;
	}

	/**
	 * Placeholder static getter.
	 *
	 * @return void
	 */
	public static function get() {
	}
}
