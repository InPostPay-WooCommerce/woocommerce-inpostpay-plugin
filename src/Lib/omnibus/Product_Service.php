<?php

namespace Ilabs\Inpost_Pay\Lib\omnibus;

use WC_Product;
use WC_Tax;

class Product_Service {

	static $prevent_duplicates = [];

	public function handle_product_update(
		int $product_id
	) {
		if ( in_array( $product_id, self::$prevent_duplicates ) ) {
			return;
		}

		$post_key                   = inpost_pay()->add_slug_prefix( 'omnibus_price' );
		$omnibus_price_from_request = isset( $_POST[ $post_key ] ) ? wc_clean( wp_unslash( $_POST[ $post_key ] ) ) : null;

		if ( ! $omnibus_price_from_request ) {
			return;
		}

		$product = wc_get_product( $product_id );

		$new_omnibus_price = ( new Price_Model_Factory() )
			->create(
				[
					Price_Model::ARRAY_PRICE_KEY_0          => $omnibus_price_from_request,
					Price_Model::ARRAY_DATETIME_KEY_1       => date( 'Y-m-d H:i:s' ),
					Price_Model::ARRAY_IS_ON_SALE_KEY_2     => $product->is_on_sale(),
					Price_Model::ARRAY_IS_PURCHASABLE_KEY_3 => ( new Product_Helper() )->is_purchasable( $product ) ? 1 : 0,
				] );


		$lowest_Price_Cache_Repository = new Lowest_Price_Cache_Post_Meta_Repository();

		$lowest_Price_Cache_Repository->update( $new_omnibus_price,
			$product_id );

		self::$prevent_duplicates[] = $product_id;
	}

	private function should_recalculate_lowest_price(
		Price_Model $price,
		int $product_id
	): bool {
		$price_repository = new Price_Post_Meta_Repository();
		$last_price       = $price_repository->get_last_price( $product_id );

		if ( ! $last_price ) {
			return false;
		}

		return $price->get_price_float() !== $last_price->get_price_float();
	}

	private function check_real_discount(
		Price_Model $price,
		int $product_id
	): ?bool {
		$price_repository = new Price_Post_Meta_Repository();

		return $price_repository->is_price_lower_than_last_price( $price,
			$product_id );
	}

	public function should_show_omnibus_note( WC_Product $product ): bool {
		if ( ! ( new Product_Helper() )->is_purchasable( $product ) ) {
			return false;
		}

		if ( inpost_pay()
			->get_omnibus()
			->get_option_show_on_none_discount_products() ) {
			return true;
		} else {
			if ( ! $product->is_on_sale() ) {
				return false;
			}
		}

		return true;
	}

	function get_product_base_net_price( WC_Product $product ) {
		$base_price         = $product->get_regular_price();
		$prices_include_tax = wc_prices_include_tax();

		if ( $prices_include_tax ) {
			$tax_rates = WC_Tax::get_rates( $product->get_tax_class() );
			$tax_rate  = 0;

			if ( ! empty( $tax_rates ) ) {
				foreach ( $tax_rates as $rate ) {
					$tax_rate += $rate['rate'];
				}
				$tax_rate = $tax_rate / 100;
			}

			$net_price = $base_price / ( 1 + $tax_rate );
		} else {
			$net_price = $base_price;
		}

		return $net_price;
	}
}
