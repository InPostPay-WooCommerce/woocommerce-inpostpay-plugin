<?php

namespace Ilabs\Inpost_Pay\Integration\Basket\Availability;

use Ilabs\Inpost_Pay\Logger;
use WC_Product;

abstract class AbstractAvailabilityIntegration implements AvailabilityIntegrationInterface {

	protected array $cart_item;

	protected ?WC_Product $product = null;

	/**
	 * @param array|WC_Product $cart_item
	 *
	 * @throws ProductIsEmptyException
	 */
	public function __construct( $cart_item ) {
		if ( $cart_item instanceof WC_Product ) {
			$this->product = $cart_item;
			return;
		}
		$this->cart_item = $cart_item;
		$this->setProduct();
		if ( $this->isEmpty() ) {
			throw new ProductIsEmptyException( $cart_item );
		}
	}

	/**
	 * Checks if the product is empty.
	 *
	 * This method checks if the product property is empty. If it is, it logs a debug message.
	 *
	 * @return bool True if the product is empty, false otherwise.
	 */
	public function isEmpty(): bool {
		if ( empty( $this->product ) ) {
			Logger::debug( '[Add to Basket] Empty product' );

			return true;
		}

		return false;
	}

	/**
	 * Check if the product is purchasable.
	 *
	 * This method checks if the product is purchasable.
	 *
	 * @return bool
	 */
	public function isPurchasable(): bool {
		if ( ! $this->product->is_purchasable() ) {
			Logger::debug( '[Add to Basket] Product is not purchasable ' . $this->product->get_id() );

			return false;
		}

		return true;
	}

	/**
	 * Check if the product is in stock.
	 *
	 * This method checks if product is in stock.
	 *
	 * @return bool
	 */
	public function isInStock(): bool {
		if ( ! $this->product->is_in_stock() ) {
			Logger::debug( '[Add to Basket] Product is out of stock ' . $this->product->get_id() );

			return false;
		}

		return true;
	}

	/**
	 * Sets the product property from the cart item if available.
	 *
	 * This method checks if the 'data' key in the cart item is set and is an instance
	 * of WC_Product. If so, it assigns the product to the product property.
	 *
	 * @return void
	 */
	private function setProduct(): void {
		if ( isset( $this->cart_item['data'] ) && $this->cart_item['data'] instanceof WC_Product ) {
			$this->product = $this->cart_item['data'];
		}
	}

	/**
	 * Check if product is available to buy.
	 *
	 * This method checks if product is in stock, purchasable and visible.
	 *
	 * @return bool
	 */
	public function checkAvailability(): bool {
		if ( ! $this->isInStock() ) {
			return false;
		}

		if ( ! $this->isPurchasable() ) {
			return false;
		}

		return true;
	}
}
