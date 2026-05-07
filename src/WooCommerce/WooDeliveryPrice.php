<?php

namespace Ilabs\Inpost_Pay\WooCommerce;

use Automattic\WooCommerce\Utilities\NumberUtil;
use Ilabs\Inpost_Pay\Integration\FlexibleShipping\FlexibleShippingHelper;
use Ilabs\Inpost_Pay\Lib;
use Ilabs\Inpost_Pay\Lib\config\ShippingCost\AbstractGroup;
use Ilabs\Inpost_Pay\Lib\config\ShippingCost\AvailabilityGroupInterface;
use Ilabs\Inpost_Pay\Lib\config\ShippingCost\GroupInterface;
use Ilabs\Inpost_Pay\Lib\config\ShippingCost\ShippingMappingSettingsManager;
use Ilabs\Inpost_Pay\Lib\form\AbstractOption;
use Ilabs\Inpost_Pay\Lib\helpers\DigitalProduct;
use Ilabs\Inpost_Pay\Lib\helpers\ShippingZoneHelper;
use Ilabs\Inpost_Pay\Lib\item\Delivery;
use Ilabs\Inpost_Pay\Lib\item\DeliveryOption;
use Ilabs\Inpost_Pay\Lib\item\Price;
use Ilabs\Inpost_Pay\Logger;
use Ilabs\Inpost_Pay\models\DeliveryOptionHelper;
use Ilabs\Inpost_Pay\models\Destination;
use Ilabs\Inpost_Pay\Lib\Shipping\ProductDeliveryChecker;
use WC_Shipping_Method;
use WC_Shipping_Rate;
use WC_Shipping_Zones;
use WC_Tax;
use function Ilabs\Inpost_Pay\inpost_pay;

class WooDeliveryPrice {

	private array $cachedShippingRates = array(
		'rates'                => array(),
		'shipping_package_key' => '',
	);

	private array $cachedShippingPackages = array();
	private array $cachedCartItems        = array();
	private array $isTaxable              = array();

	/**
	 * @var WC_Shipping_Method[]
	 */
	private array $cachedShippingMethods = array();
	private bool $checkShippingAvailability;
	private ShippingMappingSettingsManager $shippingCostSettingsManager;
	private bool $freeDeliveryFound = false;

	private int $zone_id;

	public function __construct() {
		$user_zone                         = ProductDeliveryChecker::prepare_user_zone_context();
		$this->zone_id                     = $user_zone->get_id();
		$this->shippingCostSettingsManager = inpost_pay()->shipping_cost_settings( $user_zone->get_id() );
	}

	public function mapDelivery( $order = null ) {
		$this->cachedShippingPackages = $this->findShippingPackages();
		$this->cachedShippingRates    = $this->findShippingRates();
		$this->cachedCartItems        = $this->findCartItems();
		$options                      = array();

		$baseGroups   = array();
		$baseGroups[] = $this->shippingCostSettingsManager->getApmSettingsGroup();
		$baseGroups[] = $this->shippingCostSettingsManager->getCourierSettingsGroup();

		$this->checkShippingAvailability = $this->shippingCostSettingsManager
			->getCheckShippingAvailabilityField()
			->get_bool();

		foreach ( $baseGroups as $baseGroup ) {
			if ( empty( $baseGroup ) ) {
				continue;
			}

			if ( false === $baseGroup->getIsActiveField()->get_bool() ) {
				continue;
			}

			$deliveryType = $baseGroup->getDeliveryTypeCode();

			$parameters = null;
			$price      = null;

			if ( ! $order ) {
				$transportMethodField = $baseGroup->getShippingMethodField();

				if ( ! $transportMethodField ) {
					continue;
				}

				if ( '0' === (string) $transportMethodField->get() ) {
					continue;
				}

				$parameters = $this->getDeliveryParameters(
					$deliveryType,
					$transportMethodField,
					$baseGroup,
					null
				);

				if ( $parameters === null ) {
					continue;
				}

				if ( isset( $parameters->available ) && $parameters->available === false ) {
					continue;
				}
			} else {
				$price = new Price();
				$price->set_gross( wc_format_decimal( $order->get_shipping_total(), 2 ) );
				$price->set_net( $order->get_shipping_total() - $order->get_shipping_tax() );
				$price->set_vat( wc_format_decimal( $order->get_shipping_tax(), 2 ) );
			}

			$delivery = new Delivery();
			$delivery->set_delivery_type( $deliveryType );

			$deliveryDate = date( 'Y-m-d\T12:00:00.000\Z', strtotime( ' + 2 day' ) );
			$delivery->set_delivery_date( $deliveryDate );

			$groupsWithOptions = $baseGroup->getOptionSubGroups( $this->zone_id );

			$deliveryOptions = array();

			if ( is_array( $groupsWithOptions ) && ! empty( $groupsWithOptions ) ) {
				foreach ( $groupsWithOptions as $optionGroup ) {
					if ( ! $optionGroup instanceof AbstractGroup ) {
						continue;
					}

					if ( $optionGroup->getDeliveryOptionCode() === GroupInterface::DELIVERY_OPTION_CODE_PWW_COD ) {
						continue;
					}

					if ( ! $optionGroup->getIsActiveField()->get_bool() ) {
						continue;
					}

					if ( $optionGroup instanceof AvailabilityGroupInterface ) {
						$priceAvailable = $this->optionAvailability( $optionGroup );
						if ( ! $priceAvailable ) {
							continue;
						}
					}

					$mappedOption = $this->mapDeliveryOptions(
						$optionGroup,
						$baseGroup,
						$parameters
					);

					if ( ! $mappedOption ) {
						continue;
					}

					$deliveryOptions[] = $mappedOption;
				}
			}

			$delivery->set_delivery_options( $deliveryOptions );

			$delivery_price = $price ?? $this->mapDeliveryPrice(
				$parameters->net,
				$parameters->gross,
				$parameters->tax
			);

			$delivery->set_delivery_price( $delivery_price );

			$options[] = $delivery;
		}

		return DigitalProduct::addDigitalDeliveryMethod( $options );
	}

