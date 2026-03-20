<?php

namespace Ilabs\Inpost_Pay\rest\admin\unavailable;

use Exception;
use Ilabs\Inpost_Pay\EntityLayer\Entity\UnavailableEntity;
use Ilabs\Inpost_Pay\EntityLayer\Repository\UnavailableRepository;
use Ilabs\Inpost_Pay\rest\Admin;
use WP_REST_Request;
use WP_REST_Response;
use function Ilabs\Inpost_Pay\inpost_pay_container;

class Unavailable extends Admin {

	private UnavailableRepository $repository;

	public function __construct() {

		$container        = inpost_pay_container();
		$this->repository = $container->get( UnavailableRepository::SERVICE_KEY );
	}

	/**
	 * Deletes unavailable products or categories.
	 *
	 * Handles the `/inpost/v1/izi/unavailable/remove` endpoint.
	 *
	 * Expects a JSON object with an "items" property, which is expected to be an array of objects with an "id" property.
	 *
	 * If the request is malformed, a 400 response is sent with a message indicating what is invalid.
	 *
	 * @param WP_REST_Request $request The request object.
	 *
	 * @return WP_REST_Response The response object.
	 * @throws Exception
	 */
	private function delete( WP_REST_Request $request ): WP_REST_Response {
		$params = $request->get_json_params();

		if ( ! isset( $params['items'] ) ) {
			return new WP_REST_Response(
				array(
					'code'    => 'rest_missing_items',
					'message' => __( 'Request is missing the required "items" parameter.', 'inpost-pay' ),
				),
				400
			);
		}

		$validation_message = $this->validate_delete( $params['items'] );

		if ( true !== $validation_message ) {
			return new WP_REST_Response(
				array(
					'code'    => 'rest_malformed_request',
					'message' => $validation_message,
				),
				400
			);
		}

		$this->delete_unavailable( $params['items'] );

		return new WP_REST_Response(
			array(
				'code'    => 'rest_success',
				'message' => __( 'Unavailable successfully.', 'inpost-pay' ),
			),
			200
		);
	}

	/**
	 * Adds unavailable products or categories.
	 *
	 * Handles the `/inpost/v1/izi/unavailable/add` endpoint.
	 *
	 * Expects a JSON object with either a "products" or "categories" property, both of which are expected to be arrays of objects with
	 * an "id" and "delivery_type" property.
	 *
	 * The "delivery_type" property must be one of the following values:
	 * - 1: APM
	 * - 2: COURIER
	 * - 3: BOTH
	 *
	 * If the request is malformed, a 400 response is sent with a message indicating what is invalid.
	 *
	 * @param WP_REST_Request $request The request object.
	 *
	 * @return WP_REST_Response The response object.
	 * @throws Exception
	 */
	private function add( WP_REST_Request $request ): WP_REST_Response {
		$params = $request->get_json_params();

		if ( ! isset( $params['products'] ) && ! isset( $params['categories'] ) ) {
			return new WP_REST_Response(
				array(
					'code'    => 'rest_missing_products_or_categories',
					'message' => __( 'Request is missing the required "products" or "categories" parameter.', 'inpost-pay' ),
				),
				400
			);
		}

		if ( ! empty( $params['products'] ) ) {
			$validation_message = $this->validate( $params['products'] );
			if ( true !== $validation_message ) {
				return new WP_REST_Response(
					array(
						'code'    => 'rest_malformed_request',
						'message' => $validation_message,
					),
					400
				);
			}
		}

		if ( ! empty( $params['categories'] ) ) {
			$validation_message = $this->validate( $params['categories'] );
			if ( true !== $validation_message ) {
				return new WP_REST_Response(
					array(
						'code'    => 'rest_malformed_request',
						'message' => $validation_message,
					),
					400
				);
			}
		}

		if ( ! empty( $params['products'] ) ) {
			$this->parse_products( $params['products'] );
		}

		if ( ! empty( $params['categories'] ) ) {
			$this->parse_categories( $params['categories'] );
		}

		return new WP_REST_Response(
			array(
				'code'    => 'rest_success',
				'message' => __( 'Unavailable successfully.', 'inpost-pay' ),
			),
			200
		);
	}

	/**
	 * Registers all the routes for this controller.
	 *
	 * This method is called from the constructor and is used to register all the routes for this controller.
	 *
	 * The routes are registered using the `get`, `post`, `put`, `patch`, and `delete` methods.
	 *
	 * The handlers for the routes are methods of this class.
	 */
	protected function describe(): void {
		$this->get['/inpost/v1/izi/unavailable/products']         = function ( WP_REST_Request $request ) {
			return $this->get_unavailable_products();
		};
		$this->get['/inpost/v1/izi/unavailable/categories']       = function ( WP_REST_Request $request ) {
			return $this->get_unavailable_categories( $request );
		};
		$this->post['/inpost/v1/izi/unavailable/add']             = function ( WP_REST_Request $request ) {
			return $this->add( $request );
		};
		$this->post['/inpost/v1/izi/unavailable/remove']          = function ( WP_REST_Request $request ) {
			return $this->delete( $request );
		};
		$this->post['/inpost/v1/izi/unavailable/remove/products'] = function ( WP_REST_Request $request ) {
			return $this->delete_all_products( $request );
		};
	}

