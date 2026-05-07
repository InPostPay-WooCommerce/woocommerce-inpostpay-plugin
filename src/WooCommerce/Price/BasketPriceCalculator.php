<?php

namespace Ilabs\Inpost_Pay\WooCommerce\Price;

use Ilabs\Inpost_Pay\Lib\item\Price;
use Ilabs\Inpost_Pay\Lib\Transformers\BasketProductTransformer;
use Ilabs\Inpost_Pay\Logger;

class BasketPriceCalculator {
	public static bool $totalsCalculatedInThisRequest = false;

	private float $basketBasePriceNet    = 0.0;
	private float $basketBasePriceGross  = 0.0;
	private float $basketBasePriceVat    = 0.0;
	private float $basketPromoPriceNet   = 0.0;
	private float $basketPromoPriceGross = 0.0;
	private float $basketPromoPriceVat   = 0.0;

	/**
	 * Accumulates product prices into basket totals
	 *
	 * @param BasketProductTransformer $transformer The product transformer
	 */
	public function accumulateProductPrices( BasketProductTransformer $transformer ): void {
		// Base prices
		$this->basketBasePriceNet   += $transformer->get_item_price_net();
		$this->basketBasePriceGross += $transformer->get_item_price_gross();
		$this->basketBasePriceVat   += $transformer->get_item_price_vat();

		// Promo prices
		$this->basketPromoPriceNet   += $transformer->get_item_promo_price_net();
		$this->basketPromoPriceGross += $transformer->get_item_promo_price_gross();
		$this->basketPromoPriceVat   += $transformer->get_item_promo_price_vat();

		Logger::log(
			'[1GR_DEBUG] accumulate item_vat=' . $transformer->get_item_price_vat()
			. ' running_sum_vat=' . $this->basketBasePriceVat
			. ' item_promo_vat=' . $transformer->get_item_promo_price_vat()
			. ' running_sum_promo_vat=' . $this->basketPromoPriceVat
		);
	}

	/**
	 * Reads the summary basket base price
	 *
	 * @return Price The basket base price
	 */
	public function readSummaryBasketBasePrice(): Price {
		$price = new Price();
		$price
			->set_gross( number_format( $this->basketBasePriceGross, 2, '.', '' ) )
			->set_net( $this->basketBasePriceNet )
			->set_vat( number_format( $this->basketBasePriceVat, 2, '.', '' ) );

		return $price;
	}

	/**
	 * Reads the summary basket promo price
	 *
	 * @return Price The basket promo price
	 */
	public function readSummaryBasketPromoPrice(): Price {
		$price = new Price();
		$price
			->set_gross( number_format( $this->basketPromoPriceGross, 2, '.', '' ) )
			->set_net( $this->basketPromoPriceNet )
			->set_vat( number_format( $this->basketPromoPriceVat, 2, '.', '' ) );

		return $price;
	}

	/**
	 * Reads the summary basket final price
	 *
	 * @param Price $promoPrice The promo price
	 *
	 * @return Price The basket final price
	 */
	public function readSummaryBasketFinalPrice( Price $promoPrice ): Price {
		$price = new Price();
		if ( ! self::$totalsCalculatedInThisRequest ) {
			WC()->cart->calculate_totals();
			self::$totalsCalculatedInThisRequest = true;
		}

		$couponsNetWorth = array_sum( WC()->cart->get_coupon_discount_totals() );
		$couponsTaxWorth = array_sum( WC()->cart->get_coupon_discount_tax_totals() );

		$wc_cart_total     = WC()->cart->get_total( 'edit' );
		$wc_cart_tax_total = WC()->cart->get_total_tax();
		Logger::log( '[1GR_DEBUG] WC cart get_total=' . $wc_cart_total . ' get_total_tax=' . $wc_cart_tax_total );
		Logger::log( '[1GR_DEBUG] promoPrice gross=' . $promoPrice->get_gross() . ' net=' . $promoPrice->get_net() . ' vat=' . $promoPrice->get_vat() );
		Logger::log( '[1GR_DEBUG] accumulated promo_vat=' . $this->basketPromoPriceVat . ' couponsNetWorth=' . $couponsNetWorth . ' couponsTaxWorth=' . $couponsTaxWorth );

		$gross = number_format( $promoPrice->get_gross() - $couponsNetWorth - $couponsTaxWorth, 2, '.', '' );
		$vat   = number_format( $this->basketPromoPriceVat - $couponsTaxWorth, 2, '.', '' );

		Logger::log( '[1GR_DEBUG] basket_final gross=' . $gross . ' vat=' . $vat );

		$price
			->set_gross( $gross )
			->set_vat( $vat );

		if ( $promoPrice->get_gross() === $promoPrice->get_net() ) {
			$net = $gross;
		} else {
			$net = number_format( $price->get_gross() - $price->get_vat(), 2, '.', '' );
		}

		$price->set_net( $net );

		// Ensure no negative values
		if ( $price->get_net() <= 0 ) {
			$price->set_net( 0 );
		}
		if ( $price->get_gross() <= 0 ) {
			$price->set_gross( number_format( 0, 2, '.', '' ) );
		}
		if ( $price->get_vat() <= 0 ) {
			$price->set_vat( number_format( 0, 2, '.', '' ) );
		}

		return $price;
	}
}
