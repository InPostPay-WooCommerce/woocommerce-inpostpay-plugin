<?php

namespace Ilabs\Inpost_Pay\Lib\config\ShippingCost;

abstract class AbstractGroup implements GroupInterface {

	private ?BoolField $isActiveField                             = null;
	private ?OptionCostMappingApproach $optionCostMappingApproach = null;
	private ?int $zone_id = null;

	public function __construct( ?int $zone_id = null ) {
		$this->zone_id = $zone_id;
	}

	public function getGroupId(): string {
		$id = $this->getDeliveryTypeCode();

		if ( $this->getDeliveryOptionCode() !== GroupInterface::DELIVERY_OPTION_CODE_NONE ) {
			$id .= '_' . $this->getDeliveryOptionCode();
		}

		return $id;
	}

	public function getDeliveryOptionName(): ?string {
		if ( GroupInterface::DELIVERY_OPTION_CODE_COD === $this->getDeliveryOptionCode() ) {
			return 'Pobranie';
		}

		if ( GroupInterface::DELIVERY_OPTION_CODE_PWW === $this->getDeliveryOptionCode() ) {
			return 'Paczka w Weekend';
		}

		return null;
	}

	abstract protected function getIsActiveFieldId(): string;

	protected function getIsActiveFieldLabel(): string {
		return __( 'Enabled', 'inpost-pay' );
	}

	protected function getIsActiveFieldTooltip(): string {
		return '';
	}

	protected function getIsActiveFieldDefault(): bool {
		return true;
	}

	public function getIsActiveField(): BoolField {
		if ( false === $this->isActiveField instanceof BoolField ) {
			$this->initIsActiveField();
		}

		return $this->isActiveField;
	}

	public function initIsActiveField(): void {

		$this->isActiveField = new BoolField(
			$this->getIsActiveFieldId(),
			$this->getIsActiveFieldLabel(),
			$this->getIsActiveFieldTooltip(),
			$this->getIsActiveFieldDefault()
		);
		$this->isActiveField->init();
	}


	public function getOptionCostMappingApproach(): string {
		$this->initOptionCostMappingApproach();
		if ( $this->optionCostMappingApproach ) {
			return $this->optionCostMappingApproach->get( OptionCostMappingApproach::OPTION_COST_MAPPING_APPROACH_SHIPPING_METHOD );
		}

		return $this->getOptionCostMappingApproachDefault();
	}

	public function getOptionCostMappingApproachId(): ?string {
		return null;
	}

	protected function getOptionCostMappingApproachLabel(): string {
		return '';
	}

	protected function getOptionCostMappingApproachTooltip(): string {
		return '';
	}

	protected function getOptionCostMappingApproachDefault(): string {
		return OptionCostMappingApproach::OPTION_COST_MAPPING_APPROACH_SHIPPING_METHOD;
	}

	public function getOptionCostMappingApproachObj(): ?OptionCostMappingApproach {
		if ( false === $this->optionCostMappingApproach instanceof OptionCostMappingApproach ) {
			$this->initOptionCostMappingApproach();
		}

		return $this->optionCostMappingApproach;
	}

	public function initOptionCostMappingApproach(): void {
		if ( $this->optionCostMappingApproach ) {
			return;
		}

		$id      = $this->getOptionCostMappingApproachId();
		$label   = $this->getOptionCostMappingApproachLabel();
		$default = $this->getOptionCostMappingApproachDefault();
		$tooltip = $this->getOptionCostMappingApproachTooltip();

		if ( $id ) {
			$this->optionCostMappingApproach = new OptionCostMappingApproach(
				$this->getOptionCostMappingApproachId(),
				$this->getOptionCostMappingApproachLabel(),
				$this->getOptionCostMappingApproachTooltip(),
				$this->getOptionCostMappingApproachDefault()
			);

			$this->optionCostMappingApproach->init();
		}
	}

	public function get_zone_id(): ?int {
		return $this->zone_id;
	}
}
