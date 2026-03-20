<?php

namespace Ilabs\Inpost_Pay\Lib\Transformers;

use Ilabs\Inpost_Pay\Lib\item\HotProduct;
use Ilabs\Inpost_Pay\Lib\item\HotProductAvailability;
use Ilabs\Inpost_Pay\Logger;

class HotProductTransformer extends ProductTransformer {

	public function transform(
		bool $global_main_image_only,
		array $gallery_ids = [],
		bool $withAvailability = false
	): HotProduct {
		$hot_product = new HotProduct();

		$inverse_raw = $this->wc_product->get_meta( '_izi_gallery_inverse', true );
		$is_inverse  = filter_var( $inverse_raw, FILTER_VALIDATE_BOOLEAN );

		$should_include_gallery = $is_inverse
			? $global_main_image_only
			: ! $global_main_image_only;

		$hot_product->set_product_id($this->getProductIdentifier());
		$hot_product->set_ean($this->wc_product->get_global_unique_id() ?: '0');
		$hot_product->set_product_name((string) $this->cut_title(strip_tags(html_entity_decode($this->wc_product->get_name()))));
		$hot_product->set_product_description($this->getDescription());
		$hot_product->set_product_image($this->getThumbnailImage());
		$hot_product->set_product_link($this->getProductLink());

		$hot_product->set_additional_product_images(
			$this->getProductImages($should_include_gallery, $gallery_ids)
		);

		$hot_product->set_product_attributes($this->mapProductAttributes());
		$hot_product->set_price($this->readProductPromoPrice());
		$hot_product->set_currency(get_woocommerce_currency());
		$hot_product->set_quantity($this->readStockQuantity(true));

		if ($withAvailability && $product_availability = $this->handleHotProductAvailability()) {
			$hot_product->set_product_availability($product_availability);
		}

		return $hot_product;
	}


	public function cut_title( $title, $length = 250 ) {
		if ( strlen( $title ) > $length ) {
			$title = substr( $title, 0, $length ) . '...';
		}

		return $title;
	}

	public function handleHotProductAvailability(): ?HotProductAvailability {
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
	private function getProductLink(): string {
		return get_the_permalink( $this->wc_product->get_id() );
	}
}
