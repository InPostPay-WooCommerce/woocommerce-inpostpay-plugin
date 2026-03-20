<?php
/**
 * Orchestrates InPost delivery option availability checks for products.
 *
 * @package Ilabs\Inpost_Pay\Lib\Shipping
 */

declare( strict_types=1 );

namespace Ilabs\Inpost_Pay\Lib\Shipping;

use Ilabs\Inpost_Pay\EntityLayer\Entity\UnavailableEntity;
use Ilabs\Inpost_Pay\Integration\Basket\Availability\UnavailabilityService;
use Ilabs\Inpost_Pay\Lib\config\ShippingCost\GroupInterface;
use Ilabs\Inpost_Pay\Lib\config\ShippingCost\ShippingMappingSettingsManager;
use Ilabs\Inpost_Pay\Lib\helpers\DigitalProduct;
use Ilabs\Inpost_Pay\Lib\Utils\DeliveryAvailabilityTracker;
use Ilabs\Inpost_Pay\Logger;
use WC_Product;
use function Ilabs\Inpost_Pay\inpost_pay_container;

/**
 * Class ProductDeliveryChecker
 *
 * Orchestrates product delivery option availability checks by coordinating
 * zone resolution, method mapping, rate calculation, InPost restrictions,
 * unavailability rules, weight limits, and free shipping detection.
 *
 * @package Ilabs\Inpost_Pay\Lib\Shipping
 */
class ProductDeliveryChecker {

	/**
	 * Maximum product weight in kg that can be shipped via APM (parcel locker).
	 *
	 * @var int
	 */
	private static int $max_default_weight = 25;

	/**
	 * Manager for shipping mapping settings.
	 *
	 * @var ShippingMappingSettingsManager|null
	 */
	private static ?ShippingMappingSettingsManager $shipping_cost_settings_manager = null;

	/**
	 * Delivery APM type code supported by InPost.
	 */
	private const DELIVERY_TYPE_APM = GroupInterface::DELIVERY_TYPE_CODE_APM;

	/**
	 * Delivery Courier type code supported by InPost.
	 */
	private const DELIVERY_TYPE_COURIER = GroupInterface::DELIVERY_TYPE_CODE_COURIER;

	/**
	 * Supported InPost delivery type codes.
	 *
	 * @var array
	 */
	private static array $inpost_method_types = array(
		self::DELIVERY_TYPE_APM,
		self::DELIVERY_TYPE_COURIER,
	);

	/**
	 * Valid shipping method prefixes by delivery type, used for mapped method validation.
	 *
	 * @var array
	 */
	private static array $valid_method_prefixes = array(
		GroupInterface::DELIVERY_TYPE_CODE_APM     => array(
			'easypack_parcel_machines',
			'easypack_parcel_machines_cod',
		),
		GroupInterface::DELIVERY_TYPE_CODE_COURIER => array(
			'easypack_shipping_courier',
			'easypack_cod_shipping_courier',
		),
	);

