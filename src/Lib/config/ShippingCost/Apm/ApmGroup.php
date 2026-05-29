<?php
/**
 * APM shipping cost group.
 *
 * @package Ilabs\Inpost_Pay\Lib\config\ShippingCost\Apm
 */

declare( strict_types=1 );

namespace Ilabs\Inpost_Pay\Lib\config\ShippingCost\Apm;

use Ilabs\Inpost_Pay\Lib\config\ShippingCost\AbstractPriceField;
use Ilabs\Inpost_Pay\Lib\config\ShippingCost\AbstractGroup;
use Ilabs\Inpost_Pay\Lib\config\ShippingCost\AbstractShippingMethodField;
use Ilabs\Inpost_Pay\Lib\config\ShippingCost\GroupInterface;
use Ilabs\Inpost_Pay\Lib\config\ShippingCost\ShippingMappingFieldInterface;
use Ilabs\Inpost_Pay\Lib\form\AbstractOption;
use Ilabs\Inpost_Pay\Lib\form\exception\OptionNameRequired;
use function Ilabs\Inpost_Pay\inpost_pay;

/**
 * Class ApmGroup
 *
 * Shipping cost group for standard APM (parcel machine) delivery.
 */
class ApmGroup extends AbstractGroup {

	/**
	 * Returns the shipping method field for APM.
	 *
	 * @throws OptionNameRequired When the option name is not set.
	 *
	 * @return AbstractShippingMethodField
	 */
	public function get_shipping_method_field(): AbstractShippingMethodField {
		return ApmShippingMethod::instance( $this->get_zone_id() );
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
			$this->get_shipping_method_field(),
		);
	}

	/**
	 * Registers all fields for this group.
	 *
	 * @throws OptionNameRequired When the option name is not set.
	 *
	 * @return void
	 */
	public function register_group(): void {
		$this->get_shipping_method_field()->init();
	}

	/**
	 * Returns the delivery option code.
	 *
	 * @return string
	 */
	public function get_delivery_option_code(): string {
		return GroupInterface::DELIVERY_OPTION_CODE_NONE;
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
	 * Returns the API delivery options map.
	 *
	 * @return array|null
	 */
	public function get_api_delivery_options_map(): ?array {
		return array(
			GroupInterface::DELIVERY_OPTION_CODE_PWW
			=> $this->get_delivery_option_name(),
			GroupInterface::DELIVERY_OPTION_CODE_COD
			=> $this->get_delivery_option_name(),
		);
	}

	/**
	 * Returns option sub-groups for this group.
	 *
	 * @param int|null $zone_id Optional zone ID.
	 *
	 * @return GroupInterface[]|null
	 */
	public function get_option_sub_groups( ?int $zone_id = null ): ?array {
		return array(
			inpost_pay()->shipping_cost_settings( $zone_id )->get_pww_apm_settings_group(),
			inpost_pay()->shipping_cost_settings( $zone_id )->get_cod_apm_settings_group(),
		);
	}

	/**
	 * Returns null — APM group has no availability-from-day field.
	 *
	 * @return AbstractOption|null
	 */
	public function get_available_from_day_field(): ?AbstractOption {
		return null;
	}

	/**
	 * Returns null — APM group has no availability-from-hour field.
	 *
	 * @return AbstractOption|null
	 */
	public function get_available_from_hour_field(): ?AbstractOption {
		return null;
	}

	/**
	 * Returns null — APM group has no availability-to-day field.
	 *
	 * @return AbstractOption|null
	 */
	public function get_available_to_day_field(): ?AbstractOption {
		return null;
	}

	/**
	 * Returns null — APM group has no availability-to-hour field.
	 *
	 * @return AbstractOption|null
	 */
	public function get_available_to_hour_field(): ?AbstractOption {
		return null;
	}

	/**
	 * Returns null — APM group has no price field.
	 *
	 * @return AbstractPriceField|null
	 */
	public function get_price_field(): ?AbstractPriceField {
		return null;
	}

	/**
	 * Returns the option ID for the is-active field.
	 *
	 * @return string
	 */
	protected function get_is_active_field_id(): string {
		if ( $this->get_zone_id() !== null ) {
			return 'izi_group_apm_active_' . $this->get_zone_id();
		}

		return 'izi_group_apm_active';
	}

	/**
	 * Returns the label for the is-active field.
	 *
	 * @return string
	 */
	protected function get_is_active_field_label(): string {
		return __( 'Parcel locker', 'inpost-pay' );
	}
}
