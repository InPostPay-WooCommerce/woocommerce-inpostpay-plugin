<?php

namespace Ilabs\Inpost_Pay\Integration\Shipping;

use Ilabs\Inpost_Pay\Lib\config\ShippingCost\ShippingAddTax;
use WC_Order;

abstract class AbstractShippingMethodIntegration {

	protected string $iziDeliveryMethodId;
	protected WC_Order $order;

	public function configure() {

		if ( $this instanceof ParcelLockerIntegrationInterface ) {
			$_POST[ $this->getFormFieldParcelLockerId() ] = $this->getParcelLockerId();
		}
	}

	public function getIziDeliveryMethodId(): string {
		return $this->iziDeliveryMethodId;
	}

	public function setWcOrder( WC_Order $order ) {
		$this->order = $order;
	}

	public function filterTotal(
		callable $callable,
		$isOrderGroupTaxed,
		object $basketPriceObj
	) {
		$shipping_items      = $this->order->get_items( 'shipping' );
		$iziBasketPriceGross = floatval( $basketPriceObj->gross );

		$this->order->calculate_totals( false );
		$orderTotal = floatval( $this->order->get_total() );
		// $orderTotalMinusIziBasketPrice = $orderTotal - $basketPriceGross;

		$this->order->update_taxes();
		$shipping_tax = floatval( $this->order->get_shipping_tax() );

		foreach ( $shipping_items as $shippingItem ) {
			$current_total = $shippingItem->get_total();
			$iziTotal      = $callable( $current_total );

			if ( $iziBasketPriceGross > $orderTotal ) {
				$newTotal  = $iziBasketPriceGross - $orderTotal;
				$newTotal += $current_total;

				// 38 - 28
				// 27.7 +

			}

			if ( $orderTotal > $iziBasketPriceGross ) {
				$newTotal = $current_total - ( $orderTotal - $iziBasketPriceGross );

				if ( $isOrderGroupTaxed ) {
					$newTotal += $shipping_tax;
				}
			}

			if ( $orderTotal === $iziBasketPriceGross ) {
				$newTotal = $current_total;
			}

			$newTotalWithoutTax = $newTotal;

			// $new_total -= $shipping_tax;

			$shippingItem->set_total( $newTotal );
			$shippingItem->apply_changes();

			// $basketDeliveryFromCache = json_decode( $basket )->delivery;

			/*
			if ( 'taxable' !== $shipping_item->get_tax_status() ) {
				$shipping_item->set_total( (float) $new_total - (float) $shipping_tax );
				$shipping_item->save();
			}*/
		}

		/*
		if ( $shipping_tax > 0 && $isTaxed ) {
			$this->order->set_shipping_tax( $shipping_tax );
		} else {
			$this->order->set_shipping_tax( 0 );
		}*/

		$this->order->save();

		// $this->order->update_taxes();
		$this->order->calculate_totals( false );
		$this->order->save();
	}
}
