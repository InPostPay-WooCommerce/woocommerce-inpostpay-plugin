<?php
/**
 * PWW APM shipping cost group.
 *
 * @package Ilabs\Inpost_Pay\Lib\config\ShippingCost\PwwApm
 */

declare( strict_types=1 );

namespace Ilabs\Inpost_Pay\Lib\config\ShippingCost\PwwApm;

use Ilabs\Inpost_Pay\Lib\config\ShippingCost\AbstractAvailabilityField;
use Ilabs\Inpost_Pay\Lib\config\ShippingCost\AbstractPriceField;
use Ilabs\Inpost_Pay\Lib\config\ShippingCost\AbstractGroup;
use Ilabs\Inpost_Pay\Lib\config\ShippingCost\AbstractShippingMethodField;
use Ilabs\Inpost_Pay\Lib\config\ShippingCost\AvailabilityGroupInterface;
use Ilabs\Inpost_Pay\Lib\config\ShippingCost\GroupInterface;
use Ilabs\Inpost_Pay\Lib\config\ShippingCost\ShippingMappingFieldInterface;
use Ilabs\Inpost_Pay\Lib\form\exception\OptionNameRequired;

/**
 * Class PwwApmGroup
 *
 * Shipping cost group for APM delivery with Package on Weekend (PWW) option.
 */
class PwwApmGroup extends AbstractGroup implements AvailabilityGroupInterface {

	/**
	 * Returns the available-from day field.
	 *
	 * @throws OptionNameRequired When the option name is not set.
	 *
	 * @return AbstractAvailabilityField
	 */
	public function get_available_from_day_field(): AbstractAvailabilityField {
		return PwwApmAvailableFromDay::instance( $this->get_zone_id() );
	}

	/**
	 * Returns the available-from hour field.
	 *
	 * @throws OptionNameRequired When the option name is not set.
	 *
	 * @return AbstractAvailabilityField
	 */
	public function get_available_from_hour_field(): AbstractAvailabilityField {
		return PwwApmAvailableFromHour::instance( $this->get_zone_id() );
	}

	/**
	 * Returns the available-to day field.
	 *
	 * @throws OptionNameRequired When the option name is not set.
	 *
	 * @return AbstractAvailabilityField
	 */
	public function get_available_to_day_field(): AbstractAvailabilityField {
		return PwwApmAvailableToDay::instance( $this->get_zone_id() );
	}

	/**
	 * Returns the available-to hour field.
	 *
	 * @throws OptionNameRequired When the option name is not set.
	 *
	 * @return AbstractAvailabilityField
	 */
	public function get_available_to_hour_field(): AbstractAvailabilityField {
		return PwwApmAvailableToHour::instance( $this->get_zone_id() );
	}

	/**
	 * Returns the price field for PWW APM.
	 *
	 * @throws OptionNameRequired When the option name is not set.
	 *
	 * @return AbstractPriceField
	 */
	public function get_price_field(): AbstractPriceField {
		return PwwApmPrice::instance( $this->get_zone_id() );
	}

	/**
	 * Returns the shipping method field for PWW APM.
	 *
	 * @throws OptionNameRequired When the option name is not set.
	 *
	 * @return AbstractShippingMethodField
	 */
	public function get_shipping_method_field(): AbstractShippingMethodField {
		return PwwApmShippingMethod::instance( $this->get_zone_id() );
	}

	/**
	 * Registers all fields for this group.
	 *
	 * @throws OptionNameRequired When the option name is not set.
	 *
	 * @return void
	 */
	public function register_group(): void {
		$this->get_available_from_day_field()->init();
		$this->get_available_from_hour_field()->init();
		$this->get_available_to_day_field()->init();
		$this->get_available_to_hour_field()->init();
		$this->get_price_field()->init();
		$this->get_shipping_method_field()->init();
	}

	/**
	 * Returns all fields for this group.
	 *
	 * @throws OptionNameRequired When the option name is not set.
	 *
	 * @return ShippingMappingFieldInterface[]
	 */
	public function get_fields(): array {
		return array(
			$this->get_price_field(),
			$this->get_shipping_method_field(),
			$this->get_available_from_hour_field(),
			$this->get_available_from_day_field(),
			$this->get_available_to_hour_field(),
			$this->get_available_to_day_field(),
		);
	}

	/**
	 * Returns the delivery option code.
	 *
	 * @return string
	 */
	public function get_delivery_option_code(): string {
		return GroupInterface::DELIVERY_OPTION_CODE_PWW;
	}

	/**
	 * Returns the delivery type code.
	 *
	 * @return string
	 */
	public function get_delivery_type_code(): string {
		return GroupInterface::DELIVERY_TYPE_CODE_APM;
	}

	/**
	 * Returns null — PWW APM has no API delivery options map.
	 *
	 * @return array|null
	 */
	public function get_api_delivery_options_map(): ?array {
		return null;
	}

	/**
	 * Returns null — PWW APM has no option sub-groups.
	 *
	 * @param int|null $zone_id Optional zone ID.
	 *
	 * @return GroupInterface[]|null
	 */
	public function get_option_sub_groups( ?int $zone_id = null ): ?array {
		return null;
	}

	/**
	 * Returns the label for the is-active field.
	 *
	 * @return string
	 */
	protected function get_is_active_field_label(): string {
		return __( 'Package on Weekend (PWW) Parcel Locker:', 'inpost-pay' );
	}

	/**
	 * Returns the option ID for the is-active field.
	 *
	 * @return string
	 */
	protected function get_is_active_field_id(): string {
		if ( $this->get_zone_id() !== null ) {
			return 'izi_group_pww_apm_active_' . $this->get_zone_id();
		}

		return 'izi_group_pww_apm_active';
	}

	/**
	 * Returns the tooltip for the is-active field.
	 *
	 * @return string
	 */
	protected function get_is_active_field_tooltip(): string {
		return __( 'Determines if the Package on Weekend (PWW) is active', 'inpost-pay' );
	}

	/**
	 * Returns the option ID for the cost mapping approach field.
	 *
	 * @return string|null
	 */
	public function get_option_cost_mapping_approach_id(): ?string {
		if ( $this->get_zone_id() !== null ) {
			return 'izi_group_pww_apm_opt_mapping_approach_' . $this->get_zone_id();
		}

		return 'izi_group_pww_apm_opt_mapping_approach';
	}
}
