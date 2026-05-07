<?php
/**
 * PPOM Cart Helper
 *
 * Handles cart content fixes and rehydration for WooCommerce Product Add-Ons (PPOM) integration.
 *
 * @package Ilabs\Inpost_Pay\Integration\PPOM
 */

namespace Ilabs\Inpost_Pay\Integration\PPOM;

use Ilabs\Inpost_Pay\WooCommerce\Price\BasketPriceCalculator;

/**
 * Class PpomCartHelper
 *
 * Provides utilities for fixing cart contents when PPOM plugin is active.
 */
final class PpomCartHelper {
	/**
	 * PPOM plugin file path.
	 *
	 * @var string
	 */
	private const PPOM_PLUGIN_FILE = 'woocommerce-product-addon/woocommerce-product-addon.php';

	/**
	 * Maybe fix cart contents after totals calculation.
	 *
	 * Ensures cart items are properly structured when PPOM is active and contains
	 * custom fields with pricing data.
	 *
	 * @param array $cartContents Cart contents array.
	 * @return array Fixed cart contents.
	 */
	public static function maybe_fix_cart_contents_after_totals( array $cartContents ): array {
		if ( empty( $cartContents ) ) {
			return $cartContents;
		}

		if ( ! self::is_ppom_active() ) {
			return $cartContents;
		}

		if ( ! self::cart_has_ppom_markers( $cartContents ) ) {
			return $cartContents;
		}

		BasketPriceCalculator::$totalsCalculatedInThisRequest = true;

		$fresh = self::get_fresh_cart();
		if ( ! empty( $fresh ) ) {
			$cartContents = self::ensure_cart_item_keys( $fresh );
		} else {
			$cartContents = self::ensure_cart_item_keys( $cartContents );
		}

		$rehydrateKey = self::find_ppom_item_requiring_rehydrate( $cartContents );
		if ( null === $rehydrateKey ) {
			return $cartContents;
		}

		if ( ! self::can_rehydrate_cart() ) {
			return $cartContents;
		}

		self::rehydrate_cart_from_session();

		BasketPriceCalculator::$totalsCalculatedInThisRequest = true;

		$fresh = self::get_fresh_cart();
		if ( ! empty( $fresh ) ) {
			return self::ensure_cart_item_keys( $fresh );
		}

		return $cartContents;
	}

	/**
	 * Check if PPOM plugin is active.
	 *
	 * @return bool True if PPOM is active, false otherwise.
	 */
	private static function is_ppom_active(): bool {
		if ( ! function_exists( 'is_plugin_active' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}
		return function_exists( 'is_plugin_active' ) && is_plugin_active( self::PPOM_PLUGIN_FILE );
	}

	/**
	 * Check if cart has PPOM markers.
	 *
	 * Determines if any cart item contains PPOM-specific data fields.
	 *
	 * @param array $cartContents Cart contents array.
	 * @return bool True if PPOM markers found, false otherwise.
	 */
	private static function cart_has_ppom_markers( array $cartContents ): bool {
		foreach ( $cartContents as $item ) {
			if ( isset( $item['ppom']['fields'] ) || isset( $item['ppom'] ) ) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Find PPOM item requiring rehydration.
	 *
	 * Searches for cart items with PPOM pricing data that needs session rehydration.
	 *
	 * @param array $cartContents Cart contents array.
	 * @return string|null Cart item key if found, null otherwise.
	 */
	private static function find_ppom_item_requiring_rehydrate( array $cartContents ): ?string {
		foreach ( $cartContents as $cart_item_key => $item ) {
			if ( empty( $item['ppom']['fields'] ) ) {
				continue;
			}

			$raw = $item['ppom']['ppom_option_price'] ?? null;
			if ( ! is_string( $raw ) || '' === $raw ) {
				continue;
			}

			if (
				preg_match( '/"price"\s*:\s*"?([1-9][0-9]*)(\.[0-9]+)?"?/i', $raw ) ||
				preg_match( '/\\\\"price\\\\"\\s*:\\s*\\\\"?([1-9][0-9]*)(\\.[0-9]+)?\\\\"?/i', $raw )
			) {
				return (string) $cart_item_key;
			}
		}

		return null;
	}

	/**
	 * Check if cart can be rehydrated.
	 *
	 * Verifies that WooCommerce cart object and required methods exist.
	 *
	 * @return bool True if rehydration is possible, false otherwise.
	 */
	private static function can_rehydrate_cart(): bool {
		return function_exists( 'WC' )
				&& WC()
				&& WC()->cart
				&& method_exists( WC()->cart, 'set_session' )
				&& method_exists( WC()->cart, 'get_cart_from_session' );
	}

	/**
	 * Rehydrate cart from session.
	 *
	 * Refreshes cart data from session and recalculates totals.
	 *
	 * @return void
	 */
	private static function rehydrate_cart_from_session(): void {
		WC()->cart->set_session();
		WC()->cart->get_cart_from_session();
		WC()->cart->calculate_totals();
	}

	/**
	 * Get fresh cart contents.
	 *
	 * Retrieves current cart contents from WooCommerce cart object.
	 *
	 * @return array Cart contents or empty array if cart is unavailable.
	 */
	private static function get_fresh_cart(): array {
		if ( ! function_exists( 'WC' ) || ! WC() || ! WC()->cart ) {
			return array();
		}
		return (array) WC()->cart->get_cart();
	}

	/**
	 * Ensure cart item keys are present.
	 *
	 * Adds 'key' field to each cart item if missing.
	 *
	 * @param array $cartContents Cart contents array.
	 * @return array Cart contents with keys ensured.
	 */
	private static function ensure_cart_item_keys( array $cartContents ): array {
		foreach ( $cartContents as $cart_item_key => $item ) {
			if ( ! isset( $item['key'] ) ) {
				$cartContents[ $cart_item_key ]['key'] = $cart_item_key;
			}
		}
		return $cartContents;
	}

	/**
	 * Maybe split long product attributes for PPOM products.
	 *
	 * When PPOM is active, attribute values (e.g. combined option lists) may exceed
	 * the InPost Pay API character limit. This method splits them into multiple
	 * attributes so that no single value is too long and no option is cut mid-text.
	 *
	 * @param array $products Array of ProductInterface objects.
	 *
	 * @return array Products with split attributes where needed.
	 */
	public static function maybe_split_product_attributes( array $products ): array {
		if ( ! self::is_ppom_active() ) {
			return $products;
		}

		$splitter = new ProductAttributeSplitter();

		foreach ( $products as $product ) {
			$product->set_product_attributes(
				$splitter->process( $product->get_product_attributes() )
			);
		}

		return $products;
	}
}
