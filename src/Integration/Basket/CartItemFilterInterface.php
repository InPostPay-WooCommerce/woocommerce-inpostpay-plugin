<?php

namespace Ilabs\Inpost_Pay\Integration\Basket;

use WC_Cart;

interface CartItemFilterInterface {

	public function canAddCartItem( array $cartItemContent ): bool;

	public function resolveParentBundleProductId( array $cartItemContent
	): ?int;
}
