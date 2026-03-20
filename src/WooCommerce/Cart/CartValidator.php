<?php

namespace Ilabs\Inpost_Pay\WooCommerce\Cart;

use Ilabs\Inpost_Pay\Integration\Basket\Availability\AvailabilityProductFactory;
use Ilabs\Inpost_Pay\Integration\Basket\Availability\ProductIsEmptyException;
use WC_Product;

class CartValidator {
	/**
	 * Checks if a product can be added to the basket
	 *
	 * @param array $cart_item The cart item to check
	 *
	 * @return bool True if the product can be added, false otherwise
	 */
	public function canAddProduct( array $cart_item ): bool {
		try {
			$availability = ( new AvailabilityProductFactory() )->create( $cart_item );
		} catch ( ProductIsEmptyException $e ) {
			return false;
		}

		return $availability->checkAvailability();
	}

	/**
	 * Checks product availability by product instance
	 *
	 * @param WC_Product|null $product The product to check
	 *
	 * @return bool True if the product is available, false otherwise
	 */
	public static function checkAvailabilityByProduct( ?WC_Product $product ): bool {
		return $product !== null && $product->is_purchasable() && $product->is_in_stock() && $product->is_visible();
	}

	/**
	 * Checks if product can be suggested
	 *
	 * @param WC_Product|null $product The product to check
	 *
	 * @return bool True if the product can be suggested, false otherwise
	 */
	public static function canBeSuggestedProduct( ?WC_Product $product ): bool {
		return self::checkAvailabilityByProduct( $product ) && ! $product->is_type( 'variable' );
	}
}
