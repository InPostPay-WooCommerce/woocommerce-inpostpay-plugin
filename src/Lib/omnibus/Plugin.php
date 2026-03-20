<?php

namespace Ilabs\Inpost_Pay\Lib\omnibus;

use Exception;
use Isolated\Inpost_Pay\Ilabs\Ilabs_Plugin\Abstract_Ilabs_Plugin;
use Isolated\Inpost_Pay\Ilabs\Ilabs_Plugin\Alerts;
use Isolated\Inpost_Pay\Ilabs\Ilabs_Plugin\Event_Chain\Interfaces\Wc_Product_Aware_Interface;
use Isolated\Inpost_Pay\Ilabs\Ilabs_Plugin\Event_Chain\Interfaces\Wc_Variation_Aware_Interface;
use Isolated\Inpost_Pay\Ilabs\Ilabs_Plugin\Event_Chain\Interfaces\Wp_Post_Id_Aware_Interface;
use Isolated\Inpost_Pay\Ilabs\Ilabs_Plugin\Woocommerce_Logger;
use WC_Product;
use WC_Product_Variation;
use Ilabs\Inpost_Pay\Plugin as Inpost_Plugin;

defined( 'ABSPATH' ) || exit;

class Plugin extends Inpost_Plugin {

	/**
	 * @var string
	 */
	private static $option_show_on_listing_key;

	/**
	 * @var string
	 */
	private static $option_show_on_none_discount_products_key;

	/**
	 * @var string
	 */
	private static $option_omnibus_enabled_key;

	/**
	 * @throws Exception
	 */
	protected function before_init() {
		$this->init_settings();
		if ( ! $this->get_option_omnibus_enabled() ) {
			return;
		}

		$this->implement_omnibus();
	}

	protected function plugins_loaded_hooks() {
	}

	private function init_settings() {
		self::$option_show_on_listing_key                = 'izi_omnibus_show_on_listing';
		self::$option_show_on_none_discount_products_key = 'izi_omnibus_show_on_none_discount_products';
		self::$option_omnibus_enabled_key                = 'izi_omnibus_enabled';
	}

	/**
	 * @throws Exception
	 */
	public function enqueue_frontend_scripts() {

	}

	/**
	 * @throws Exception
	 */
	public function enqueue_dashboard_scripts() {
		wp_enqueue_style( $this->get_plugin_prefix() . '_admin_css',
			$this->get_plugin_css_url() . '/omnibus/admin.css'
		);
	}

	public function init() {
		if ( ! $this->get_option_omnibus_enabled() ) {
			return;
		}

		( new Hooks() )->init();
	}

	/**
	 * @return void
	 * @throws Exception
	 */
	private function implement_omnibus() {
		$omnibus         = $this->get_omnibus()->get_event_chain();
		$product_service = new Product_Service();

		$omnibus
			->on_wc_product_update()
			->action( function ( Wc_Product_Aware_Interface $product_aware ) use
			(
				$product_service
			) {
				if ( $product_aware instanceof WC_Product_Variation ) {
					return;
				}
				get_woocommerce_currency();
				$product_id = $product_aware->get_product()->get_id();
				$product_service->handle_product_update( $product_id );
			} )
			->on_wc_variation_update()
			->action( function ( Wc_Variation_Aware_Interface $product_aware
			) use (
				$product_service
			) {
				$product_id = $product_aware->get_variation()->get_id();
				$product_service->handle_product_update( $product_id );
			} )
			->on_wc_product_options_pricing()
			->action( function ( Wp_Post_Id_Aware_Interface $post_id_aware ) {
				$product = wc_get_product( $post_id_aware->get_post_id() );
				/*if ( ! ( new Product_Service() )->should_show_omnibus_note( $product ) ) {
					return;
				}*/


				$post_id          = $post_id_aware->get_post_id();
				$price_repository = new Lowest_Price_Cache_Post_Meta_Repository();
				$omnibus_price    = $price_repository->get( $post_id );

				if ( ! empty( $omnibus_price ) ) {
					$omnibus_price_val = $omnibus_price->get_price_float();
				} else {
					$omnibus_price_val = '';
				}

				woocommerce_wp_text_input(
					[
						'id'          => $this->add_slug_prefix( 'omnibus_price' ),
						'label'       => __( 'Omnibus Price',
							'inpost-pay' ),
						'value'       => wc_format_localized_price( $omnibus_price_val ),
						'class'       => '',
						'data_type'   => 'price',
						'description' => __( 'If you provide non-tax prices for this product, provide a non-tax Omnibus price. If you provide tax-inclusive prices for this product, provide the tax-inclusive Omnibus price.',
							'inpost-pay' ),
					]
				);

			} )
			->execute();
	}

	public function output_product_note_html(
		WC_Product $product,
		bool $only_on_single = true
	) {


		if ( ! ( new Product_Service() )->should_show_omnibus_note( $product ) ) {
			return;
		}

		$price_note_template_service   = new Price_Note_Template_Service();
		$lowest_Price_Cache_Repository = new Lowest_Price_Cache_Post_Meta_Repository();
		$lowest_price                  = $lowest_Price_Cache_Repository
			->get( $product->get_id() );

		if ( $only_on_single && ! is_product() ) {
			return;
		}

		if ( $lowest_price instanceof Price_Model ) {
			if ( $lowest_Price_Cache_Repository->is_price_outdated( $lowest_price ) ) {
				return;
			}
			$price_note_template_service->output_product_note_html( $lowest_price->get_price_float(),
				$product );
		} else {
			$price_note_template_service->output_product_note_html(
				$product->get_regular_price( 'false' ),
				$product );
		}
	}

	public function format_omnibus_price(
		float $price,
		WC_Product $product
	): string {
		return wc_price( wc_get_price_to_display( $product,
			[ 'price' => $price ] ) );
	}


	public function get_option_show_on_listing(): bool {
		return get_option( self::$option_show_on_listing_key,
				0 ) == 1;
	}

	public function get_option_show_on_none_discount_products(): bool {
		return get_option( self::$option_show_on_none_discount_products_key,
				0 ) == 1;
	}

	public function get_option_omnibus_enabled(): bool {
		return true;
	}
}
