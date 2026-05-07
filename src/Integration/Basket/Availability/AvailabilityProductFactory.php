<?php

namespace Ilabs\Inpost_Pay\Integration\Basket\Availability;

class AvailabilityProductFactory {
	/**
	 * @throws ProductIsEmptyException
	 */
	public function create( $cart_item ): AvailabilityIntegrationInterface {
		// WPC Product Bundles - silently exclude bundle parent and child items from basket payload.
		// Bundle parent carries a negative promo price (discount value) which InPost API rejects.
		if ( is_array( $cart_item )
			&& ( isset( $cart_item['woosb_ids'] ) || isset( $cart_item['woosb_parent_id'] ) )
			&& is_plugin_active( 'woo-product-bundle/wpc-product-bundles.php' ) ) {
			return new WpcBundleAvailabilityIntegration( $cart_item );
		}

		if ( is_array( $cart_item ) && is_plugin_active( 'product-extras-for-woocommerce/product-extras-for-woocommerce.php' ) ) {
			return new ProductExtrasForWoocommerceAvailabilityIntegration( $cart_item );
		}

		return new GenericAvailabilityIntegration( $cart_item );
	}
}