	private function findShippingPackages(): array {
		$dest = Destination::get();

		return ProductDeliveryChecker::prepare_manual_package( $dest );
	}

	private function findCartItems(): array {
		if ( isset( \WC()->cart ) && \WC()->cart && ! \WC()->cart->is_empty() ) {
			return \WC()->cart->get_cart();
		}

		return array();
	}

	private function findShippingRates(): array {
		$result = array(
			'rates'                => array(),
			'shipping_package_key' => '',
		);

		if ( ! empty( $this->cachedShippingPackages ) ) {
			$rates = $this->calculateRatesForZone( $this->zone_id, $this->cachedShippingPackages );
			if ( ! empty( $rates ) ) {
				return array(
					'rates'                => $rates,
					'shipping_package_key' => 0,
				);
			}
		}

		return $result;
	}

	/**
	 * Calculate shipping rates for a specific zone
	 */
	private function calculateRatesForZone( int $zone_id, array $package ): array {
		$zone             = \WC_Shipping_Zones::get_zone( $zone_id );
		$shipping_methods = $zone->get_shipping_methods( true );

		$rates = array();

		foreach ( $shipping_methods as $method ) {
			if ( ! $method->is_enabled() ) {
				continue;
			}

			$method->calculate_shipping( $package );

			// Get the rates from the method
			if ( isset( $method->rates ) && is_array( $method->rates ) ) {
				foreach ( $method->rates as $rate ) {
					$rates[ $rate->get_id() ] = $rate;
				}
			}
		}

		return $rates;
	}

