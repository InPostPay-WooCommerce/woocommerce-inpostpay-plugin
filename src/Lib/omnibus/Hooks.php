<?php

namespace Ilabs\Inpost_Pay\Lib\omnibus;

use WC_Product;
use WC_Product_Grouped;
use WC_Product_Variable;
use WC_Product_Variation;
use WP_Post;

class Hooks {

	public function init() {
		/*add_filter( 'woocommerce_grouped_product_columns',
			[ $this, 'filter_grouped_product_columns' ],
			10,
			2 );*/
		/*add_action( 'woocommerce_grouped_product_list_after_omnibus',
			[ $this, 'filter_grouped_product_child' ],
			10,
			1 );*/
		/*add_action( 'woocommerce_single_product_summary',
			[ $this, 'single_product_summary' ],
			11,
			1 );*/
		/*add_action( 'woocommerce_get_price_html', [
			$this,
			'woocommerce_get_variation_price_html',
		], 10, 2 );*/
		add_action( 'woocommerce_variation_options_pricing',
			[ $this, 'woocommerce_variation_options_pricing' ],
			10,
			3 );

		/*add_action( 'woocommerce_after_shop_loop_item_title',
			[ $this, 'after_shop_loop_item_title' ],
			10,
			3 );
		add_shortcode( 'omnibus_by_ilabs_message_single_product',
			[ $this, 'omnibus_by_ilabs_message_shortcode' ] );*/
	}

	public function filter_grouped_product_columns(
		array $columns,
		WC_Product $product
	) {
		$columns[] = 'omnibus';

		return $columns;
	}

	public function filter_grouped_product_child( WC_Product $product ) {
		if ( ( new Product_Service() )->should_show_omnibus_note( $product )
		     && ! apply_filters( inpost_pay()
				->get_omnibus()
				->add_slug_prefix( 'disable_message' ),
				false,
				$product ) ) {
			echo '<tr><td>';
			inpost_pay()->get_omnibus()->output_product_note_html( $product );
			echo '</td></tr>';
		}
	}

	public function single_product_summary() {
		$product = wc_get_product();

		if ( ! $product instanceof WC_Product_Variable
		     && ! $product instanceof WC_Product_Grouped ) {
			if ( ! apply_filters( inpost_pay()
				->get_omnibus()
				->add_slug_prefix( 'disable_message' ),
				false,
				$product ) ) {
				inpost_pay()
					->get_omnibus()
					->output_product_note_html( $product );
			}
		}
	}

	public function woocommerce_get_variation_price_html(
		string $price,
		WC_Product $product
	): string {
		$original_price_html = $price;
		if ( ! ( new Product_Service() )->should_show_omnibus_note( $product )
		     || ! $product instanceof WC_Product_Variation ) {

			return $original_price_html;
		}

		if ( apply_filters( inpost_pay()
			->get_omnibus()
			->add_slug_prefix( 'disable_message' ),
			false,
			$product ) ) {
			return $original_price_html;
		}

		$price_note_template_service = new Price_Note_Template_Service();
		$currency                    = get_woocommerce_currency_symbol();

		$lowest_Price_Cache_Repository = new Lowest_Price_Cache_Post_Meta_Repository();
		$lowest_price                  = $lowest_Price_Cache_Repository
			->get( $product->get_id() );

		if ( ! is_product() ) {
			return $original_price_html;
		}

		if ( $lowest_price instanceof Price_Model ) {
			if ( $lowest_Price_Cache_Repository->is_price_outdated( $lowest_price ) ) {
				return $original_price_html;
			}

			return sprintf( "%s<span class='omnibus-by-ilabs-price-note'><br>%s</span>"
				,
				$original_price_html,
				$price_note_template_service->get_product_note_html( $lowest_price->get_price_float(),
					$product ) );
		} else {
			return sprintf( "%s<span class='omnibus-by-ilabs-price-note'><br>%s</span>"
				,
				$original_price_html,
				$price_note_template_service->get_product_note_html(
					$product->get_regular_price( 'false' ),
					$product ) );
		}
	}

	public function woocommerce_variation_options_pricing(
		$loop,
		$variation_data,
		WP_Post $variation_post
	) {
		$product = wc_get_product( $variation_post->ID );
		if ( ! $product instanceof WC_Product_Variation ) {
			return;
		}


		$post_id          = $variation_post->ID;
		$price_repository = new Lowest_Price_Cache_Post_Meta_Repository();
		$omnibus_price    = $price_repository->get( $post_id );

		if ( ! empty( $omnibus_price ) ) {
			$omnibus_price_val = $omnibus_price->get_price_float();
		} else {
			$omnibus_price_val = '';
		}

		woocommerce_wp_text_input(
			[
				'id'          => inpost_pay()->add_slug_prefix( 'omnibus_price' ),
				'label'       => __( 'Omnibus Price',
					'inpost-pay' ),
				'value'       => wc_format_localized_price( $omnibus_price_val ),
				'class'       => '',
				'data_type'   => 'price',
				'description' => __( 'If you provide non-tax prices for this product, provide a non-tax Omnibus price. If you provide tax-inclusive prices for this product, provide the tax-inclusive Omnibus price.',
					'inpost-pay' ),
			]
		);
	}

	public function after_shop_loop_item_title() {
		if ( ! inpost_pay()->get_omnibus()->get_option_show_on_listing() ) {
			return;
		}

		global $product;

		if ( false === $product instanceof WC_Product ) {
			return;
		}

		if ( ! $product instanceof WC_Product_Variable ) {
			inpost_pay()
				->get_omnibus()
				->output_product_note_html( $product, false );
		}
	}

	public function omnibus_by_ilabs_message_shortcode( $atts = [] ) {
		$product      = wc_get_product();
		$atts         = array_change_key_case( (array) $atts, CASE_LOWER );
		$omnibus_atts = shortcode_atts(
			[
				'only_on_single' => true,
			],
			$atts
		);
		if ( strtolower( $omnibus_atts['only_on_single'] ) === "false" ) {
			$omnibus_atts['only_on_single'] = false;
		}
		if ( strtolower( $omnibus_atts['only_on_single'] ) === "true" ) {
			$omnibus_atts['only_on_single'] = true;
		}
		if ( ! $product instanceof WC_Product_Variable
		     && ! $product instanceof WC_Product_Grouped ) {
			if ( ! apply_filters( inpost_pay()
				->get_omnibus()
				->add_slug_prefix( 'disable_message' ),
				false,
				$product ) ) {
				inpost_pay()->get_omnibus()->output_product_note_html( $product,
					$omnibus_atts['only_on_single'] );
			}
		}
	}
}