	/**
	 * Gets delivery options for a product.
	 *
	 * This function determines which InPost delivery methods are available for a product by:
	 * 1. Checking available shipping methods in the current zone
	 * 2. Applying InPost-specific product restrictions (when izi_shipping_check_shipping_availability is enabled)
	 * 3. Only applying restrictions to easypack_ methods (non-easypack methods are left unrestricted)
	 * 4. Validating method prefixes for mapped methods
	 * 5. Applying additional availability checks (weight, unavailability service, etc.)
	 *
	 * Restrictions logic:
	 * - When allowedMethods is not configured (empty): no restriction applies (null) → all mapped methods available
	 * - When allowedMethods has values: allows only specified easypack_ methods for a given type
	 * - Non-easypack methods (like flat_rate, free_shipping) are never affected by InPost restrictions
	 *
	 * @param WC_Product $product            Product.
	 * @param bool       $is_related_product Whether this is a related product.
	 * @param int        $quantity           Product quantity.
	 * @param bool       $enable_tracking    Enable detailed tracking of decisions.
	 *
	 * @return array|null Array with shipping methods or null if all delivery options are available.
	 */
	public static function get_delivery_options(
		WC_Product $product,
		bool $is_related_product = false,
		int $quantity = 1,
		bool $enable_tracking = true
	): ?array {
		$tracker = $enable_tracking ? new DeliveryAvailabilityTracker( $product, $is_related_product ) : null;

		$user_zone = ShippingZoneResolver::prepare_user_zone_context();
		ShippingZoneResolver::set_zone_id( $user_zone->get_id() );

		if ( null === self::$shipping_cost_settings_manager ) {
			self::$shipping_cost_settings_manager = inpost_pay()->shipping_cost_settings( ShippingZoneResolver::get_zone_id() );
			ShippingMethodMapper::init( self::$shipping_cost_settings_manager );
		}

		if ( $tracker ) {
			$tracker->add_global_decision(
				'zone_detection',
				sprintf( 'Using zone: %s (ID: %d)', $user_zone->get_zone_name(), $user_zone->get_id() )
			);
		}

		$delivery_restriction = self::handle_need_shipping( $product, $is_related_product, $tracker );

		if ( ! empty( $delivery_restriction ) ) {
			if ( $tracker ) {
				$tracker->set_final_result( $delivery_restriction );
				$tracker->log_summary();
			}

			return $delivery_restriction;
		}

		[ $mapped_courier_methods, $mapped_apm_methods ] = ShippingMethodMapper::get_mapped_shipping_methods();

		$available_types = ShippingRatesResolver::get_available_methods_for_product(
			$product,
			$mapped_courier_methods,
			$mapped_apm_methods,
			$quantity,
			$tracker
		);

		$delivery_availability = array_combine(
			self::$inpost_method_types,
			array_map(
				static function ( $type ) use ( $available_types ) {
					return in_array( $type, $available_types, true );
				},
				self::$inpost_method_types
			)
		);

		$check_availability      = (bool) get_option( 'izi_shipping_check_shipping_availability', false );
		$is_inpost_plugin_active = is_plugin_active( 'inpost-for-woocommerce/woocommerce-inpost.php' );

		$inpost_restrictions = array();
		if ( $check_availability && $is_inpost_plugin_active ) {
			foreach ( self::$inpost_method_types as $type ) {
				if ( $delivery_availability[ $type ] ) {
					$inpost_restrictions = InpostProductRestrictions::get_inpost_restrictions( $product );
					break;
				}
			}
		} elseif ( $check_availability && ! $is_inpost_plugin_active && $tracker ) {
			$tracker->add_global_decision(
				'inpost_plugin_check',
				'InPost logistics plugin (inpost-for-woocommerce) is not active - skipping easypack_ restrictions'
			);
		}

		$inpost_methods = ShippingMethodMapper::get_inpost_methods();
		foreach ( $inpost_methods as $type => &$method ) {
			$method = array_filter( array_map( 'trim', explode( ',', $method ) ) );
		}
		unset( $method );

		$mapped_methods = ShippingMethodMapper::get_mapped_methods();

		foreach ( self::$inpost_method_types as $type ) {
			if ( ! $delivery_availability[ $type ] ) {
				continue;
			}

			if ( array_key_exists( $type, $inpost_restrictions ) ) {
				if ( null === $inpost_restrictions[ $type ] ) {
					continue;
				}

				$has_easypack_method = false;
				if ( isset( $inpost_methods[ $type ] ) ) {
					foreach ( $inpost_methods[ $type ] as $method ) {
						if ( InpostProductRestrictions::is_easypack_method( $method ) ) {
							$has_easypack_method = true;
							break;
						}
					}
				}

				if ( $has_easypack_method && '' !== $inpost_restrictions[ $type ] ) {
					$old_value                      = $delivery_availability[ $type ];
					$delivery_availability[ $type ] = $inpost_restrictions[ $type ];

					if ( $tracker && ( $old_value !== $delivery_availability[ $type ] ) ) {
						$tracker->add_decision(
							'inpost_restrictions',
							$type,
							$delivery_availability[ $type ],
							$delivery_availability[ $type ]
								? 'Method allowed by InPost product settings'
								: 'Method blocked by InPost product settings'
						);
					}
				}

				if ( isset( $mapped_methods[ $type ] ) ) {
					$mapped_method = $mapped_methods[ $type ];
					$is_valid      = false;
					$prefixes      = self::$valid_method_prefixes[ $type ];
					foreach ( $prefixes as $prefix ) {
						if ( 0 === strpos( $mapped_method, $prefix ) ) {
							$is_valid = true;
							break;
						}
					}
					if ( ! $is_valid ) {
						$delivery_availability[ $type ] = false;
						if ( $tracker ) {
							$tracker->add_decision(
								'invalid_method_prefix',
								$type,
								false,
								sprintf( 'Mapped method "%s" does not match valid prefixes', $mapped_method )
							);
						}
					}
				}
			}
		}

		/**
		 * Get from DI container.
		 *
		 * @var UnavailabilityService $unavailability_service
		 */
		$unavailability_service                 = inpost_pay_container()->get( UnavailabilityService::SERVICE_KEY );
		$unavailable_delivery_types_for_product = $unavailability_service->unavailable_delivery_types_for_product( $product );

		if ( UnavailableEntity::BOTH === $unavailable_delivery_types_for_product ) {
			foreach ( $delivery_availability as $type => $available ) {
				$delivery_availability[ $type ] = false;
				if ( $tracker ) {
					$tracker->add_decision(
						'unavailability_service',
						$type,
						false,
						'Blocked by UnavailabilityService (BOTH types)'
					);
				}
			}
		} elseif ( UnavailableEntity::APM === $unavailable_delivery_types_for_product ) {
			$delivery_availability['APM'] = false;
			if ( $tracker ) {
				$tracker->add_decision(
					'unavailability_service',
					'APM',
					false,
					'Blocked by UnavailabilityService'
				);
			}
		} elseif ( UnavailableEntity::COURIER === $unavailable_delivery_types_for_product ) {
			$delivery_availability['COURIER'] = false;
			if ( $tracker ) {
				$tracker->add_decision(
					'unavailability_service',
					'COURIER',
					false,
					'Blocked by UnavailabilityService'
				);
			}
		}

		if (
			! empty( $delivery_availability[ GroupInterface::DELIVERY_TYPE_CODE_APM ] )
			&& method_exists( $product, 'get_weight' )
		) {
			$weight          = (float) $product->get_weight();
			$weight_kg       = (float) wc_get_weight( $weight, 'kg' );
			$total_weight_kg = $weight_kg * (int) $quantity;

			if ( $total_weight_kg > self::$max_default_weight ) {
				$delivery_availability[ GroupInterface::DELIVERY_TYPE_CODE_APM ] = false;
				if ( $tracker ) {
					$tracker->add_decision(
						'weight_check',
						'APM',
						false,
						sprintf(
							'Product too heavy for APM: %.2f kg (max: %d kg)',
							$total_weight_kg,
							self::$max_default_weight
						),
						array(
							'weight_kg'     => $total_weight_kg,
							'max_weight_kg' => self::$max_default_weight,
						)
					);
				}
			}
		}

		if ( ! $is_related_product && ! in_array( false, $delivery_availability, true ) ) {
			if ( $tracker ) {
				$tracker->set_final_result( null );
				$tracker->log_summary();
			}

			return null;
		}

		Logger::log('RELATED PRODUCT: ' . var_export($is_related_product,true));

		if ( $is_related_product ) {
			$related_product_price = (float) $product->get_price();

			$free_deliveries = FreeShippingCalculator::get_free_shipping_methods(
				$mapped_courier_methods,
				$mapped_apm_methods,
				$related_product_price
			);

			$result = self::get_delivery_related_products( $delivery_availability, $free_deliveries );
			if ( $tracker ) {
				$tracker->set_final_result( $result );
				$tracker->log_summary();
			}

			return $result;
		}

		$result = self::get_delivery_product( $delivery_availability );
		if ( $tracker ) {
			$tracker->set_final_result( $result );
			$tracker->log_summary();
		}

		return $result;
	}

