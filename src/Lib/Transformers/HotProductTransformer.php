<?php
/**
 * Hot product transformer.
 *
 * @package Ilabs\Inpost_Pay
 */

namespace Ilabs\Inpost_Pay\Lib\Transformers;

use Ilabs\Inpost_Pay\Lib\item\HotProduct;
use Ilabs\Inpost_Pay\Lib\item\HotProductAvailability;

/**
 * Transforms WC_Product into a HotProduct data object.
 */
class HotProductTransformer extends ProductTransformer {

	/**
	 * Transforms the product into a HotProduct object.
	 *
	 * @param bool  $global_main_image_only Whether to use only the main image globally.
	 * @param array $gallery_ids            Gallery image IDs.
	 * @param bool  $with_availability      Whether to include availability data.
	 *
	 * @return HotProduct
	 */
	public function transform(
		bool $global_main_image_only,
		array $gallery_ids = array(),
		bool $with_availability = false
	): HotProduct {
		$hot_product = new HotProduct();

		$inverse_raw = $this->wc_product->get_meta( '_izi_gallery_inverse', true );
		$is_inverse  = filter_var( $inverse_raw, FILTER_VALIDATE_BOOLEAN );

		$should_include_gallery = $is_inverse
			? $global_main_image_only
			: ! $global_main_image_only;

		$ean = $this->wc_product->get_global_unique_id();
		$hot_product->set_product_id( $this->get_product_identifier() );
		$hot_product->set_ean( $ean ? $ean : '0' );
		$hot_product->set_product_name( (string) $this->cut_title( wp_strip_all_tags( html_entity_decode( $this->wc_product->get_name() ) ) ) );
		$hot_product->set_product_description( $this->get_description() );
		$hot_product->set_product_image( $this->get_thumbnail_image() );
		$hot_product->set_product_link( $this->get_product_link() );

		$hot_product->set_additional_product_images(
			$this->get_product_images( $should_include_gallery, $gallery_ids )
		);

		$hot_product->set_product_attributes( $this->map_product_attributes() );
		$hot_product->set_price( $this->read_product_promo_price() );
		$hot_product->set_currency( get_woocommerce_currency() );
		$hot_product->set_quantity( $this->read_stock_quantity( true ) );

		$product_availability = $this->handle_hot_product_availability();
		if ( $with_availability && $product_availability ) {
			$hot_product->set_product_availability( $product_availability );
		}

		return $hot_product;
	}


	/**
	 * Cuts the title to a maximum length.
	 *
	 * @param string $title  The title to cut.
	 * @param int    $length Maximum title length.
	 *
	 * @return string
	 */
	public function cut_title( string $title, int $length = 250 ): string {
		if ( strlen( $title ) > $length ) {
			$title = substr( $title, 0, $length ) . '...';
		}

		return $title;
	}

	/**
	 * Handles hot product availability dates.
	 *
	 * @return HotProductAvailability|null
	 */
	public function handle_hot_product_availability(): ?HotProductAvailability {
		$start_date_raw = get_post_meta( $this->wc_product->get_id(), 'hot_product_start_date', true );
		$end_date_raw   = get_post_meta( $this->wc_product->get_id(), 'hot_product_end_date', true );

		try {
			$start_date = null;
			$end_date   = null;

			if ( $start_date_raw ) {
				$start_date = ( new \DateTime( $start_date_raw ) )->format( 'Y-m-d\TH:i:s.u\Z' );
			}

			if ( $end_date_raw ) {
				$end_date = ( new \DateTime( $end_date_raw ) )->format( 'Y-m-d\TH:i:s.u\Z' );
			}
		} catch ( \Exception $e ) {

			return null;
		}

		if ( $start_date || $end_date ) {
			return new HotProductAvailability(
				$start_date,
				$end_date
			);
		}

		return null;
	}

	/**
	 * Retrieves the link to the product page.
	 *
	 * @return string Product page permalink.
	 */
	private function get_product_link(): string {
		return get_the_permalink( $this->wc_product->get_id() );
	}
}
