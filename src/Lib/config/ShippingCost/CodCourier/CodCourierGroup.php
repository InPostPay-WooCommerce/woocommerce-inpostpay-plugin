<?php
/**
 * COD courier shipping cost group.
 *
 * @package Ilabs\Inpost_Pay\Lib\config\ShippingCost\CodCourier
 */

declare( strict_types=1 );

namespace Ilabs\Inpost_Pay\Lib\config\ShippingCost\CodCourier;

use Ilabs\Inpost_Pay\Lib\config\ShippingCost\AbstractPriceField;
use Ilabs\Inpost_Pay\Lib\config\ShippingCost\AbstractGroup;
use Ilabs\Inpost_Pay\Lib\config\ShippingCost\AbstractShippingMethodField;
use Ilabs\Inpost_Pay\Lib\config\ShippingCost\GroupInterface;
use Ilabs\Inpost_Pay\Lib\config\ShippingCost\ShippingMappingFieldInterface;
use Ilabs\Inpost_Pay\Lib\form\AbstractOption;
use Ilabs\Inpost_Pay\Lib\form\exception\OptionNameRequired;

/**
 * Class CodCourierGroup
 *
 * Shipping cost group for courier delivery with Cash on Delivery (COD) option.
 */
class CodCourierGroup extends AbstractGroup {

	/**
	 * Returns the price field for COD courier.
	 *
	 * @throws OptionNameRequired When the option name is not set.
	 *
	 * @return AbstractPriceField
	 */
	public function get_price_field(): AbstractPriceField {
		return CodCourierPrice::instance( $this->get_zone_id() );
	}

	/**
	 * Returns the shipping method field for COD courier.
	 *
	 * @throws OptionNameRequired When the option name is not set.
	 *
	 * @return AbstractShippingMethodField
	 */
	public function get_shipping_method_field(): AbstractShippingMethodField {
		return CodCourierShippingMethod::instance( $this->get_zone_id() );
	}

	/**
	 * Registers all fields for this group.
	 *
	 * @throws OptionNameRequired When the option name is not set.
	 *
	 * @return void
	 */
	public function register_group(): void {
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
		);
	}

	/**
	 * Returns the delivery option code.
	 *
	 * @return string
	 */
	public function get_delivery_option_code(): string {
		return GroupInterface::DELIVERY_OPTION_CODE_COD;
	}

	/**
	 * Returns the delivery type code.
	 *
	 * @return string
	 */
	public function get_delivery_type_code(): string {
		return GroupInterface::DELIVERY_TYPE_CODE_COURIER;
	}

	/**
	 * Returns null — COD courier has no API delivery options map.
	 *
	 * @return array|null
	 */
	public function get_api_delivery_options_map(): ?array {
		return null;
	}

	/**
	 * Returns null — COD courier group has no availability-from-day field.
	 *
	 * @return AbstractOption|null
	 */
	public function get_available_from_day_field(): ?AbstractOption {
		return null;
	}

	/**
	 * Returns null — COD courier group has no availability-from-hour field.
	 *
	 * @return AbstractOption|null
	 */
	public function get_available_from_hour_field(): ?AbstractOption {
		return null;
	}

	/**
	 * Returns null — COD courier group has no availability-to-day field.
	 *
	 * @return AbstractOption|null
	 */
	public function get_available_to_day_field(): ?AbstractOption {
		return null;
	}

	/**
	 * Returns null — COD courier group has no availability-to-hour field.
	 *
	 * @return AbstractOption|null
	 */
	public function get_available_to_hour_field(): ?AbstractOption {
		return null;
	}

	/**
	 * Returns null — COD courier has no option sub-groups.
	 *
	 * @param int|null $zone_id Optional zone ID.
	 *
	 * @return GroupInterface[]|null
	 */
	public function get_option_sub_groups( ?int $zone_id = null ): ?array {
		return null;
	}

	/**
	 * Returns the option ID for the is-active field.
	 *
	 * @return string
	 */
	protected function get_is_active_field_id(): string {
		if ( $this->get_zone_id() !== null ) {
			return 'izi_group_cod_courier_active_' . $this->get_zone_id();
		}

		return 'izi_group_cod_courier_active';
	}

	/**
	 * Returns the label for the is-active field.
	 *
	 * @return string
	 */
	protected function get_is_active_field_label(): string {
		return __( 'Cash on Delivery (COD) Courier:', 'inpost-pay' );
	}

	/**
	 * Returns the tooltip for the is-active field.
	 *
	 * @return string
	 */
	protected function get_is_active_field_tooltip(): string {
		return __(
			'Determines if the Cash on Delivery (COD) is active',
			'inpost-pay'
		);
	}

	/**
	 * Returns the option ID for the cost mapping approach field.
	 *
	 * @return string|null
	 */
	public function get_option_cost_mapping_approach_id(): ?string {
		if ( $this->get_zone_id() !== null ) {
			return 'izi_group_cod_courier_opt_mapping_approach_' . $this->get_zone_id();
		}

		return 'izi_group_cod_courier_opt_mapping_approach';
	}
}