	/**
	 * Gets delivery parameters for a specific shipping method.
	 *
	 * @param string         $deliveryType Delivery type code.
	 * @param AbstractOption $transportMethodField Transport method field.
	 * @param GroupInterface $group Settings group.
	 * @param bool|null      $checkShippingAvailability Whether to check availability.
	 *
	 * @return \stdClass|null Delivery parameters object or null if not available.
	 * TODO: Replace with typed DeliveryParameters object.
	 */
	public function getDeliveryParameters(
		string $deliveryType,
		AbstractOption $transportMethodField,
		GroupInterface $group,
		?bool $checkShippingAvailability = null
	): ?\stdClass {
		$method = $transportMethodField->get();
		Logger::log( "CACHED DELIVERY METHOD: $method" );

		$response = array(
			'available' => false,
			'net'       => 0,
			'tax'       => 0,
			'gross'     => 0,
			'log'       => array(),
		);

		if ( null === $checkShippingAvailability ) {
			$checkShippingAvailability = $this->checkShippingAvailability;
		}

		$hasPhysicalProducts = false;
		foreach ( $this->cachedCartItems as $cart_item ) {
			if ( ! $this->isProductVirtual( $cart_item['data']->get_id() ) ) {
				$hasPhysicalProducts = true;
				break;
			}
		}

		if ( ! $hasPhysicalProducts ) {
			return (object) $response;
		}

		$allProductsUnavailable = true;
		foreach ( $this->cachedCartItems as $cart_item ) {
			$pid = $cart_item['data']->get_id();

			if ( $this->isProductVirtual( $pid ) ) {
				continue;
			}

			if ( $checkShippingAvailability ) {
				$isAvailable = $this->iziAvailableForProduct( $pid, $method );

				if ( $isAvailable ) {
					$allProductsUnavailable = false;
				}
			} else {
				$allProductsUnavailable = false;
			}
		}

		if ( $allProductsUnavailable && $checkShippingAvailability ) {
			return null;
		}

		$destination = Destination::get();
		$rates       = ShippingZoneHelper::getShippingRatesForZone( $this->zone_id, $destination );

		$shipping_packages    = $this->cachedShippingPackages;
		$shipping_package_key = $this->cachedShippingRates['shipping_package_key'];

		$single_package = isset( $shipping_packages['contents'] )
			? $shipping_packages
			: ( $shipping_packages[ $shipping_package_key ] ?? null );

		$coupons                     = WC()->cart->get_coupons();
		$coupon_grants_free_shipping = false;

		if ( $coupons ) {
			foreach ( $coupons as $coupon ) {
				if ( $coupon->is_valid() && $coupon->get_free_shipping() ) {
					$coupon_grants_free_shipping = true;
					break;
				}
			}
		}

		$native_free_shipping_eligible = false;
		foreach ( $rates as $pre_check_rate ) {
			if ( $pre_check_rate->get_method_id() !== 'free_shipping' ) {
				continue;
			}

			$free_shipping_method = WC_Shipping_Zones::get_shipping_method( $pre_check_rate->get_instance_id() );

			if ( ! $free_shipping_method instanceof \WC_Shipping_Free_Shipping ) {
				continue;
			}

			if ( $single_package === null ) {
				continue;
			}

			if ( ! $free_shipping_method->is_available( $single_package ) ) {
				continue;
			}

			if ( ! $this->check_free_shipping_eligibility( $free_shipping_method ) ) {
				continue;
			}

			$native_free_shipping_eligible = true;
			break;
		}

		$flexible_shipping_helper = new FlexibleShippingHelper();
		$baseCartTotal            = ProductDeliveryChecker::get_cart_total();

		foreach ( $rates as $rate_key => $rate ) {
			$this->freeDeliveryFound = false;
			$response['log'][]       = 'shipping_package looping through rates : ' . $rate->id;

			$rate_id          = $rate->get_id();
			$rate_method      = $rate->get_method_id();
			$rate_instance_id = $rate->get_instance_id();
			$rate_method_id   = $rate_method . ':' . $rate_instance_id;

			$shipping_method = WC_Shipping_Zones::get_shipping_method( $rate->get_instance_id() );

			if ( $rate_id === $method || $rate_method_id === $method ) {
				if ( $shipping_method instanceof WC_Shipping_Method && $shipping_method->id === $rate_method ) {
					$this->cachedShippingMethods[ $deliveryType ] = $shipping_method;

					$is_available          = $single_package && $shipping_method->is_available( $single_package );
					$response['available'] = $is_available;

					/** @var WC_Shipping_Rate $found_rate */
					$found_rate = $rate;
					$isTaxable  = $shipping_method->is_taxable();

					$this->cacheDeliveryMethodTaxableStatus( $group, $isTaxable );

					$rate_meta_data = $found_rate->get_meta_data();
					if ( isset( $rate_meta_data['izi_full_cost'] ) ) {
						$rateCost = $rate_meta_data['izi_full_cost'];
					} else {
						$rateCost = $found_rate->get_cost();
					}
					$addVatToShippingCost = $isTaxable;

					$taxes = $addVatToShippingCost
						? $found_rate->get_taxes()
						: WC_Tax::calc_tax( $rateCost, WC_Tax::get_shipping_tax_rates(), $isTaxable );

					$tax_sum_raw    = array_sum( $taxes );
					$tax_normalized = self::normalizePrice( $tax_sum_raw );

					Logger::log( '[1GR_DEBUG] rate_id=' . $rate_id );
					Logger::log( '[1GR_DEBUG] rateCost=' . $rateCost . ' source=' . ( isset( $rate_meta_data['izi_full_cost'] ) ? 'izi_full_cost' : 'get_cost' ) );
					Logger::log( '[1GR_DEBUG] isTaxable=' . ( $isTaxable ? 'true' : 'false' ) . ' addVatOnTop=' . ( $addVatToShippingCost ? 'true' : 'false' ) );
					Logger::log( '[1GR_DEBUG] taxes_raw=' . wp_json_encode( $taxes ) );
					Logger::log( '[1GR_DEBUG] tax_sum_raw=' . $tax_sum_raw . ' tax_normalized=' . $tax_normalized . ' diff=' . ( $tax_sum_raw - $tax_normalized ) );

					$response['net'] = $rateCost;
					$response['tax'] = $tax_normalized;

					if ( ! $addVatToShippingCost ) {
						$response['gross'] = $response['net'];
						$response['net']  -= $response['tax'];
					} else {
						$response['gross'] = $response['net'] + $response['tax'];
					}

					Logger::log( '[1GR_DEBUG] response net=' . $response['net'] . ' tax=' . $response['tax'] . ' gross=' . $response['gross'] );

					$is_free_shipping = false;

					if ( $native_free_shipping_eligible ) {
						$is_free_shipping = true;
					} elseif ( $shipping_method instanceof \WC_Shipping_Free_Shipping ) {
						$is_free_shipping = $this->check_free_shipping_eligibility( $shipping_method );
						Logger::log( 'free_shipping_method=' . ( $is_free_shipping ? 'true' : 'false' ) );
					} elseif ( 0 === strpos( $rate_method_id, 'easypack_' ) ) {
						$inpost_result    = ProductDeliveryChecker::get_inpost_free_shipping_threshold( $rate_method_id, null );
						$threshold        = $inpost_result['threshold'];
						$is_free_shipping = ( $threshold > 0 && $baseCartTotal >= $threshold );
						Logger::log(
							"inpost_threshold=$threshold cart=$baseCartTotal free=" . ( $is_free_shipping ? 'true' : 'false' ),
						);
					} elseif ( $coupon_grants_free_shipping ) {
						$supports_coupon  = $flexible_shipping_helper->supports_coupon_free_shipping( $shipping_method );
						$is_free_shipping = ( $supports_coupon === true );
						Logger::log( 'coupon_free_shipping_applies=' . ( $is_free_shipping ? 'true' : 'false' ) );
					}

					if ( $is_available && $is_free_shipping ) {
						$this->freeDeliveryFound = true;
						$response['net']         = 0.00;
						$response['tax']         = 0.00;
						$response['gross']       = 0.00;
						Logger::log( 'APPLIED FREE SHIPPING' );
					}

					return (object) $response;
				}
			}
		}

		Logger::log( 'NO_MATCH return default' );

		return (object) $response;
	}

