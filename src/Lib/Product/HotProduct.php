<?php

namespace Ilabs\Inpost_Pay\Lib\Product;

use Ilabs\Inpost_Pay\Lib\Api\v1\Products;
use Ilabs\Inpost_Pay\Lib\config\product\HotProductsConfig;
use Ilabs\Inpost_Pay\Lib\config\product\InactiveHotProductsConfig;
use Ilabs\Inpost_Pay\Lib\item\HotProducts;
use Ilabs\Inpost_Pay\Lib\Product\CustomMeta\HotProductPublishedMeta;
use Ilabs\Inpost_Pay\Lib\Transformers\HotProductTransformer;
use Ilabs\Inpost_Pay\Logger;
use Ilabs\Inpost_Pay\Lib\helpers\WooProductHelper;
use function Ilabs\Inpost_Pay\inpost_pay_container;

class HotProduct {

	public function getList( int $page_index, int $page_size, array $product_ids ): HotProducts {
		$hot_products_list = ( new HotProductsConfig() )->get();
		$hot_products      = new HotProducts();
		$hot_products->set_page_index( $page_index );
		$hot_products->set_page_size( $page_size );

		if ( empty( $hot_products_list ) ) {
			return $hot_products;
		}

		$hot_products->set_total_items( count( $hot_products_list ) );

		if ( count( $product_ids ) > 0 ) {
			$hot_products_list = array_unique( array_intersect( $hot_products_list, $product_ids ) );
		}

		$hot_products_list = array_slice(
			$hot_products_list,
			( $page_index - 1 ) * $page_size,
			$page_size
		);

		if ( count( $hot_products_list ) === 0 ) {
			return $hot_products;
		}

		$global_main_image_only_raw = get_option( 'izi_main_image_only', false );
		$global_main_image_only     = filter_var( $global_main_image_only_raw, FILTER_VALIDATE_BOOLEAN );

		/**
		 * Get from container DI.
		 *
		 * @var WooProductHelper $product_helper
		 */
		$product_helper = inpost_pay_container()->get( WooProductHelper::SERVICE_KEY );
		$products       = $product_helper->load_products_safe( $hot_products_list );
		$content        = array();

		foreach ( $products as $id => $wc_product ) {
			if ( ! $wc_product ) {
				continue;
			}

			$product_transformer = new HotProductTransformer( $wc_product );
			$content[]           = $product_transformer->transform( $global_main_image_only, array(), true );
		}

		$hot_products->set_content( $content );

		return $hot_products;
	}


	public function getInactiveProductIds(): array {
		$api_response = ( new Products() )->get();

		if ( empty( $api_response->content ) ) {
			return array();
		}

		$product_ids      = array();
		$api_products_ids = array();
		foreach ( $api_response->content as $product ) {
			$api_products_ids[] = $product->product_id;
			if ( $product->status === 'INACTIVE' ) {
				$product_ids[] = $product->product_id;

			}

			update_post_meta(
				$product->product_id,
				HotProductPublishedMeta::INPOST_PAY_HOT_PRODUCT_PUBLISHED,
				$product->status
			);
		}

		( new InactiveHotProductsConfig() )->update( $product_ids );

		$this->clean_not_in_api( $api_products_ids );

		set_transient(
			'inpost_pay_product_update_hot_from_api',
			true,
			5
		);

		return $product_ids;
	}

	public function getExpiredProductIds(): array {
		$api_response = ( new Products() )->get();

		if ( empty( $api_response->content ) ) {
			return array();
		}

		$expired_product_ids = array();
		$current_time        = new \DateTime( 'now', new \DateTimeZone( 'Europe/Warsaw' ) );

		foreach ( $api_response->content as $product ) {
			$end_date_raw = get_post_meta( $product->product_id, 'hot_product_end_date', true );

			if ( $end_date_raw ) {
				$end_date = new \DateTime( $end_date_raw );

				if ( $current_time > $end_date ) {
					$expired_product_ids[] = $product->product_id;

					$current_status = get_post_meta(
						$product->product_id,
						HotProductPublishedMeta::INPOST_PAY_HOT_PRODUCT_PUBLISHED,
						true
					);

					if ( $current_status !== $product->status ) {
						update_post_meta(
							$product->product_id,
							HotProductPublishedMeta::INPOST_PAY_HOT_PRODUCT_PUBLISHED,
							$product->status
						);

					}
				}
			}
		}

		set_transient(
			'inpost_pay_product_expired_check',
			true,
			5
		);

		return $expired_product_ids;
	}

	public function clean_not_in_api( array $api_products_ids ): void {
		( new HotProductsConfig() )->update( $api_products_ids );
	}
}
