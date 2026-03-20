<?php

namespace Ilabs\Inpost_Pay\Integration\Basket;

use WC_Cart;

class BundledItemFactory {

	public static function create( array $rawCartItemData, WC_Cart $cart ) {
		foreach ( ( new CartItemFilter() )->getCartItemFilterInterfaces() as $cartItemFilterInterface ) {
			$parentBundleProductId = $cartItemFilterInterface->resolveParentBundleProductId( $rawCartItemData );
			if ( $parentBundleProductId ) {

				return new BundledItem( $rawCartItemData,
					$parentBundleProductId,
					$cartItemFilterInterface,
					$cart );
			}
		}

		return false;
	}
}
