<?php

namespace Ilabs\Inpost_Pay\models;

use Ilabs\Inpost_Pay\Lib\config\ShippingCost\GroupInterface;
use Ilabs\Inpost_Pay\Lib\config\ShippingCost\OptionCostMappingApproach;
use Ilabs\Inpost_Pay\Lib\form\AbstractOption;
use Ilabs\Inpost_Pay\WooCommerce\WooDeliveryPrice;
use WC_Tax;


class DeliveryOptionHelper {

	private WooDeliveryPrice $wooDeliveryPrice;
	private GroupInterface $optionGroup;
	private GroupInterface $baseGroup;
	private array $baseDeliveryPrice;
	private ?array $optionPrice;
	private bool $fixedCost = false;
	private bool $available = false;
	private float $fee      = 0;

	private string $mappedShippingMethod = '';
	private AbstractOption $mappedShippingMethodField;

	public function __construct(
		WooDeliveryPrice $wooDeliveryPrice,
		GroupInterface $optionGroup,
		GroupInterface $baseGroup,
		array $baseDeliveryPrice
	) {
		$this->wooDeliveryPrice  = $wooDeliveryPrice;
		$this->optionGroup       = $optionGroup;
		$this->baseGroup         = $baseGroup;
		$this->baseDeliveryPrice = $baseDeliveryPrice;

		$this->define();
	}


	private function define() {
		$this->optionPrice['net']   = 0.0;
		$this->optionPrice['gross'] = 0.0;
		$this->optionPrice['tax']   = 0.0;

		$optionCostMappingApproach = $this->optionGroup->get_option_cost_mapping_approach();

		$isOptionCostMappingApproachFee = $optionCostMappingApproach === OptionCostMappingApproach::OPTION_COST_MAPPING_APPROACH_FEE;
		// todo rename

		if ( $isOptionCostMappingApproachFee ) {
			$priceField = $this->optionGroup->get_price_field();
			if ( $priceField && ! $this->available ) {
				$priceFieldVal = floatval( $priceField->get() );
				if ( $priceFieldVal > 0 ) {
					$this->fee       = $priceFieldVal;
					$this->fixedCost = true;
					$this->available = true;
				}
			}
		} else {
			$transportMethodField = $this->optionGroup->get_shipping_method_field();
			if ( $transportMethodField ) {
				$method = $transportMethodField->get();

				if ( ! empty( $method ) && (string) $method !== '0' ) {
					$this->mappedShippingMethod      = $method;
					$this->mappedShippingMethodField = $transportMethodField;
					$this->available                 = true;
					$this->fixedCost                 = false;
				} else {
					$this->available = false;
				}
			}
		}
	}

	public function calculateFee( bool $checkShippingAvailability ) {

		if ( $this->isFixedCost() ) {
			$this->calculateByFixedPrice();
		} else {
			$this->calculateUsingShippingMethod( $checkShippingAvailability );
		}
	}

	private function calculateByFixedPrice() {

		$optionPrice = wc_format_decimal(
			$this->fee,
			2
		);
		$optionPrice = abs( ( floatval( $optionPrice ) ) );

		if ( $optionPrice > 0 ) {
			$taxes = WC_Tax::calc_tax(
				$optionPrice,
				WC_Tax::get_shipping_tax_rates()
			);
			$tax   = array_sum( $taxes );

			$shippingMethod = $this->wooDeliveryPrice->getCachedShippingMethods()[ $this->baseGroup->get_delivery_type_code() ];

			if ( $shippingMethod === null || $this->wooDeliveryPrice->isFreeDeliveryFound() ) {
				$this->optionPrice['gross'] = 0;
				$this->optionPrice['net']   = 0;
				$this->optionPrice['tax']   = 0;

				return;
			}

			if ( $shippingMethod->is_taxable() ) {
				$this->optionPrice['gross'] = $this->normalizePrice( $optionPrice + (float) $tax );
				$this->optionPrice['net']   = $this->normalizePrice( $optionPrice );
				$this->optionPrice['tax']   = $this->normalizePrice( array_sum( $taxes ) );
			} else {
				$this->optionPrice['gross'] = $this->normalizePrice( $optionPrice );
				$this->optionPrice['net']   = $this->normalizePrice( $optionPrice - (float) $tax );
				$this->optionPrice['tax']   = $this->normalizePrice( array_sum( $taxes ) );
			}
		}
	}