	/**
	 * Validates a list of items.
	 *
	 * @param array $items Array of items to validate.
	 *
	 * @return string|true A message indicating what is invalid or true if the list is valid.
	 */
	private function validate( array $items ) {
		foreach ( $items as $item ) {
			if ( empty( $item['id'] ) ) {
				return __( 'Request is missing the required "id" parameter.', 'inpost-pay' );
			}

			if ( empty( $item['delivery_type'] ) ) {
				return __( 'Request is missing the required "delivery_type" parameter.', 'inpost-pay' );
			}

			$delivery_type = (string) $item['delivery_type'];
			if ( ! in_array( $delivery_type, UnavailableEntity::DELIVERY_TYPES, true ) ) {
				return __( 'Incorrect "delivery_type" parameter.', 'inpost-pay' );
			}
		}

		return true;
	}

	/**
	 * Validates a list of items for deletion.
	 *
	 * @param array $items Array of items to validate.
	 *
	 * @return string|true A message indicating what is invalid or true if the list is valid.
	 */
	private function validate_delete( array $items ) {
		foreach ( $items as $item ) {
			$id = is_array( $item ) ? ( $item['id'] ?? null ) : $item;

			if ( empty( $id ) ) {
				return __( 'Request is missing the required "id" parameter.', 'inpost-pay' );
			}
		}

		return true;
	}

	/**
	 * Processes an array of products and saves them as unavailable.
	 *
	 * Each product in the array is expected to have an 'id' and 'delivery_type'.
	 * A new UnavailableModel is created with these attributes and saved.
	 *
	 * @param array $products Array of products to be processed.
	 *
	 * @throws \Exception
	 */
	private function parse_products( array $products ): void {
		foreach ( $products as $product ) {
			$product_id    = (int) ( $product['id'] ?? $product['product_id'] );
			$delivery_type = (string) ( $product['delivery_type'] ?? '' );

			$this->upsert_unavailable( $product_id, $delivery_type, 'product' );
		}
	}

	/**
	 * Processes an array of categories and saves them as unavailable.
	 *
	 * Each category in the array is expected to have an 'id' and 'delivery_type'.
	 * A new UnavailableModel is created with these attributes and saved.
	 *
	 * @param array $categories Array of categories to be processed.
	 *
	 * @throws \Exception
	 */
	private function parse_categories( array $categories ): void {
		foreach ( $categories as $category ) {
			$category_id   = (int) ( $category['id'] ?? $category['category_id'] );
			$delivery_type = (string) ( $category['delivery_type'] ?? '' );

			$this->upsert_unavailable( $category_id, $delivery_type, 'category' );
		}
	}

	/**
	 * Deletes unavailable products or categories.
	 *
	 * Iterates over the $items array and for each item, it creates a new UnavailableModel with the given id and then deletes it.
	 *
	 * @param array $items Array of ids of products or categories to be deleted.
	 */
	private function delete_unavailable( array $items ): void {
		foreach ( $items as $item ) {
			$item_id = is_array( $item ) ? ( $item['id'] ?? $item ) : $item;

			$entity = $this->repository->find( $item_id );

			if ( $entity ) {
				$this->repository->delete( $entity );
			}
		}
	}

	/**
	 * Retrieves all unavailable products.
	 *
	 * Fetches products marked as unavailable from the model and transforms them into an array format.
	 * Each product in the array is augmented with its title and edit link.
	 * Returns a WP_REST_Response containing the list of unavailable products.
	 *
	 * @return \WP_REST_Response Response object containing unavailable products.
	 */
	private function get_unavailable_products(): WP_REST_Response {
		$unavailable_products = $this->repository->get_all_products();

		$unavailable_products = array_map(
			static function ( UnavailableEntity $entity ) {
				$product_id = $entity->get_product_id();

				return array(
					'id'            => $entity->get_id(),
					'product_id'    => $product_id,
					'delivery_type' => $entity->get_delivery_type(),
					'title'         => get_the_title( $product_id ),
					'category'      => get_the_category( $product_id ),
					'edit_link'     => get_edit_post_link( $product_id, 'raw' ),
				);
			},
			$unavailable_products
		);

		return new WP_REST_Response(
			array(
				'products' => $unavailable_products,
			),
			200
		);
	}

