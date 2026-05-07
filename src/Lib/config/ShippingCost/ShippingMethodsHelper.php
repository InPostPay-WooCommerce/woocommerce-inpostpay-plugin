<?php

namespace Ilabs\Inpost_Pay\Lib\config\ShippingCost;

class ShippingMethodsHelper {

	private ShippingMappingSettingsManager $shippingCostOptions;


	public function __construct(
		ShippingMappingSettingsManager $shippingCostOptions
	) {
		$this->shippingCostOptions = $shippingCostOptions;
	}

	public function getConfiguredShippingMethods(): array {
		$return = array();
		foreach ( $this->getShippingMethodFields() as $field ) {
			$val = $field->get();
			if ( ! empty( $val ) ) {
				$return[] = $val;
			}
		}

		return $return;
	}

	public function getConfiguredShippingMethodsExploded(): array {
		$return = array();
		foreach ( $this->getConfiguredShippingMethods() as $value ) {
			$return[] = explode( ':', esc_attr( $value ) )[0];
		}

		return $return;
	}

	/**
	 * @return AbstractShippingMethodField[]
	 */
	public function getShippingMethodFields(): array {
		return array(
			$this->shippingCostOptions->getApmSettingsGroup()
										->getShippingMethodField(),
			$this->shippingCostOptions->getCodApmSettingsGroup()
										->getShippingMethodField(),
			$this->shippingCostOptions->getPwwApmSettingsGroup()
										->getShippingMethodField(),
			$this->shippingCostOptions->getCourierSettingsGroup()
										->getShippingMethodField(),
			$this->shippingCostOptions->getCodCourierSettingsGroup()
										->getShippingMethodField(),
		);
	}
}
