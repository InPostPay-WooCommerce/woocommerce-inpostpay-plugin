<?php

namespace Ilabs\Inpost_Pay\rest\admin\product;

use Ilabs\Inpost_Pay\Lib\config\product\HotProductsConfig;
use Ilabs\Inpost_Pay\rest\Admin;
use WP_REST_Request;

class Products extends Admin {

	protected function describe(): void {
		$this->get['/inpost/v1/izi/product/list'] = function ( WP_REST_Request $request ) {
			global $wpdb;

			$category_id        = $request->get_param( 'category_id' );
			$search             = $request->get_param( 'search' ); // Wyszukiwanie po nazwie
			$limit              = $request->get_param( 'limit' ) ?: 20; // Domyślnie 20 produktów na stronę
			$page               = $request->get_param( 'page' ) ?: 1;
			$offset             = ( $page - 1 ) * $limit;
			$type               = $request->get_param( 'type' );
			$exclude_virtual    = $request->get_param( 'exclude_virtual' ); // Czy wykluczać wirtualne produkty?
			$exclude_attributes = $request->get_param( 'exclude_attributes' ); // Czy wykluczać produkty z atrybutami?
			$exclude_ids        = $request->get_param( 'exclude_ids' );

			if ( $type === 'hotproduct' ) {
				$type = 'simple';
				// $exclude_attributes = true;
				$exclude_virtual = true;
				$exclude_ids     = ( new HotProductsConfig() )->get();
			}

			// Podstawowe warunki WHERE
			$where = "WHERE p.post_type = 'product' AND p.post_status = 'publish'";

			// Filtr kategorii (jeśli podano)
			if ( $category_id ) {
				$where .= $wpdb->prepare(
					"
            AND p.ID IN (
                SELECT object_id FROM {$wpdb->term_relationships}
                WHERE term_taxonomy_id = %d
            )",
					$category_id
				);
			}

			// Filtr wyszukiwania po nazwie (jeśli podano)
			if ( $search ) {
				$where .= $wpdb->prepare( ' AND p.post_title LIKE %s', '%' . $wpdb->esc_like( $search ) . '%' );
			}

			// Filtr według typu produktu (jeśli podano)
			if ( $type ) {
				if ( $type === 'simple' ) {
					// Jeśli szukamy "simple", musimy uwzględnić produkty BEZ `_product_type`
					$where .= $wpdb->prepare(
						"
                AND (p.ID IN (
                    SELECT post_id FROM {$wpdb->postmeta}
                    WHERE meta_key = '_product_type' AND meta_value = %s
                ) OR p.ID NOT IN (
                    SELECT post_id FROM {$wpdb->postmeta}
                    WHERE meta_key = '_product_type'
                ))",
						$type
					);
				} else {
					// Normalne filtrowanie po `_product_type`
					$where .= $wpdb->prepare(
						"
                AND p.ID IN (
                    SELECT post_id FROM {$wpdb->postmeta}
                    WHERE meta_key = '_product_type' AND meta_value = %s
                )",
						$type
					);
				}
			}

			// Wykluczenie produktów wirtualnych**
			if ( $exclude_virtual ) {
				$where .= " AND p.ID NOT IN (
            SELECT post_id FROM {$wpdb->postmeta}
            WHERE meta_key = '_virtual' AND meta_value = 'yes'
        )";
			}

			// Wykluczenie produktów z atrybutami**
			if ( $exclude_attributes ) {
				$where .= " AND p.ID NOT IN (
            SELECT post_id FROM {$wpdb->postmeta}
            WHERE meta_key = '_product_attributes' AND meta_value != 'a:0:{}'
        )";
			}

			// Wykluczenie konkretnych ID produktów
			if ( ! empty( $exclude_ids ) && is_array( $exclude_ids ) ) {
				$exclude_ids             = array_map( 'intval', $exclude_ids );
				$exclude_ids_placeholder = implode( ',', array_fill( 0, count( $exclude_ids ), '%d' ) );
				$where                  .= $wpdb->prepare( " AND p.ID NOT IN ($exclude_ids_placeholder)", ...$exclude_ids );
			}

			// Pobranie liczby wszystkich pasujących produktów
			$count_query    = "SELECT COUNT(*) FROM {$wpdb->posts} p $where";
			$total_products = $wpdb->get_var( $count_query );
			$total_pages    = ceil( $total_products / $limit );

			// Pobranie produktów z paginacją
			$query = $wpdb->prepare(
				"
        SELECT DISTINCT
            p.ID AS id,
            p.post_title AS name,
            COALESCE(pm_type.meta_value, 'simple') AS type,
            pm_virtual.meta_value AS `virtual`,
            pm_attributes.meta_value AS attributes
        FROM {$wpdb->posts} p
        LEFT JOIN {$wpdb->postmeta} pm_type ON p.ID = pm_type.post_id AND pm_type.meta_key = '_product_type'
        LEFT JOIN {$wpdb->postmeta} pm_virtual ON p.ID = pm_virtual.post_id AND pm_virtual.meta_key = '_virtual'
        LEFT JOIN {$wpdb->postmeta} pm_attributes ON p.ID = pm_attributes.post_id AND pm_attributes.meta_key = '_product_attributes'
        $where
        ORDER BY p.post_title ASC
        LIMIT %d OFFSET %d
    ",
				$limit,
				$offset
			);

			$products = $wpdb->get_results( $query );

			$response = array(
				'total_products' => (int) $total_products,
				'total_pages'    => (int) $total_pages,
				'current_page'   => (int) $page,
				'products'       => array_map(
					function ( $row ) {
						return array(
							'id'   => (int) $row->id,
							'name' => $row->name,
							'type' => $row->type,
						);
					},
					$products
				),
			);

			return rest_ensure_response( $response );
		};
	}
}
