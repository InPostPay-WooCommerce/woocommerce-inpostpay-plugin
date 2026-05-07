<?php
/**
 * WPC Product Bundles availability integration for InPost Pay.
 *
 * Handles exclusion of WPC Bundle parent and child items from the basket payload
 * and provides utility to remove bundle items from the WooCommerce cart
 * before order creation.
 *
 * @package Ilabs\Inpost_Pay\Integration\Basket\Availability
 */

namespace Ilabs\Inpost_Pay\Integration\Basket\Availability;

/**
 * Class WpcBundleAvailabilityIntegration
 *
 * Silently excludes WPC Product Bundle items (parent and children) from
 * the InPost Pay basket payload. Bundle parent carries a negative promo
 * price (discount value) which the InPost API rejects.
 *
 * @since 2.0.8
 */
class WpcBundleAvailabilityIntegration extends AbstractAvailabilityIntegration {

	/**
	 * Constructor.
	 *
	 * @param array $cart_item WooCommerce cart item data.
	 *
	 * @throws ProductIsEmptyException
	 */
	public function __construct( $cart_item ) {
		parent::__construct( $cart_item );
	}

	/**
	 * Check product availability.
	 *
	 * Always returns false — bundle items should be excluded from the
	 * InPost Pay basket payload entirely.
	 *
	 * @return bool Always false.
	 */
	public function checkAvailability(): bool {
		return false;
	}

	/**
	 * Remove WPC Product Bundle parent and child items from the WooCommerce cart.
	 *
	 * Should be called after restoring the cart session and before stock validation
	 * during InPost Pay order creation. Bundle parent items fail WooCommerce stock
	 * checks because they don't represent real inventory.
	 *
	 * @return void
	 */
	public static function maybe_delete_wpc_products(): void {
		if ( ! is_plugin_active( 'woo-product-bundle/wpc-product-bundles.php' ) ) {
			return;
		}

		$cart = WC()->cart;

		if ( ! $cart ) {
			return;
		}

		$removed = false;

		foreach ( $cart->get_cart() as $cart_item_key => $cart_item ) {
			if ( isset( $cart_item['woosb_ids'] ) ) {
				$cart->remove_cart_item( $cart_item_key );
				$removed = true;
			}

			if ( isset( $cart_item['woosb_parent_id'] ) ) {
				$cart->remove_cart_item( $cart_item_key );
				$removed = true;
			}
		}

		if ( $removed ) {
			$cart->calculate_totals();
		}
	}
}
