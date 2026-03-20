<?php
namespace Ilabs\Inpost_Pay\rest\admin\product;

use Ilabs\Inpost_Pay\rest\Admin;
use WP_REST_Request;


class Categories extends Admin {

	protected function describe() {
		$this->get['/inpost/v1/izi/product/categories'] = function ( WP_REST_Request $request ) {
			$args = [
				'taxonomy'   => 'product_cat',
				'hide_empty' => false,
				'orderby'    => $request->get_param( 'orderby' ) ?: 'name',
				'order'      => $request->get_param( 'order' ) ?: 'ASC',
			];

			$categories = get_terms( $args );
			if ( is_wp_error( $categories ) ) {
				return rest_ensure_response( [ 'error' => 'Błąd pobierania kategorii' ] );
			}

			$response = [];
			foreach ( $categories as $category ) {
				$response[] = [
					'id'   => $category->term_id,
					'name' => $category->name,
					'slug' => $category->slug,
				];

			}

			return rest_ensure_response( $response );
		};
	}
}