	private function cacheDeliveryMethodTaxableStatus(
		GroupInterface $group,
		bool $isTaxable
	) {
		$this->isTaxable[ $group->getGroupId() ] = $isTaxable;
	}


	public function mapDeliveryPrice( $net, $gross, $tax ): Lib\item\Price {
		$price = new Price();

		$price->set_gross( wc_format_decimal( $gross, 2 ) );
		$price->set_net( $net );
		$price->set_vat( wc_format_decimal( $tax, 2 ) );

		return $price;
	}

	public function mapDeliveryOptionPrice( $net ) {
		$price = new Price();
		$gross = $net * self::getShippingTaxModifier();
		$vat   = $gross - $net;
		$price->set_gross( wc_format_decimal( $gross, 2 ) );
		$price->set_net( $net );
		$price->set_vat( wc_format_decimal( $vat, 2 ) );

		return $price;
	}

	private function optionAvailability(
		AvailabilityGroupInterface $availabilityGroupInterface
	): bool {
		$dayOfWeek = date( 'N' );
		$hour      = date( 'H' );

		$dayFrom  = $availabilityGroupInterface->getAvailableFromDayField()->get();
		$dayTo    = $availabilityGroupInterface->getAvailableToDayField()->get();
		$hourFrom = $availabilityGroupInterface->getAvailableFromHourField()->get();
		$hourTo   = $availabilityGroupInterface->getAvailableToHourField()->get();

		if ( $dayOfWeek < $dayFrom ) {
			return false;
		}
		if ( ( $dayOfWeek === $dayFrom ) && $hour < $hourFrom ) {
			return false;
		}

		if ( $dayOfWeek > $dayTo ) {
			return false;
		}
		if ( ( $dayOfWeek === $dayTo ) && $hour > $hourTo ) {
			return false;
		}

		return true;
	}

