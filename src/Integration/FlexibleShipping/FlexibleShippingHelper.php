<?php
/**
 * Flexible Shipping Helper
 *
 * Helper class for detecting and handling Flexible Shipping methods.
 *
 * @package Ilabs\Inpost_Pay\Integration\FlexibleShipping
 */

namespace Ilabs\Inpost_Pay\Integration\FlexibleShipping;

use WC_Shipping_Method;

/**
 * Class FlexibleShippingHelper
 *
 * Handles Flexible Shipping specific logic, particularly free shipping requirements.
 */
class FlexibleShippingHelper {

	/**
	 * Service key for the container.
	 *
	 * @var string
	 */
	public const SERVICE_KEY = 'service.flexible_shipping_helper';

	/**
	 * Flexible Shipping method ID prefix.
	 *
	 * @var string
	 */
	public const FLEXIBLE_SHIPPING_METHOD_ID = 'flexible_shipping';

	/**
	 * Free shipping requires setting key.
	 *
	 * @var string
	 */
	public const FREE_SHIPPING_REQUIRES_KEY = 'free_shipping_requires';

	/**
	 * Cache for supports_coupon_free_shipping results.
	 *
	 * @var array<string, bool>
	 */
	private array $cache = [];

	/**
	 * Checks if the given shipping method is a Flexible Shipping method.
	 *
	 * @param WC_Shipping_Method $method Shipping method instance.
	 *
	 * @return bool True if method is Flexible Shipping, false otherwise.
	 */
	public function is_flexible_shipping( WC_Shipping_Method $method ): bool {
		return 0 === strpos( $method->id, self::FLEXIBLE_SHIPPING_METHOD_ID );
	}

	/**
	 * Checks if the Flexible Shipping method supports coupon-based free shipping.
	 *
	 * This method verifies if the shipping method:
	 * 1. Is a Flexible Shipping method
	 * 2. Has the free_shipping_requires setting (PRO version)
	 * 3. Has a value that includes 'coupon' logic
	 *
	 * IMPORTANT: This method returns NULL for non-Flexible-Shipping methods.
	 *
	 * @param WC_Shipping_Method $method Shipping method instance.
	 *
	 * @return bool|null True if supports coupons, false if doesn't, null if not FS.
	 */
	public function supports_coupon_free_shipping( WC_Shipping_Method $method ): ?bool {
		if ( ! $this->is_flexible_shipping( $method ) ) {
			return null;
		}

		// Cache key based on method instance ID.
		$cache_key = $method->id . ':' . $method->instance_id;

		if ( isset( $this->cache[ $cache_key ] ) ) {
			return $this->cache[ $cache_key ];
		}

		$free_shipping_requires = $this->get_free_shipping_requires( $method );

		if ( null === $free_shipping_requires ) {
			$this->cache[ $cache_key ] = false;
			return false;
		}

		$coupon_based_values = [
			'coupon',
			'order_amount_or_coupon',
			'order_amount_and_coupon',
		];

		$result = in_array( $free_shipping_requires, $coupon_based_values, true );
		$this->cache[ $cache_key ] = $result;

		return $result;
	}

	/**
	 * Gets the free_shipping_requires setting value from Flexible Shipping method.
	 *
	 * @param WC_Shipping_Method $method Shipping method instance.
	 *
	 * @return string|null Setting value or null if not found.
	 */
	private function get_free_shipping_requires( WC_Shipping_Method $method ): ?string {
		if ( ! isset( $method->instance_settings ) || ! is_array( $method->instance_settings ) ) {
			return null;
		}

		if ( ! isset( $method->instance_settings[ self::FREE_SHIPPING_REQUIRES_KEY ] ) ) {
			return null;
		}

		$value = $method->instance_settings[ self::FREE_SHIPPING_REQUIRES_KEY ];

		return ! empty( $value ) ? (string) $value : null;
	}

	/**
	 * Gets detailed information about Flexible Shipping method configuration.
	 *
	 * Useful for debugging and logging.
	 *
	 * @param WC_Shipping_Method $method Shipping method instance.
	 *
	 * @return array Array with method details.
	 */
	public function get_method_info( WC_Shipping_Method $method ): array {
		return [
			'is_flexible_shipping'           => $this->is_flexible_shipping( $method ),
			'method_id'                      => $method->id,
			'instance_id'                    => $method->instance_id,
			'free_shipping_requires'         => $this->get_free_shipping_requires( $method ),
			'supports_coupon_free_shipping'  => $this->supports_coupon_free_shipping( $method ),
		];
	}

	/**
	 * Clears the internal cache.
	 *
	 * Useful for testing or when shipping methods are modified during runtime.
	 *
	 * @return void
	 */
	public function clear_cache(): void {
		$this->cache = [];
	}
}