	// --- Public API: delegates to sub-classes for backward compatibility ---

	/**
	 * Prepares the current user's shipping zone context.
	 *
	 * Delegates to ShippingZoneResolver.
	 *
	 * @return \WC_Shipping_Zone
	 */
	public static function prepare_user_zone_context(): \WC_Shipping_Zone {
		return ShippingZoneResolver::prepare_user_zone_context();
	}

	/**
	 * Prepares a manual WooCommerce package for shipping zone matching.
	 *
	 * Delegates to ShippingZoneResolver.
	 *
	 * @param array $dest Destination data.
	 *
	 * @return array
	 */
	public static function prepare_manual_package( array $dest ): array {
		return ShippingZoneResolver::prepare_manual_package( $dest );
	}

	/**
	 * Sets the zone ID. Delegates to ShippingZoneResolver.
	 *
	 * @param int|null $zone_id Zone ID to set.
	 *
	 * @return void
	 */
	public static function set_zone_id( ?int $zone_id ): void {
		ShippingZoneResolver::set_zone_id( $zone_id );
	}

	/**
	 * Gets the zone ID. Delegates to ShippingZoneResolver.
	 *
	 * @return int|null
	 */
	public static function get_zone_id(): ?int {
		return ShippingZoneResolver::get_zone_id();
	}

