<?php

namespace Ilabs\Inpost_Pay\rest\admin\product;

use Ilabs\Inpost_Pay\hooks\admin\AdminProductUpdate;
use Ilabs\Inpost_Pay\Lib\config\product\HotProductsConfig;
use Ilabs\Inpost_Pay\Lib\helpers\EANHelper;
use Ilabs\Inpost_Pay\Lib\Product\CustomMeta\HotProductPublishedMeta;
use Ilabs\Inpost_Pay\Lib\Product\HotProduct;
use Ilabs\Inpost_Pay\Lib\Utils\HotProductUtils;
use Ilabs\Inpost_Pay\Logger;
use Ilabs\Inpost_Pay\rest\Admin;
use Ilabs\Inpost_Pay\Lib\Api\v1\Products;
use Ilabs\Inpost_Pay\Lib\helpers\WooProductHelper;
use RuntimeException;
use WP_REST_Request;
use WP_REST_Response;
use function Ilabs\Inpost_Pay\inpost_pay_container;

class HotProducts extends Admin {

	protected function describe(): void {

		$this->get['/inpost/v1/izi/hot-product/count'] = function ( WP_REST_Request $request ) {

			$hot_products       = new HotProductsConfig();
			$hot_products_count = count( $hot_products->get() );


			return rest_ensure_response( [
				'count' => $hot_products_count,
			] );

		};

		$this->get['/inpost/v1/izi/hot-product/list'] = function ( WP_REST_Request $request ) {

			$is_updated = get_transient( 'inpost_pay_product_update_hot_from_api' );
			if ( ! $is_updated ) {
				( new HotProduct() )->getInactiveProductIds();
			}

			$hot_products = new HotProductsConfig();
			$product_ids  = $hot_products->get();

			if ( ! $product_ids ) {
				return rest_ensure_response( new WP_REST_Response(
					[ 'error' => __( 'No hot products found.', 'inpost-pay' ) ],
					200
				) );
			}

			$response = HotProductUtils::buildHotProductDataByIds( $product_ids );

			$unpublishedProducts = array_filter( $response, static function ( $item ) {
				return $item['published'] === false;
			} );

			if ( count( $unpublishedProducts ) > 0 ) {
				$izi_product_api = new Products();

				$product_ids = [];

				foreach ( $unpublishedProducts as $product ) {
					$product_ids[] = $product['id'];
				}

				$api_result = $izi_product_api->get( 0, count( $product_ids ), $product_ids );

				if ( isset( $api_result->content ) ) {
					foreach ( $api_result->content as $content ) {
						if ( $content->status === 'ACTIVE' ) {
							AdminProductUpdate::$IS_START = true;

							update_post_meta(
								$content->product_id,
								HotProductPublishedMeta::INPOST_PAY_HOT_PRODUCT_PUBLISHED,
								HotProductUtils::STATUS_ACTIVE
							);

							$index = array_search( $content->product_id, array_column( $response, 'id' ), true );
							if ( $index !== false ) {
								$response[ $index ]['published'] = true;
							}

						}
					}
				}
			}

			return rest_ensure_response( $response );
		};

		$this->post['/inpost/v1/izi/hot-product/add'] = function ( WP_REST_Request $request ) {
			$hot_products = new HotProductsConfig();

//			if ( count( $hot_products->get() ) >= HotProductsConfig::IZI_HOT_PRODUCTS_LIMIT ) {
//				return rest_ensure_response( [ 'error' => __( 'You reached the limit of hot products.', 'inpost-pay' ) ] );
//			}

			$izi_product_api = new Products();

			if ( $request->has_param( 'product_id' ) ) {
				$id            = (int) $request->get_param( 'product_id' );
				$product_ids   = $request->get_param( 'product_ids' ) ?? [];
				$product_ids[] = $id;
				$product_ids   = array_unique( array_map( 'intval', $product_ids ) );
				$request->set_param( 'product_ids', $product_ids );
			}

			$valid_product_ids = [];
			$rejected_ids      = [];

			if ( $request->has_param( 'product_ids' ) && is_array( $request->get_param( 'product_ids' ) ) ) {
				$product_ids_param   = $request->get_param( 'product_ids' ) ?? [];
				$initial_product_ids = array_map( 'intval', $product_ids_param );

				if ( empty( $initial_product_ids ) ) {
					return rest_ensure_response( new WP_REST_Response(
						[ 'error' => __( 'No products provided.', 'inpost-pay' ) ],
						400
					) );
				}

				/**
				 * Get from container DI.
				 *
				 * @var WooProductHelper $product_helper
				 */
				$product_helper    = inpost_pay_container()->get( WooProductHelper::SERVICE_KEY );
				$products          = $product_helper->load_products_safe( $initial_product_ids );
				$hot_products_list = $hot_products->get();

				foreach ( $products as $id => $product ) {
					if ( ! $product || $product->get_type() !== 'simple' ) {
						$rejected_ids[] = $id;
						continue;
					}

					$ean = $product->get_meta( '_global_unique_id', true );

					// Logger::log('[HOTPRODUCT_DEBUG] Product EAN: ' . $ean);
					if ( ! EANHelper::isValid( $ean ) ) {
						// Logger::log('[HOTPRODUCT_DEBUG] Product EAN is not valid: ' . $id);
						$rejected_ids[] = $id;
						continue;
					}

					$valid_product_ids[] = $id;
				}

				try {
					if ( ! empty( $valid_product_ids ) ) {
						$result = $izi_product_api->post( $valid_product_ids );
					} else {
						$result = [ 'products' => [] ];
					}
				} catch ( \RuntimeException $e ) {
					$rejected_ids      = array_merge( $rejected_ids, $valid_product_ids );
					$valid_product_ids = [];
					$result            = [ 'products' => [] ];
				}

				if ( isset( $result['error'] ) && ! isset( $result['products'] ) ) {
					return rest_ensure_response( new WP_REST_Response(
						[ 'error' => $result['error'], 'error_code' => $result['error_code'] ?? '' ],
						403
					) );
				}

				AdminProductUpdate::$IS_START = true;

				foreach ( $result['products'] as $product_id ) {
					update_post_meta( $product_id, HotProductPublishedMeta::INPOST_PAY_HOT_PRODUCT_PUBLISHED, 'INACTIVE' );
				}

				$hot_products_list = array_unique( array_merge( $hot_products_list, $result['products'] ?? [] ) );

				if ( count( $hot_products_list ) > HotProductsConfig::IZI_HOT_PRODUCTS_LIMIT ) {
					return rest_ensure_response( new WP_REST_Response(
						[ 'error' => __( 'You reached the limit of hot products.', 'inpost-pay' ) ],
						403
					) );
				}

				$hot_products->update( $hot_products_list );
			}

			$added_products    = HotProductUtils::buildHotProductDataByIds( $valid_product_ids );
			$rejected_products = HotProductUtils::buildHotProductDataByIds( $rejected_ids );

//			Logger::log('[HOTPRODUCT_DEBUG] Added products: ' . var_export( $added_products, true ) . '');
//			Logger::log('[HOTPRODUCT_DEBUG] Rejected products: ' . var_export( $rejected_products, true ) . '');

			return rest_ensure_response( [
				'added'    => [
					'message'  => __( 'Highlight products', 'inpost-pay' ),
					'products' => $added_products,
				],
				'rejected' => [
					'message'  => __( 'Products that could not be highlighted', 'inpost-pay' ),
					'products' => $rejected_products,
				]
			] );
		};

		$this->post['/inpost/v1/izi/hot-product/delete'] = function ( WP_REST_Request $request ) {
			$hot_products = new HotProductsConfig();

			$hot_products_list = $hot_products->get();

			if ( ! in_array( $request->get_param( 'product_id' ), $hot_products_list ) ) {
				return rest_ensure_response( new WP_REST_Response(
					[ 'error' => __( 'Product not found.', 'inpost-pay' ) ],
					403
				) );
			}

			$hot_products_list = array_values( array_diff( $hot_products_list, [ $request->get_param( 'product_id' ) ] ) );

			$izi_product_api = new Products();

			$product_id = (int) $request->get_param( 'product_id' );
			if ( ! $izi_product_api->delete( $product_id ) ) {
				return rest_ensure_response( new WP_REST_Response(
					[ 'error' => __( 'Product not removed.', 'inpost-pay' ) ],
					403
				) );
			}

			delete_post_meta( $product_id, 'hot_product_start_date' );
			delete_post_meta( $product_id, 'hot_product_end_date' );

			$hot_products->update( $hot_products_list );

			return rest_ensure_response( [ 'message' => __( 'Product removed.', 'inpost-pay' ) ] );
		};

		$this->post['/inpost/v1/izi/hot-product/delete_all'] = function ( WP_REST_Request $request ) {
			$hot_products      = new HotProductsConfig();
			$hot_products_list = $hot_products->get();
			$izi_product_api   = new Products();

			$not_deleted_products = [];

			$api_result = $izi_product_api->get( 0, 1000 );
			$remote_ids = isset( $api_result->content )
				? array_map( static function ( $c ) {
					return (int) $c->product_id;
				}, $api_result->content )
				: [];

			foreach ( $hot_products_list as $product_id ) {
				$pid = (int) $product_id;

				try {
					$deleted_remote = $izi_product_api->delete( $pid );
				} catch ( \Throwable $e ) {
					$deleted_remote = false;
				}

				delete_post_meta( $pid, 'hot_product_start_date' );
				delete_post_meta( $pid, 'hot_product_end_date' );
				delete_post_meta( $pid, HotProductPublishedMeta::INPOST_PAY_HOT_PRODUCT_PUBLISHED );

				if ( ! $deleted_remote ) {
					$not_deleted_products[] = [
						'id'   => $pid,
						'name' => get_the_title( $pid ),
					];
				}
			}

			$hot_products->update( [] );
			delete_transient( 'inpost_pay_product_update_hot_from_api' );

			$response['message'] = __( 'Products removed.', 'inpost-pay' );
			if ( count( $not_deleted_products ) > 0 ) {
				$response['error']                = __( 'Products not removed.', 'inpost-pay' );
				$response['not_deleted_products'] = $not_deleted_products;
			}

			return rest_ensure_response( $response );
		};

		$this->post['/inpost/v1/izi/hot-product/set-availability'] = function ( WP_REST_Request $request ) {

			$entries = $request->get_json_params();

			if ( ! is_array( $entries ) || empty( $entries ) ) {
				return rest_ensure_response( new WP_REST_Response(
					[ 'error' => __( 'No availability data provided.', 'inpost-pay' ) ],
					400
				) );
			}

			$rejected     = [];
			$updated      = [];
			$products_api = new Products();

			foreach ( $entries as $entry ) {
				$product_id = isset( $entry['product_id'] ) ? (int) $entry['product_id'] : 0;
				$start_date = $entry['start_date'] ?? null;
				$end_date   = $entry['end_date'] ?? null;

				if ( ! $product_id || get_post_type( $product_id ) !== 'product' ) {
					$rejected[] = $product_id;
					continue;
				}

				$product = wc_get_product( $product_id );
				if ( ! $product ) {
					$rejected[] = $product_id;
					continue;
				}

				try {
					if ( $start_date ) {
						$dt_start = new \DateTime( $start_date );
						update_post_meta( $product_id, 'hot_product_start_date', $dt_start->format( 'Y-m-d H:i:s' ) );
					}
					if ( $end_date ) {
						$dt_end = new \DateTime( $end_date );
						update_post_meta( $product_id, 'hot_product_end_date', $dt_end->format( 'Y-m-d H:i:s' ) );
					}

					$response  = $products_api->put( $product );
					$status    = HotProductUtils::handlePutResponse( $response );
					$updated[] = [
						'id'     => $product_id,
						'status' => $status
					];
				} catch ( \Exception $e ) {
					$rejected[] = $product_id;
				}
			}

			$message = __( 'Availability dates updated.', 'inpost-pay' );

			if ( ! empty( $rejected ) ) {
				$message = __( 'Availability dates updated for some products. Some failed to update.', 'inpost-pay' );
			}

			return rest_ensure_response( [
				'message'  => $message,
				'updated'  => $updated,
				'rejected' => $rejected,
			] );
		};
	}
}
