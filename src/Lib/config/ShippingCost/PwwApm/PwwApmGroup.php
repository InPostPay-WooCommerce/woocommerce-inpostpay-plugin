<?php

namespace Ilabs\Inpost_Pay\Lib\config\ShippingCost\PwwApm;

use Ilabs\Inpost_Pay\Lib\config\ShippingCost\AbstractAvailabilityField;
use Ilabs\Inpost_Pay\Lib\config\ShippingCost\AbstractPriceField;
use Ilabs\Inpost_Pay\Lib\config\ShippingCost\AbstractGroup;
use Ilabs\Inpost_Pay\Lib\config\ShippingCost\AbstractShippingMethodField;
use Ilabs\Inpost_Pay\Lib\config\ShippingCost\AvailabilityGroupInterface;
use Ilabs\Inpost_Pay\Lib\config\ShippingCost\GroupInterface;
use Ilabs\Inpost_Pay\Lib\config\ShippingCost\ShippingMappingFieldInterface;
use Ilabs\Inpost_Pay\Lib\form\exception\OptionNameRequired;

class PwwApmGroup extends AbstractGroup implements AvailabilityGroupInterface {

	/**
	 * @throws OptionNameRequired
	 */
	public function getAvailableFromDayField(): AbstractAvailabilityField {
		return PwwApmAvailableFromDay::instance( $this->get_zone_id() );
	}

	/**
	 * @throws OptionNameRequired
	 */
	public function getAvailableFromHourField(): AbstractAvailabilityField {
		return PwwApmAvailableFromHour::instance( $this->get_zone_id() );
	}

	/**
	 * @throws OptionNameRequired
	 */
	public function getAvailableToDayField(): AbstractAvailabilityField {
		return PwwApmAvailableToDay::instance( $this->get_zone_id() );
	}

	/**
	 * @throws OptionNameRequired
	 */
	public function getAvailableToHourField(): AbstractAvailabilityField {
		return PwwApmAvailableToHour::instance( $this->get_zone_id() );
	}

	/**
	 * @throws OptionNameRequired
	 */
	public function getPriceField(): AbstractPriceField {
		return PwwApmPrice::instance( $this->get_zone_id() );
	}

	/**
	 * @throws OptionNameRequired
	 */
	public function getShippingMethodField(): AbstractShippingMethodField {
		return PwwApmShippingMethod::instance( $this->get_zone_id() );
	}

	/**
	 * @throws OptionNameRequired
	 */
	public function registerGroup(): void {
		$this->getAvailableFromDayField()->init();
		$this->getAvailableFromHourField()->init();
		$this->getAvailableToDayField()->init();
		$this->getAvailableToHourField()->init();
		$this->getPriceField()->init();
		$this->getShippingMethodField()->init();
	}

	/**
	 * @return ShippingMappingFieldInterface[]
	 * @throws OptionNameRequired
	 */
	public function getFields(): array {
		return array(
			$this->getPriceField(),
			$this->getShippingMethodField(),
			$this->getAvailableFromHourField(),
			$this->getAvailableFromDayField(),
			$this->getAvailableToHourField(),
			$this->getAvailableToDayField(),
		);
	}

	public function getDeliveryOptionCode(): string {
		return GroupInterface::DELIVERY_OPTION_CODE_PWW;
	}

	public function getDeliveryTypeCode(): string {
		return GroupInterface::DELIVERY_TYPE_CODE_APM;
	}

	public function getApiDeliveryOptionsMap(): ?array {
		return null;
	}

	public function getOptionSubGroups( ?int $zone_id = null ): ?array {
		return null;
	}

	protected function getIsActiveFieldLabel(): string {
		return __( 'Package on Weekend (PWW) Parcel Locker:', 'inpost-pay' );
	}

	public function getIsActiveFieldId(): string {
		if ( $this->get_zone_id() !== null ) {
			return 'izi_group_pww_apm_active_' . $this->get_zone_id();
		}

		return 'izi_group_pww_apm_active';
	}

	protected function getIsActiveFieldTooltip(): string {
		return __( 'Determines if the Package on Weekend (PWW) is active', 'inpost-pay' );
	}

	public function getOptionCostMappingApproachId(): ?string {
		if ( $this->get_zone_id() !== null ) {
			return 'izi_group_pww_apm_opt_mapping_approach_' . $this->get_zone_id();
		}

		return 'izi_group_pww_apm_opt_mapping_approach';
	}
}
