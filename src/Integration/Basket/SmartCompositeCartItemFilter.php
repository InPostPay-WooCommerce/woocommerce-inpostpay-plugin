<?php

namespace Ilabs\Inpost_Pay\Integration\Basket;

use WC_Cart;

class SmartCompositeCartItemFilter extends AbstractCartItemFilter implements CartItemFilterInterface {

	public function canAddCartItem( array $cartItemContent ): bool {
		if ( isset( $cartItemContent['wooco_parent_id'] ) && (int) $cartItemContent['wooco_parent_id'] > 0 ) {

			return false;
		}

		return true;
	}

	public function resolveParentBundleProductId( array $cartItemContent
	): ?int {
		if ( isset( $cartItemContent['wooco_parent_id'] ) && (int) $cartItemContent['wooco_parent_id'] > 0 ) {

			return (int) $cartItemContent['wooco_parent_id'];
		}

		return null;
	}
}
