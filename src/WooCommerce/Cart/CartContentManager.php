<?php

namespace Ilabs\Inpost_Pay\WooCommerce\Cart;

use Ilabs\Inpost_Pay\Logger;

class CartContentManager {
	/**
	 * Get cart contents or empty array if cart is empty
	 *
	 * @param object $wooCommerce The WooCommerce instance
	 *
	 * @return array Cart contents
	 */
	public function getCartContents( object $wooCommerce ): array {
		return ( ! WC()->cart || WC()->cart->is_empty() ) ? array() : $wooCommerce->cart->cart_contents;
	}

	/**
	 * Refresh cart contents for special product types
	 *
	 * @param array $cartContents The current cart contents
	 * @param array $wp_actions WordPress actions array (passed by reference)
	 * @return array Updated cart contents
	 */
	public function refreshCartContents( array $cartContents, array &$wp_actions ): array {
		$hasThemeCompleteItems = false;

		foreach ( $cartContents as $key => $item ) {
			if ( empty( $item['tmhasepo'] ) ) {
				continue;
			}

			if ( ! isset( $item['tm_epo_options_static_prices_first'] ) && class_exists( 'THEMECOMPLETE_EPO_Cart' ) ) {
				$epoCart              = \THEMECOMPLETE_EPO_Cart::instance();
				$cartContents[ $key ] = $epoCart->add_cart_item( $item, $key );
			}

			$cartContents[ $key ]['tc_recalculate'] = true;
			$hasThemeCompleteItems                  = true;
		}

		if ( $hasThemeCompleteItems && isset( $wp_actions['woocommerce_before_calculate_totals'] ) ) {
			unset( $wp_actions['woocommerce_before_calculate_totals'] );
		}

		\WC()->cart->cart_contents = $cartContents;

		return $cartContents;
	}
}
