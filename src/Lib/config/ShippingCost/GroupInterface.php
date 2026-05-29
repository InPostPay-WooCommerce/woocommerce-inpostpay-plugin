<?php
/**
 * Shipping cost group interface.
 *
 * @package Ilabs\Inpost_Pay\Lib\config\ShippingCost
 */

declare( strict_types=1 );

namespace Ilabs\Inpost_Pay\Lib\config\ShippingCost;

use Ilabs\Inpost_Pay\Lib\form\AbstractOption;

/**
 * Interface GroupInterface
 *
 * Defines the contract for a shipping cost configuration group.
 */
interface GroupInterface {

	const DELIVERY_OPTION_CODE_PWW = 'PWW';

	const DELIVERY_OPTION_CODE_COD = 'COD';

	const DELIVERY_OPTION_CODE_PWW_COD = 'PWW_COD';

	const DELIVERY_OPTION_CODE_NONE = 'NONE';




	const DELIVERY_TYPE_CODE_APM = 'APM';

	const DELIVERY_TYPE_CODE_COURIER = 'COURIER';


	/**
	 * Registers the group settings.
	 *
	 * @return void
	 */
	public function register_group(): void;

	/**
	 * Initialises the is-active field for this group.
	 *
	 * @return void
	 */
	public function init_is_active_field(): void;

	/**
	 * Initialises the option cost mapping approach field.
	 *
	 * @return void
	 */
	public function init_option_cost_mapping_approach(): void;

	/**
	 * Returns all fields for this group.
	 *
	 * @return ShippingMappingFieldInterface[]
	 */
	public function get_fields(): array;

	/**
	 * Returns the delivery option code for this group.
	 *
	 * @return string
	 */
	public function get_delivery_option_code(): string;

	/**
	 * Returns the delivery type code (e.g. APM, COURIER).
	 *
	 * @return string
	 */
	public function get_delivery_type_code(): string;

	/**
	 * Returns the human-readable delivery option name.
	 *
	 * @return string|null
	 */
	public function get_delivery_option_name(): ?string;

	/**
	 * Returns the API delivery options map.
	 *
	 * @return array|null
	 */
	public function get_api_delivery_options_map(): ?array;

	/**
	 * Returns the available-from day field.
	 *
	 * @return AbstractOption|null
	 */
	public function get_available_from_day_field(): ?AbstractOption;

	/**
	 * Returns the available-from hour field.
	 *
	 * @return AbstractOption|null
	 */
	public function get_available_from_hour_field(): ?AbstractOption;

	/**
	 * Returns the available-to day field.
	 *
	 * @return AbstractOption|null
	 */
	public function get_available_to_day_field(): ?AbstractOption;

	/**
	 * Returns the available-to hour field.
	 *
	 * @return AbstractOption|null
	 */
	public function get_available_to_hour_field(): ?AbstractOption;

	/**
	 * Returns the price field for this group.
	 *
	 * @return AbstractPriceField|null
	 */
	public function get_price_field(): ?AbstractPriceField;

	/**
	 * Returns the shipping method field for this group.
	 *
	 * @return AbstractShippingMethodField|null
	 */
	public function get_shipping_method_field(): ?AbstractShippingMethodField;

	/**
	 * Returns the is-active boolean field.
	 *
	 * @return BoolField
	 */
	public function get_is_active_field(): BoolField;

	/**
	 * Returns the selected option cost mapping approach value.
	 *
	 * @return string
	 */
	public function get_option_cost_mapping_approach(): string;

	/**
	 * Returns the unique group identifier.
	 *
	 * @return string
	 */
	public function get_group_id(): string;

	/**
	 * Returns option sub-groups for this group.
	 *
	 * @param int|null $zone_id Optional zone ID.
	 *
	 * @return GroupInterface[]|null
	 */
	public function get_option_sub_groups( ?int $zone_id = null ): ?array;
}