	/**
	 * Retrieves all unavailable categories with subcategories included.
	 *
	 * Fetches categories marked as unavailable from the model and transforms them into a flat array format.
	 * Categories are sorted hierarchically (parent followed by children) without nesting.
	 * Each category in the array is augmented with its title and edit link.
	 * Returns a WP_REST_Response containing the paginated list of unavailable categories.
	 *
	 * @param WP_REST_Request $request The request object.
	 *
	 * @return \WP_REST_Response Response object containing unavailable categories.
	 */
	private function get_unavailable_categories( WP_REST_Request $request ): WP_REST_Response {
		$unavailable_categories = $this->repository->get_all_categories();
		$search                 = $request->get_param( 'search' );
		$page                   = $request->get_param( 'page' ) ?: 1;
		$per_page               = $request->get_param( 'per_page' ) ?: 10;

		$args = array(
			'type'         => 'product',
			'hide_empty'   => false,
			'hierarchical' => true,
			'taxonomy'     => 'product_cat',
		);

		if ( $search ) {
			$args['search'] = $search;
		}

		$all_categories       = get_categories( $args );
		$flattened_categories = $this->flatten_categories_hierarchically( $all_categories );
		$total                = count( $flattened_categories );
		$categories           = array_slice( $flattened_categories, ( $page - 1 ) * $per_page, $per_page );
		$max_pages            = ceil( $total / $per_page );

		$unavailable_map = array();

		foreach ( $unavailable_categories as $entity ) {
			$unavailable_map[ $entity->get_category_id() ] = array(
				'id'            => $entity->get_id(),
				'delivery_type' => $entity->get_delivery_type(),
			);
		}

		$categories_map = array();

		foreach ( $categories as $category ) {
			$categories_map[] = array(
				'id'            => $unavailable_map[ $category->term_id ]['id'] ?? null,
				'delivery_type' => $unavailable_map[ $category->term_id ]['delivery_type'] ?? null,
				'category_id'   => $category->term_id,
				'title'         => $category->name,
				'edit_link'     => get_edit_term_link( $category->term_id, 'product_cat' ),
			);
		}

		return new WP_REST_Response(
			array(
				'total_categories' => $total,
				'current_page'     => (int) $page,
				'total_pages'      => $max_pages,
				'categories'       => $categories_map,
			),
			200
		);
	}

	/**
	 * Flattens category hierarchy into a single-level array preserving hierarchical order.
	 *
	 * Takes a hierarchical array of categories and returns a flat array where parent categories
	 * are immediately followed by their children in the correct order (parent -> children -> next parent).
	 *
	 * @param array $categories Array of WP_Term category objects.
	 * @param int   $parent_id  Current parent ID for recursion (default: 0 for root level).
	 *
	 * @return array Flattened array of categories in hierarchical order.
	 */
	private function flatten_categories_hierarchically( array $categories, int $parent_id = 0 ): array {
		$flattened = array();

		foreach ( $categories as $category ) {
			if ( (int) $category->parent === $parent_id ) {
				$flattened[] = $category;
				$children    = $this->flatten_categories_hierarchically( $categories, $category->term_id );
				$flattened   = array_merge( $flattened, $children );
			}
		}

		return $flattened;
	}

	/**
	 * Deletes all unavailable products.
	 *
	 * Handles the `/inpost/v1/izi/unavailable/remove/products` endpoint.
	 *
	 * @param WP_REST_Request $request The request object.
	 *
	 * @return \WP_REST_Response The response object.
	 */
	private function delete_all_products( WP_REST_Request $request ): WP_REST_Response {
		$deleted = $this->repository->delete_all_products();

		if ( 0 === $deleted ) {
			return new WP_REST_Response(
				array(
					'message' => __( 'No unavailable products to delete.', 'inpost-pay' ),
				),
				200
			);
		}

		return new WP_REST_Response(
			array(
				'message' => sprintf( __( 'Deleted %d unavailable products.', 'inpost-pay' ), $deleted ),
			),
			200
		);
	}

	/**
	 * Handles upserting unavailable entities (product or category).
	 *
	 * @param int    $object_id     Product or category ID.
	 * @param string $delivery_type Delivery type.
	 * @param string $type          'product' or 'category'.
	 *
	 * @return void
	 */
	private function upsert_unavailable( int $object_id, string $delivery_type, string $type ): void {
		$field = ( 'product' === $type ) ? 'product_id' : 'category_id';

		$existing = $this->repository->find_by(
			array( $field => $object_id )
		);

		$count = count( $existing );

		if ( 1 === $count ) {
			/**
			 * Get the first entity from the array.
			 *
			 * @var UnavailableEntity $entity
			 */
			$entity = $existing[0];
			$entity->set_delivery_type( $delivery_type );

			$this->repository->save( $entity );
			return;
		}

		if ( $count > 1 ) {
			$this->repository->delete_many( $existing );
		}

		$entity = new UnavailableEntity();

		if ( 'product' === $type ) {
			$entity->set_product_id( $object_id );
		} else {
			$entity->set_category_id( $object_id );
		}

		$entity->set_delivery_type( $delivery_type );

		$this->repository->save( $entity );
	}
}