	public function mapDeliveryOptions(
		GroupInterface $optionSubGroup,
		GroupInterface $baseGroup,
		\stdClass $baseDeliveryPrice
	): ?DeliveryOption {

		$deliveryOptionHelper = new DeliveryOptionHelper(
			$this,
			$optionSubGroup,
			$baseGroup,
			array(
				'net'   => $baseDeliveryPrice->net,
				'gross' => $baseDeliveryPrice->gross,
				'tax'   => $baseDeliveryPrice->tax,
			)
		);

		if ( false === $deliveryOptionHelper->isAvailable() ) {
			return null;
		}

		$deliveryOptionHelper->calculateFee( $this->checkShippingAvailability );

		if ( ! $deliveryOptionHelper->isAvailable() ) {
			return null;
		}

		$option = new DeliveryOption();

		$price = new Price();
		$option->set_delivery_name( $optionSubGroup->getDeliveryOptionName() );
		$option->set_delivery_code_value( $optionSubGroup->getDeliveryOptionCode() );

		$price->set_gross( wc_format_decimal( $deliveryOptionHelper->getOptionPrice()['gross'], 2 ) );
		$price->set_net( $deliveryOptionHelper->getOptionPrice()['net'] );
		$price->set_vat( wc_format_decimal( $deliveryOptionHelper->getOptionPrice()['tax'], 2 ) );

		$option->set_delivery_option_price( $price );

		return $option;
	}

	public static function getShippingTaxModifier(): float {
		return 1.23;
	}

	protected function iziAvailableForProduct( $id, string $transportMethod ): bool {
		$methodBase = explode( ':', esc_attr( $transportMethod ) )[0];

		if ( strpos( $methodBase, 'easypack_' ) !== 0 ) {
			return true;
		}

		$configuredMethods = array( $methodBase );
		$allowedMethods    = get_post_meta( $id, 'woo_inpost_shipping_methods_allowed', true );

		if ( ! empty( $allowedMethods ) && is_array( $allowedMethods ) ) {
			$found = false;
			foreach ( $allowedMethods as $method ) {
				$method = explode( ':', $method )[0];
				if ( in_array( $method, $configuredMethods, true ) ) {
					$found = true;
					break;
				}
			}

			return $found;
		}

		return true;
	}

	public function isProductVirtual( $productId ): bool {
		$product = wc_get_product( $productId );

		return ( $product instanceof \WC_Product ) && $product->is_virtual();
	}

	public function getCachedShippingMethods(): array {
		return $this->cachedShippingMethods;
	}

	public function getIsTaxable(): array {
		return $this->isTaxable;
	}

	public static function normalizePrice( float $price ): float {
		return (float) wc_format_decimal( abs( $price ), 2 );
	}

	public function isFreeDeliveryFound(): bool {
		return $this->freeDeliveryFound;
	}

	/**
	 * Checks if the free shipping method is eligible for free shipping.
	 *
	 * @param \WC_Shipping_Free_Shipping $shipping_method
	 *
	 * @return bool
	 */
	private function check_free_shipping_eligibility( \WC_Shipping_Free_Shipping $shipping_method ): bool {
		$has_met_min_amount = false;
		if ( in_array( $shipping_method->requires, array( 'min_amount', 'either', 'both' ), true ) ) {
			$total = WC()->cart->get_displayed_subtotal();

			if ( 'no' === $shipping_method->ignore_discounts ) {
				$total -= WC()->cart->get_discount_total();
				if ( WC()->cart->display_prices_including_tax() ) {
					$total -= WC()->cart->get_discount_tax();
				}
			}

			$total     = NumberUtil::round( $total, wc_get_price_decimals() );
			$minAmount = (float) $shipping_method->min_amount;

			if ( $total >= $minAmount ) {
				$has_met_min_amount = true;
			}
		}

		switch ( $shipping_method->requires ) {
			case 'min_amount':
				return $has_met_min_amount;
			case 'coupon':
				return $this->has_free_shipping_coupon();
			case 'both':
				return $has_met_min_amount && $this->has_free_shipping_coupon();
			case 'either':
				return $has_met_min_amount || $this->has_free_shipping_coupon();
			default:
				return false;
		}
	}

	/**
	 * Checks if there's a valid free shipping coupon in the cart.
	 *
	 * @return bool
	 */
	private function has_free_shipping_coupon(): bool {
		$coupons = WC()->cart->get_coupons();

		if ( $coupons ) {
			foreach ( $coupons as $coupon ) {
				if ( $coupon->is_valid() && $coupon->get_free_shipping() ) {
					return true;
				}
			}
		}

		return false;
	}
}
