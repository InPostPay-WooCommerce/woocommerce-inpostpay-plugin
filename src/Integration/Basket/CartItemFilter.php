<?php

namespace Ilabs\Inpost_Pay\Integration\Basket;

use WC_Cart;

class CartItemFilter {

	public function canAddCartItem( array $cartItemContent ): bool {
		foreach ( $this->getCartItemFilterInterfaces() as $cartItemFilterInterface ) {
			if ( ! $cartItemFilterInterface->canAddCartItem( $cartItemContent ) ) {
				return false;
			}
		}

		return true;
	}


	/**
	 * @return CartItemFilterInterface[]
	 */
	public function getCartItemFilterInterfaces(): array {
		return [
			new SmartCompositeCartItemFilter(),
			new ExtendonsCompositeCartItemFilter(),
		];
	}
}