	private function calculateUsingShippingMethod(
		bool $checkShippingAvailability
	) {
		$deliveryParameters = $this->wooDeliveryPrice->getDeliveryParameters(
			$this->baseGroup->get_delivery_type_code(),
			$this->mappedShippingMethodField,
			$this->optionGroup,
			$checkShippingAvailability,
		);

		if ( false === $deliveryParameters->available ) {
			$this->optionPrice['net']   = 0.0;
			$this->optionPrice['gross'] = 0.0;
			$this->optionPrice['tax']   = 0.0;
			$this->available            = false;

			return;
		}

		$optionNet   = $deliveryParameters->net;
		$baseNet     = floatval( $this->baseDeliveryPrice['net'] );
		$optionGross = $deliveryParameters->gross;
		$baseGross   = floatval( $this->baseDeliveryPrice['gross'] );
		$optionTax   = $deliveryParameters->tax;
		$baseTax     = floatval( $this->baseDeliveryPrice['tax'] );

		$optionNetMinusBaseNet = $optionNet - $baseNet;

		if ( $optionNetMinusBaseNet <= 0 ) {
			$this->optionPrice['net']   = 0.0;
			$this->optionPrice['gross'] = 0.0;
			$this->optionPrice['tax']   = 0.0;
		} else {
			$optionGrossMinusBaseGross = $this->normalizePrice( $optionGross - $baseGross );
			$optionTaxMinusBaseTax     = $this->normalizePrice( $optionTax - $baseTax );

			$this->optionPrice['net']   = $this->normalizePrice( $optionNetMinusBaseNet );
			$this->optionPrice['gross'] = $optionGrossMinusBaseGross;
			$this->optionPrice['tax']   = $optionTaxMinusBaseTax;
		}
	}

	private function normalizePrice( float $price ): float {
		$price = wc_format_decimal(
			$price,
			2
		);

		return abs( ( floatval( $price ) ) );
	}

	public function getParentDeliveryPrice(): ?array {
		return $this->parentDeliveryPrice;
	}

	public function getParentGroup(): ?GroupInterface {
		return $this->parentGroup;
	}

	public function getOptionGroup(): GroupInterface {
		return $this->optionGroup;
	}

	public function getBaseGroup(): ?GroupInterface {
		return $this->baseGroup;
	}

	public function getBaseDeliveryPrice(): ?array {
		return $this->baseDeliveryPrice;
	}

	public function isFixedCost(): bool {
		return $this->fixedCost;
	}

	public function isAvailable(): bool {
		return $this->available;
	}

	public function getFee(): float {
		return $this->fee;
	}

	public function getMappedShippingMethod(): string {
		return $this->mappedShippingMethod;
	}

	public function setParentDeliveryPrice( array $parentDeliveryPrice ): void {
		$this->parentDeliveryPrice = $parentDeliveryPrice;
	}

	public function setParentGroup( GroupInterface $parentGroup ): void {
		$this->parentGroup = $parentGroup;
	}

	public function setBaseDeliveryPrice( array $baseDeliveryPrice ): void {
		$this->baseDeliveryPrice = $baseDeliveryPrice;
	}

	public function setOptionPrice( array $optionPrice ): void {
		$this->optionPrice = $optionPrice;
	}

	public function getMappedShippingMethodField(): AbstractOption {
		return $this->mappedShippingMethodField;
	}

	public function getOptionPrice(): ?array {
		return $this->optionPrice;
	}

	/**
	 * @param GroupInterface   $baseGroup
	 * @param GroupInterface[]
	 *
	 * @return GroupInterface
	 */
	public static function getOrderFinalDeliveryGroup(
		GroupInterface $baseGroup,
		array $optionsGroups
	): GroupInterface {
		// no options
		if ( empty( $optionsGroups ) ) {
			return $baseGroup;
		}

		// one option
		if ( count( $optionsGroups ) === 1 ) {
			if ( $optionsGroups[0]->get_option_cost_mapping_approach()
				!== OptionCostMappingApproach::OPTION_COST_MAPPING_APPROACH_SHIPPING_METHOD ) {
				return $baseGroup;// $transportMethodField not used
			}

			$transportMethodField = $optionsGroups[0]->get_shipping_method_field();
			if ( $transportMethodField ) {
				$method = $transportMethodField->get();// update WcOrder with this shipping method if option has mapped method not price fee
				if ( is_string( $method ) && $method !== '0' ) {
					return $optionsGroups[0];
				}
			}
		}

		// pww+cod
		return $baseGroup;
	}
}
