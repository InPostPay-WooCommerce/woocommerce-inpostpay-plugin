<?php

namespace Ilabs\Inpost_Pay\Integration\Basket;

use WC_Cart;

class BundledItem {

	private array $rawCartItemData;

	private int $parentProductId;

	private CartItemFilterInterface $filter;

	private WC_Cart $cart;

	/**
	 * @param array $rawCartItemData
	 * @param int $parentProductId
	 * @param CartItemFilterInterface $filter
	 * @param WC_Cart $cart
	 */
	public function __construct(
		array $rawCartItemData,
		int $parentProductId,
		CartItemFilterInterface $filter,
		WC_Cart $cart
	) {
		$this->rawCartItemData = $rawCartItemData;
		$this->filter          = $filter;
		$this->parentProductId = $parentProductId;
		$this->cart            = $cart;
	}

	public function removeParentWithBundledItems(): void {
		foreach ( $this->cart->get_cart() as $cart_item_key => $item ) {
			if ( ( $item['product_id'] ) == $this->parentProductId ) {
				$this->cart->remove_cart_item( $cart_item_key );

			}
		}
	}

	public function getRawCartItemData(): array {
		return $this->rawCartItemData;
	}

	public function getParentProductId(): int {
		return $this->parentProductId;
	}

	public function getCart(): WC_Cart {
		return $this->cart;
	}

	public function getFilter(): CartItemFilterInterface {
		return $this->filter;
	}
}
