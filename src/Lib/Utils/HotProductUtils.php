<?php

namespace Ilabs\Inpost_Pay\Lib\Utils;

use Ilabs\Inpost_Pay\Lib\config\product\HotProductsConfig;
use Ilabs\Inpost_Pay\Lib\Product\CustomMeta\HotProductPublishedMeta;

class HotProductUtils {
	public const STATUS_ACTIVE = 'ACTIVE';
	public const STATUS_INACTIVE = 'INACTIVE';

	public static function buildHotProductDataByIds( array $product_ids ): array {
		global $wpdb;

		if ( empty( $product_ids ) ) {
			return [];
		}

		// Filtrujemy tylko numeryczne ID dla bezpieczeństwa
		$product_ids     = array_map( 'intval', $product_ids );
		$ids_placeholder = implode( ',', array_fill( 0, count( $product_ids ), '%d' ) );

		// Główne zapytanie do pobrania produktów i ich metadanych
		$query = $wpdb->prepare( "
		        SELECT
		            p.ID AS id,
		            p.post_title AS name,
		            published.meta_value AS published,
		            start_date.meta_value AS start_date,
		            end_date.meta_value AS end_date,
		            ean.meta_value AS ean
		        FROM {$wpdb->posts} p
		        LEFT JOIN {$wpdb->postmeta} published
		            ON p.ID = published.post_id AND published.meta_key = %s
		        LEFT JOIN {$wpdb->postmeta} start_date
		            ON p.ID = start_date.post_id AND start_date.meta_key = 'hot_product_start_date'
		        LEFT JOIN {$wpdb->postmeta} end_date
		            ON p.ID = end_date.post_id AND end_date.meta_key = 'hot_product_end_date'
		        LEFT JOIN {$wpdb->postmeta} ean
		            ON p.ID = ean.post_id AND ean.meta_key = '_global_unique_id'
		        WHERE p.ID IN ($ids_placeholder) AND p.post_type = 'product'
		    ",
			array_merge(
				[ HotProductPublishedMeta::INPOST_PAY_HOT_PRODUCT_PUBLISHED ],
				$product_ids
			)
		);

		$results = $wpdb->get_results( $query );

		return array_map( static function ( $row ) {
			$start_date   = $row->start_date ? ( new \DateTime( $row->start_date ) )->format( DATE_ATOM ) : null;
			$end_date     = $row->end_date ? ( new \DateTime( $row->end_date ) )->format( DATE_ATOM ) : null;
			$is_published = $row->published && $row->published !== self::STATUS_INACTIVE;

			return [
				'id'         => (int) $row->id,
				'name'       => $row->name,
				'ean'        => $row->ean ?: '',
				'published'  => (bool) $is_published,
				'start_date' => $start_date,
				'end_date'   => $end_date,
			];
		}, $results );
	}


	public static function handlePutResponse( $response, $withTransient = true ) {
		$product = wc_get_product( $response->product_id );

		if ( ! $response ) {
			return null;
		}

		$status = $response->status ?? null;

		if ( $response->status === self::STATUS_INACTIVE ) {
			$product_id = $response->product_id;

			update_post_meta(
				$product->get_id(),
				HotProductPublishedMeta::INPOST_PAY_HOT_PRODUCT_PUBLISHED,
				self::STATUS_INACTIVE
			);

			if ( is_admin() && $withTransient ) {
				set_transient(
					'inpost_pay_product_update_hot_inactive_' . get_current_user_id(),
					$product_id,
					30
				);
			}
		}

		return $status;
	}

	/**
	 * Check if any Hot Products are configured.
	 *
	 * @return bool True if at least one Hot Product exists, false otherwise.
	 */
	public static function has_hot_products(): bool {
		$hot_products_list = ( new HotProductsConfig() )->get();

		return ! empty( $hot_products_list ) && count( $hot_products_list ) > 0;
	}

	/**
	 * Get the total count of configured Hot Products.
	 *
	 * @return int Number of Hot Products.
	 */
	public static function count_hot_products(): int {
		$hot_products_list = ( new HotProductsConfig() )->get();

		return count( $hot_products_list );
	}
}