	/**
	 * Gets the current cart gross total after discounts.
	 *
	 * Delegates to FreeShippingCalculator.
	 *
	 * @return float
	 */
	public static function get_cart_total(): float {
		return FreeShippingCalculator::get_cart_total();
	}

	/**
	 * Gets the free shipping threshold for a given InPost shipping method instance.
	 *
	 * Delegates to FreeShippingCalculator.
	 *
	 * @param string     $method_id        Shipping method ID.
	 * @param float|null $lowest_threshold Current lowest threshold.
	 *
	 * @return array Array with 'threshold' key.
	 */
	public static function get_inpost_free_shipping_threshold( string $method_id, ?float $lowest_threshold ): array {
		return FreeShippingCalculator::get_inpost_free_shipping_threshold( $method_id, $lowest_threshold );
	}

	// --- Private helpers ---

	/**
	 * Handles products that do not require physical shipping (virtual or downloadable).
	 *
	 * Returns a digital delivery response array for such products, or an empty
	 * array if the product requires physical shipping.
	 *
	 * @param mixed                            $product          Product.
	 * @param bool                             $is_related_product Whether this is a related product.
	 * @param DeliveryAvailabilityTracker|null $tracker          Decision tracker.
	 *
	 * @return array Digital delivery response or empty array.
	 */
	private static function handle_need_shipping(
		$product,
		bool $is_related_product,
		?DeliveryAvailabilityTracker $tracker
	): array {
		if ( $product && ( $product->is_virtual() || $product->is_downloadable() ) ) {
			if ( $tracker ) {
				$tracker->add_global_decision(
					'product_type_check',
					sprintf(
						'Product is %s - no physical shipping needed',
						$product->is_virtual() ? 'virtual' : 'downloadable'
					)
				);
			}
			$response = array(
				array(
					'delivery_type'         => DigitalProduct::DELIVERY_TYPE_DIGITAL,
					'if_delivery_available' => true,
				),
			);

			if ( $is_related_product ) {
				$response[0]['if_delivery_free'] = false;
			}

			return $response;
		}

		return array();
	}

	/**
	 * Formats delivery availability data for related products.
	 *
	 * @param array $delivery_availability Delivery availability by type.
	 * @param array $free_deliveries       Free delivery flags by type.
	 *
	 * @return array Formatted delivery options for related products.
	 */
	private static function get_delivery_related_products( array $delivery_availability, array $free_deliveries ): array {
		$delivery_related_products = array();

		$cart_available_methods = ShippingRatesResolver::get_delivery_types_for_cart();

		foreach ( self::$inpost_method_types as $type ) {
			$if_delivery_available = $delivery_availability[ $type ] ?? true;
			$is_available_for_cart = in_array( $type, $cart_available_methods, true );
			$is_free               = ( $if_delivery_available && $is_available_for_cart )
				? ( $free_deliveries[ $type ] ?? false )
				: false;

			// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_var_export
			Logger::log( '(get_delivery_related_products) FREE_DELIVERIES: ' . var_export( $free_deliveries, true ) );
			// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_var_export
			Logger::log( '(get_delivery_related_products) IF_DELIVERY_FREE: ' . var_export( $is_free, true ) );

			$delivery_related_products[] = array(
				'delivery_type'         => $type,
				'if_delivery_available' => $if_delivery_available,
				'if_delivery_free'      => $is_free,
			);
		}

		return $delivery_related_products;
	}

	/**
	 * Formats delivery availability data for regular products.
	 *
	 * @param array $delivery_availability Delivery availability by type.
	 *
	 * @return array Formatted delivery options.
	 */
	private static function get_delivery_product( array $delivery_availability ): array {
		$delivery_products = array();
		foreach ( $delivery_availability as $type => $is_available ) {
			$delivery_products[] = array(
				'delivery_type'         => $type,
				'if_delivery_available' => $is_available,
			);
		}

		return $delivery_products;
	}
}
