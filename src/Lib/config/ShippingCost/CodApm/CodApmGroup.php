<?php

namespace Ilabs\Inpost_Pay\Lib\config\ShippingCost\CodApm;

use Ilabs\Inpost_Pay\Lib\config\ShippingCost\AbstractPriceField;
use Ilabs\Inpost_Pay\Lib\config\ShippingCost\AbstractGroup;
use Ilabs\Inpost_Pay\Lib\config\ShippingCost\AbstractShippingMethodField;
use Ilabs\Inpost_Pay\Lib\config\ShippingCost\GroupInterface;
use Ilabs\Inpost_Pay\Lib\config\ShippingCost\ShippingMappingFieldInterface;
use Ilabs\Inpost_Pay\Lib\form\AbstractOption;
use Ilabs\Inpost_Pay\Lib\form\exception\OptionNameRequired;

class CodApmGroup extends AbstractGroup {

	/**
	 * @throws OptionNameRequired
	 */
	public function getPriceField(): AbstractPriceField {
		return CodApmPrice::instance( $this->get_zone_id() );
	}

	/**
	 * @throws OptionNameRequired
	 */
	public function getShippingMethodField(): AbstractShippingMethodField {
		return CodApmShippingMethod::instance( $this->get_zone_id() );
	}

	/**
	 * @throws OptionNameRequired
	 */
	public function registerGroup(): void {
		$this->getPriceField()->init();
		$this->getShippingMethodField()->init();
	}

	/**
	 * @return ShippingMappingFieldInterface[]
	 * @throws OptionNameRequired
	 */
	public function getFields(): array {
		return [
			$this->getPriceField(),
			$this->getShippingMethodField(),
		];
	}

	public function getDeliveryOptionCode(): string {
		return GroupInterface::DELIVERY_OPTION_CODE_COD;
	}

	public function getDeliveryTypeCode(): string {
		return GroupInterface::DELIVERY_TYPE_CODE_APM;
	}

	public function getApiDeliveryOptionsMap(): ?array {
		return null;
	}

	public function getAvailableFromDayField(): ?AbstractOption {
		return null;
	}

	public function getAvailableFromHourField(): ?AbstractOption {
		return null;
	}

	public function getAvailableToDayField(): ?AbstractOption {
		return null;
	}

	public function getAvailableToHourField(): ?AbstractOption {
		return null;
	}

	public function getOptionSubGroups( ?int $zone_id = null ): ?array {
		return null;
	}

	protected function getIsActiveFieldLabel(): string {
		return __( 'Cash on Delivery (COD) Parcel Machine', 'inpost-pay' );
	}

	protected function getIsActiveFieldTooltip(): string {
		return __( 'Determines if the Cash on Delivery (COD) is active', 'inpost-pay' );
	}

	public function getIsActiveFieldId(): string {
		if ($this->get_zone_id() !== null) {
			return 'izi_group_cod_apm_active_' . $this->get_zone_id();
		}

		return 'izi_group_cod_apm_active';
	}

	public function getOptionCostMappingApproachId(): ?string {
		if ($this->get_zone_id() !== null) {
			return 'izi_group_cod_apm_opt_mapping_approach_' . $this->get_zone_id();
		}

		return 'izi_group_cod_apm_opt_mapping_approach';
	}
}
