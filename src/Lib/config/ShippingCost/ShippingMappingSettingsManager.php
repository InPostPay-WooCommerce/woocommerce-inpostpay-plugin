<?php

namespace Ilabs\Inpost_Pay\Lib\config\ShippingCost;

use Ilabs\Inpost_Pay\Lib\config\ShippingCost\Apm\ApmGroup;
use Ilabs\Inpost_Pay\Lib\config\ShippingCost\CodApm\CodApmGroup;
use Ilabs\Inpost_Pay\Lib\config\ShippingCost\CodCourier\CodCourierGroup;
use Ilabs\Inpost_Pay\Lib\config\ShippingCost\Courier\CourierGroup;
use Ilabs\Inpost_Pay\Lib\config\ShippingCost\PwwApm\PwwApmGroup;
use Ilabs\Inpost_Pay\Lib\helpers\ShippingZoneHelper;


class ShippingMappingSettingsManager {

	private ShippingMethodsHelper $shippingMethodsHelper;
	private ?int $zone_id = null;

	/**
	 * @var GroupInterface[]
	 */
	private array $groups;

	public function __construct( ?int $zone_id = null ) {
		$this->shippingMethodsHelper = new ShippingMethodsHelper( $this );

		$this->zone_id = $zone_id;

		$this->groups = array(
			$this->getApmSettingsGroup(),
			$this->getCodApmSettingsGroup(),
			$this->getCourierSettingsGroup(),
			$this->getCodCourierSettingsGroup(),
			$this->getPwwApmSettingsGroup(),
		);
	}

	public function register() {
		foreach ( $this->groups as $group ) {
			$group->registerGroup();
			$group->initIsActiveField();
			$group->initOptionCostMappingApproach();

		}
		// $this->getShippingAddTaxField()->init();
		$this->getCheckShippingAvailabilityField()->init();
	}

	public function findGroup(
		string $deliveryTypeCode,
		string $optionCode = GroupInterface::DELIVERY_OPTION_CODE_NONE
	): ?GroupInterface {

		foreach ( $this->groups as $group ) {
			if ( $optionCode !== $group->getDeliveryOptionCode()
				|| $deliveryTypeCode !== $group->getDeliveryTypeCode() ) {
				continue;
			}

			return $group;

		}

		return null;
	}

	/**
	 * @return GroupInterface[]
	 */
	public function findCourierGroupsWithOptions(): array {
		return array(
			$this->getCodCourierSettingsGroup(),
		);
	}

	/**
	 * @return GroupInterface[]
	 */
	public function findApmGroupsWithOptions(): array {
		return array(
			$this->getPwwApmSettingsGroup(),
			$this->getCodApmSettingsGroup(),
		);
	}

	public function getApmFields() {
		return array_merge(
			$this->getApmSettingsGroup()->getFields(),
			$this->getPwwApmSettingsGroup()->getFields(),
			$this->getCodApmSettingsGroup()->getFields()
		);
	}

	public function getCourierFields() {
		return array_merge(
			$this->getCourierSettingsGroup()->getFields(),
			$this->getCodCourierSettingsGroup()->getFields()
		);
	}

	public function getCodApmSettingsGroup(): CodApmGroup {
		return new CodApmGroup( $this->zone_id );
	}

	public function getCodCourierSettingsGroup(): CodCourierGroup {
		return new CodCourierGroup( $this->zone_id );
	}

	public function getCourierSettingsGroup(): CourierGroup {
		return new CourierGroup( $this->zone_id );
	}

	public function getPwwApmSettingsGroup(): PwwApmGroup {
		return new PwwApmGroup( $this->zone_id );
	}

	public function getApmSettingsGroup(): ApmGroup {
		return new ApmGroup( $this->zone_id );
	}

	public function getCheckShippingAvailabilityField(): CheckShippingAvailability {
		return CheckShippingAvailability::instance();
	}

	public function get_shipping_methods_helper(): ShippingMethodsHelper {
		return $this->shippingMethodsHelper;
	}

	public function isGroupWithOptions(
		GroupInterface $settingsGroupInterface
	): bool {
		return $settingsGroupInterface->getDeliveryOptionCode() !== GroupInterface::DELIVERY_OPTION_CODE_NONE;
	}

	public static function get() {
	}
}
