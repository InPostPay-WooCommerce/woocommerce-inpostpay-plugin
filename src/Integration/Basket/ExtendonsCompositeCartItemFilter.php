<?php

namespace Ilabs\Inpost_Pay\Integration\Basket;

use WC_Cart;

class ExtendonsCompositeCartItemFilter extends AbstractCartItemFilter implements CartItemFilterInterface {

	public function canAddCartItem( array $cartItemContent ): bool {
		if ( isset( $cartItemContent['bundled_by'] ) && is_string( $cartItemContent['bundled_by'] ) ) {

			return false;
		}

		return true;
	}

	public function resolveParentBundleProductId( array $cartItemContent
	): ?int {
		if ( isset( $cartItemContent['bundled_by'] ) && is_string( $cartItemContent['bundled_by'] ) ) {

			return ( (int) $cartItemContent['bundled_by'] );
		}

		return null;
	}
}
