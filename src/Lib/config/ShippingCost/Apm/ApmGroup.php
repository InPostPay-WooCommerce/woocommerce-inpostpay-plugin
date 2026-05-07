<?php

namespace Ilabs\Inpost_Pay\Lib\config\ShippingCost\Apm;

use Ilabs\Inpost_Pay\Lib\config\ShippingCost\AbstractPriceField;
use Ilabs\Inpost_Pay\Lib\config\ShippingCost\AbstractGroup;
use Ilabs\Inpost_Pay\Lib\config\ShippingCost\AbstractShippingMethodField;
use Ilabs\Inpost_Pay\Lib\config\ShippingCost\GroupInterface;
use Ilabs\Inpost_Pay\Lib\config\ShippingCost\ShippingMappingFieldInterface;
use Ilabs\Inpost_Pay\Lib\form\AbstractOption;
use Ilabs\Inpost_Pay\Lib\form\exception\OptionNameRequired;
use function Ilabs\Inpost_Pay\inpost_pay;

class ApmGroup extends AbstractGroup {

	/**
	 * @throws OptionNameRequired
	 */
	public function getShippingMethodField(): AbstractShippingMethodField {
		return ApmShippingMethod::instance( $this->get_zone_id() );
	}

	/**
	 * @return ShippingMappingFieldInterface[]
	 * @throws OptionNameRequired
	 */
	public function getFields(): array {
		return array(
			$this->getShippingMethodField(),
		);
	}

	/**
	 * @throws OptionNameRequired
	 */
	public function registerGroup(): void {
		$this->getShippingMethodField()->init();
	}

	public function getDeliveryOptionCode(): string {
		return GroupInterface::DELIVERY_OPTION_CODE_NONE;
	}

	public function getDeliveryTypeCode(): string {
		return GroupInterface::DELIVERY_TYPE_CODE_APM;
	}

	public function getApiDeliveryOptionsMap(): ?array {
		return array(
			GroupInterface::DELIVERY_OPTION_CODE_PWW
			=> $this->getDeliveryOptionName( GroupInterface::DELIVERY_OPTION_CODE_PWW ),
			GroupInterface::DELIVERY_OPTION_CODE_COD
			=> $this->getDeliveryOptionName( GroupInterface::DELIVERY_OPTION_CODE_COD ),
		);
	}

	public function getOptionSubGroups( ?int $zone_id = null ): ?array {
		return array(
			inpost_pay()->shipping_cost_settings( $zone_id )->getPwwApmSettingsGroup(),
			inpost_pay()->shipping_cost_settings( $zone_id )->getCodApmSettingsGroup(),
		);
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

	public function getPriceField(): ?AbstractPriceField {
		return null;
	}

	public function getIsActiveFieldId(): string {
		if ( $this->get_zone_id() !== null ) {
			return 'izi_group_apm_active_' . $this->get_zone_id();
		}

		return 'izi_group_apm_active';
	}

	protected function getIsActiveFieldLabel(): string {
		return __( 'Parcel locker', 'inpost-pay' );
	}
}
