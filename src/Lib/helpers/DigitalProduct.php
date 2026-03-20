<?php

namespace Ilabs\Inpost_Pay\Lib\helpers;

use Ilabs\Inpost_Pay\Lib\item\Delivery;
use Ilabs\Inpost_Pay\Lib\item\DigitalDelivery;

class DigitalProduct {

	public const DELIVERY_TYPE_DIGITAL = 'DIGITAL';
	/**
	 * Add/Remove the digital product flag in the cart session.
	 *
	 * During the BasketChange hook, we check if there are any digital products in the cart.
	 * If there are, we set a flag in the cart session. This flag is used later to determine
	 * if we should redirect to the Basket or Checkout page.
	 */
	public static function handleDigitalProduct() : void {
		// Guard: Don't access cart/session if not initialized
		if ( ! WC()->session || ! WC()->cart ) {
			return;
		}

		$has_digital = false;

		foreach ( WC()->cart->get_cart() as $item ) {
			$product = $item['data'];

			if ( $product->is_virtual() || $product->is_downloadable() ) {
				$has_digital = true;
				break;
			}
		}

		WC()->session->set( 'cart_has_digital_products', $has_digital );
	}

	/**
	 * Return true if the cart has any digital products.
	 *
	 * @return bool
	 */
	public static function basketHasDigitalProduct() : bool {
		// Guard: Don't access session if not initialized
		if ( ! WC()->session ) {
			return false;
		}
		return WC()->session->get( 'cart_has_digital_products', false );
	}

	/**
	 * Adds a digital delivery method to the array of available shipping methods.
	 *
	 * If the cart contains any digital products, this method adds a new delivery
	 * method to the array of available shipping methods. The new delivery method
	 * is an instance of the `Delivery` class and has its `delivery_type` set to
	 * `DIGITAL`.
	 *
	 * @param Delivery[] $methods Array of available shipping methods.
	 *
	 * @return Delivery[] Array of available shipping methods with the digital
	 *                    delivery method added if the cart contains any digital
	 *                    products.
	 */
	public static function addDigitalDeliveryMethod( $methods ): array {
		self::handleDigitalProduct();
		if ( ! self::basketHasDigitalProduct() ) {
			return $methods;
		}
		$digital_delivery_method                 = new DigitalDelivery();
		$methods[]                               = $digital_delivery_method;

		return $methods;
	}
}
