<?php

namespace Ilabs\Inpost_Pay\Lib\Api\v1;

use Ilabs\Inpost_Pay\Lib\Connection;
use Ilabs\Inpost_Pay\Lib\helpers\WooProductHelper;
use Ilabs\Inpost_Pay\Lib\item\HotProducts;
use Ilabs\Inpost_Pay\Lib\Transformers\HotProductTransformer;
use JsonException;
use RuntimeException;
use WC_Product;
use function Ilabs\Inpost_Pay\inpost_pay_container;

class Products extends Connection {
	/**
	 * Gets a list of products from InPostPay.
	 *
	 * @param int   $page_index Page index.
	 * @param int   $page_size Page size.
	 * @param array $product_ids List of product IDs to filter.
	 *
	 * @return object v1/izi/products response.
	 */
	public function get( int $page_index = 0, int $page_size = 100, array $product_ids = array() ) {
		$param_product_ids = '';
		if ( count( $product_ids ) > 0 ) {
			$param_product_ids = '&' . $this->build_params_from_array( 'product_ids', $product_ids );
		}

		return $this->request(
			'v1/izi/products?page_index=' . $page_index . '&page_size=' . $page_size . $param_product_ids,
			'GET',
			array()
		);
	}


	/**
	 * Posts products to API
	 *
	 * @param int[] $product_ids list of product ids to post
	 *
	 * @return array {
	 *   'products': int[] list of product ids that were added
	 *   'not_added_products': array {
	 *     id: int,
	 *     name: string,
	 *     message: string
	 *   }[]
	 * }
	 * @throws JsonException
	 */
	public function post( array $product_ids ): array {
		$response = array(
			'products'           => array(),
			'not_added_products' => array(),
		);

		if ( empty( $product_ids ) ) {
			return $response;
		}

		$hot_products = new HotProducts();

		$global_main_image_only_raw = get_option( 'izi_main_image_only', false );
		$global_main_image_only     = filter_var( $global_main_image_only_raw, FILTER_VALIDATE_BOOLEAN );

		/**
		 * Get from container DI.
		 *
		 * @var WooProductHelper $product_helper
		 */
		$product_helper = inpost_pay_container()->get( WooProductHelper::SERVICE_KEY );
		$products       = $product_helper->load_products_safe( $product_ids );

		$content = array();
		foreach ( $products as $id => $product ) {
			if ( ! $product ) {
				$response['not_added_products'][] = array(
					'id'      => $id,
					'name'    => '',
					'message' => __( 'Product not found', 'inpost-pay' ),
				);
				continue;
			}

			$content[] = ( new HotProductTransformer( $product ) )->transform( $global_main_image_only );
		}

		if ( empty( $content ) ) {
			return $response;
		}

		$hot_products->set_content( $content );

		$request = $this->request(
			'v1/izi/products',
			'POST',
			$hot_products->encode(),
			false,
			true,
		);

		if ( empty( $request->content ) ) {
			if ( isset( $request->error_code ) && $request->error_code === 'MAX_LIMIT_PRODUCTS' ) {
				throw new \RuntimeException(
					__( 'You have reached the maximum number of hot products allowed by InPost Pay.', 'inpost-pay' )
				);
			}

			throw new \RuntimeException( __( 'No products added', 'inpost-pay' ) );
		}

		$added_products_ids = array_map(
			static fn( $item ) => (int) $item->product_id,
			$request->content
		);

		$response['products'] = $added_products_ids;

		$not_added_products = array_diff( $product_ids, $added_products_ids );
		if ( ! empty( $not_added_products ) ) {
			$not_added_posts = get_posts(
				array(
					'post__in'  => $not_added_products,
					'post_type' => 'product',
					'fields'    => 'ids',
				)
			);

			foreach ( $not_added_products as $not_added_id ) {
				$response['not_added_products'][] = array(
					'id'      => $not_added_id,
					'name'    => in_array( $not_added_id, $not_added_posts, true ) ? get_the_title( $not_added_id ) : '',
					'message' => __( 'Product not added', 'inpost-pay' ),
				);
			}
		}

		return $response;
	}

	/**
	 * Updates a product in InPostPay.
	 *
	 * @param WC_Product $product Product.
	 * @param bool       $with_gallery Whether to include gallery images.
	 * @param array      $gallery_ids Array of gallery image IDs.
	 *
	 * @return ?object v1/izi/product/{product_id} response, or null on failure.
	 * @throws RuntimeException If product is not found.
	 * @throws JsonException
	 */
	public function put( WC_Product $product, bool $with_gallery = false, array $gallery_ids = array() ): ?object {

		if ( $with_gallery ) {
			$global_main_image_only_raw = get_option( 'izi_main_image_only', false );
			$is_main_image_only         = filter_var( $global_main_image_only_raw, FILTER_VALIDATE_BOOLEAN );
			$with_gallery               = $is_main_image_only;
		}

		$content = ( new HotProductTransformer( $product ) )->transform(
			$with_gallery,
			$gallery_ids,
			true
		);

		return $this->request(
			'v1/izi/product/' . $product->get_id(),
			'PUT',
			$content->encode(),
			false,
			true,
		);
	}

	/**
	 * Deletes a product from InPostPay.
	 *
	 * @param int $product_id Product ID.
	 *
	 * @return bool True if the product was deleted, false otherwise.
	 */
	public function delete( int $product_id ): bool {
		$request = $this->request(
			'v1/izi/product/' . $product_id,
			'DELETE',
			array(),
			true,
			false,
		);

		return $request[1] == 204;
	}
}
