<?php

namespace Ilabs\Inpost_Pay\WooCommerce\Price;

use Ilabs\Inpost_Pay\Lib\item\Price;
use Ilabs\Inpost_Pay\Lib\omnibus\Coupon_Helper;
use Ilabs\Inpost_Pay\Lib\omnibus\Lowest_Price_Cache_Post_Meta_Repository;
use Ilabs\Inpost_Pay\Lib\omnibus\Price_Model;
use WC_Product;
use WC_Tax;

class OmnibusPrice {
	/**
	 * Checks if the cart has omnibus coupons
	 *
	 * @return bool True if the cart has omnibus coupons, false otherwise
	 */
	public function hasOmnibusCoupons(): bool {
		if ( ! WC()->cart || WC()->cart->is_empty() ) {
			return false;
		}

		return Coupon_Helper::validate_cart_having_omnibus_coupons( WC()->cart );
	}

	/**
	 * Reads the lowest price for a cart product
	 *
	 * @param array $cartContent The cart content
	 *
	 * @return Price|null The lowest price or null if not available
	 */
	public function readCartProductLowestPrice( array $cartContent ): ?Price {
		/**
		 * @var WC_Product $productSimple
		 */
		$productSimple = $cartContent['data'];
		$price         = new Price();

		$lowestPriceCacheRepository = new Lowest_Price_Cache_Post_Meta_Repository();
		$lowestPrice                = $lowestPriceCacheRepository->get( $productSimple->get_id() );

		if ( ! $lowestPrice instanceof Price_Model ) {
			return null;
		}

		$is_taxable = $productSimple->is_taxable();

		if ( $is_taxable ) {
			$priceIncludingTax = $lowestPrice->get_price_float();
			$priceExcludingTax = wc_get_price_excluding_tax( $productSimple, [ 'price' => $lowestPrice->get_price_float() ] );
		} else {
			$priceIncludingTax = wc_get_price_including_tax( $productSimple, [ 'price' => $lowestPrice->get_price_float() ] );
			$priceExcludingTax = $lowestPrice->get_price_float();
		}

		$taxes = WC_Tax::calc_tax(
			$priceIncludingTax,
			WC_Tax::get_shipping_tax_rates(),
			true
		);

		$vat = array_sum( $taxes );

		$price
			->set_gross( number_format( $priceIncludingTax, 2, '.', '' ) )
			->set_net( $priceExcludingTax )
			->set_vat( number_format( $vat, 2, '.', '' ) )
		;

		return $price;
	}
}
