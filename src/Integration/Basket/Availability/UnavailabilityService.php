<?php

/**
 * Unavailability service.
 *
 * @package Ilabs\Inpost_Pay
 */

declare( strict_types=1 );

namespace Ilabs\Inpost_Pay\Integration\Basket\Availability;

use Ilabs\Inpost_Pay\Container\ServiceContainer;
use Ilabs\Inpost_Pay\EntityLayer\Entity\UnavailableEntity;
use Ilabs\Inpost_Pay\EntityLayer\Repository\UnavailableRepository;
use Ilabs\Inpost_Pay\Logger;
use WC_Product;

/**
 * Unavailability service.
 *
 * Responsible for determining whether InPost Pay widget can be displayed
 * based on product/category unavailability rules and current basket contents.
 *
 * @package Ilabs\Inpost_Pay
 */
class UnavailabilityService {

	/**
	 * Service key for DI container.
	 */
	public const SERVICE_KEY = 'service.unavailability_service';

	/**
	 * Unavailability repository.
	 *
	 * @var UnavailableRepository
	 */
	private UnavailableRepository $repository;

	/**
	 * Flag tracking current unavailability status.
	 *
	 * @var bool
	 */
	private bool $is_unavailable = false;

	/**
	 * Cache for product unavailability (in-memory)
	 *
	 * @var array
	 */
	private static array $product_cache = array();

	/**
	 * Cache for category IDs (in-memory)
	 *
	 * @var array|null
	 */
	private static ?array $category_ids_cache = null;

	/**
	 * Cache for unavailable entities by product_id
	 *
	 * @var array
	 */
	private static array $product_entity_cache = array();

	/**
	 * Cache for unavailable entities by category_id
	 *
	 * @var array
	 */
	private static array $category_entity_cache = array();

	/**
	 * Cache for category hierarchies (category_id => [category_id, parent_id, grandparent_id, ...])
	 *
	 * @var array
	 */
	private static array $category_hierarchy_cache = array();

	/**
	 * Constructor.
	 *
	 * @param ServiceContainer $container DI container.
	 *
	 * @return void
	 */
	public function __construct( ServiceContainer $container ) {
		$this->repository = $container->get( UnavailableRepository::SERVICE_KEY );
	}

	/**
	 * Checks if the widget should be displayed on a product page.
	 * The widget is unavailable to display if the product is disabled for both delivery types.
	 * If the product is available for one of the delivery types, the widget is available to display
	 * if there is at least one available item in the basket.
	 *
	 * @param int $product_id Product ID.
	 *
	 * @return bool
	 */
	public function is_unavailable_to_display_on_product_page( int $product_id ): bool {
		$product = new WC_Product( $product_id );

		// Check cache first
		$cache_key = 'display_' . $product_id;
		if ( isset( self::$product_cache[ $cache_key ] ) ) {
			return self::$product_cache[ $cache_key ];
		}

		$product_unavailable = $this->get_product_unavailable_cached( $product_id );

		// Get the category IDs of the current product (including parent categories)
		$unavailable_category_ids = $this->get_category_ids_cached();
		$product_category_ids     = $this->get_all_category_ids_including_parents( $product->get_category_ids() );

		// Check each unavailable category ID to see if it matches a product category ID
		foreach ( $unavailable_category_ids as $unavailable_category_id ) {
			$category_id = is_array( $unavailable_category_id ) ? $unavailable_category_id[0] : $unavailable_category_id;

			if ( in_array( $category_id, $product_category_ids, true ) ) {
				$category_unavailable = $this->get_category_unavailable_cached( $category_id );

				if ( $category_unavailable && $category_unavailable->get_delivery_type() === UnavailableEntity::BOTH ) {
					$this->is_unavailable = true;
				} else {
					$this->is_unavailable = false;
				}
			}
		}

		if ( $product_unavailable && $product_unavailable->get_delivery_type() === UnavailableEntity::BOTH ) {
			$this->is_unavailable = true;
		} elseif ( $product_unavailable ) {
			$this->is_unavailable = false;
		}

		if ( $this->is_unavailable ) {
			self::$product_cache[ $cache_key ] = true;

			return true;
		}

		// If the product is available, check if it's available in the basket and return the result
		$result                            = ! $this->is_basket_available();
		self::$product_cache[ $cache_key ] = $result;

		return $result;
	}

	/**
	 * Get product unavailable entity (with cache)
	 *
	 * @param int $product_id Product ID.
	 *
	 * @return UnavailableEntity|null
	 */
	private function get_product_unavailable_cached( int $product_id ): ?UnavailableEntity {
		if ( isset( self::$product_entity_cache[ $product_id ] ) ) {
			return self::$product_entity_cache[ $product_id ];
		}

		$entity = $this->repository->find_one_by( array( 'product_id' => $product_id ) );

		self::$product_entity_cache[ $product_id ] = $entity;

		return $entity;
	}

	/**
	 * Get category unavailable entity (with cache)
	 *
	 * @param int $category_id Category ID.
	 *
	 * @return UnavailableEntity|null
	 */
	private function get_category_unavailable_cached( int $category_id ): ?UnavailableEntity {
		if ( isset( self::$category_entity_cache[ $category_id ] ) ) {
			return self::$category_entity_cache[ $category_id ];
		}

		$entity = $this->repository->find_one_by( array( 'category_id' => $category_id ) );

		self::$category_entity_cache[ $category_id ] = $entity;

		return $entity;
	}

	/**
	 * Get category IDs (with cache)
	 *
	 * @return array
	 */
	private function get_category_ids_cached(): array {
		if ( null !== self::$category_ids_cache ) {
			return self::$category_ids_cache;
		}

		self::$category_ids_cache = $this->repository->get_category_ids();

		return self::$category_ids_cache;
	}

	/**
	 * Get all category IDs including parent categories (with cache).
	 *
	 * For a given array of category IDs, returns all IDs plus their parent categories
	 * all the way up the hierarchy. This ensures that unavailability rules applied to
	 * parent categories are correctly inherited by products in subcategories.
	 *
	 * @param array $category_ids Array of category IDs.
	 *
	 * @return array All category IDs including parents.
	 */
	private function get_all_category_ids_including_parents( array $category_ids ): array {
		$all_ids = array();

		foreach ( $category_ids as $category_id ) {
			// Check cache first
			if ( isset( self::$category_hierarchy_cache[ $category_id ] ) ) {
				array_push( $all_ids, ...self::$category_hierarchy_cache[ $category_id ] );
				continue;
			}

			// Build hierarchy
			$hierarchy = array( $category_id );
			$ancestors = get_ancestors( $category_id, 'product_cat', 'taxonomy' );

			if ( ! empty( $ancestors ) ) {
				array_push( $hierarchy, ...$ancestors );
			}

			// Cache the result
			self::$category_hierarchy_cache[ $category_id ] = $hierarchy;

			array_push( $all_ids, ...$hierarchy );
		}

		return array_unique( $all_ids );
	}

	/**
	 * Preload unavailability for multiple products (bulk optimization)
	 *
	 * @param array $product_ids Product IDs.
	 *
	 * @return void
	 */
	public function preload_products( array $product_ids ): void {
		if ( empty( $product_ids ) ) {
			return;
		}

		// Filter out already cached
		$to_load = array_diff( $product_ids, array_keys( self::$product_entity_cache ) );

		if ( empty( $to_load ) ) {
			Logger::log( '[UNAVAILABILITY] All products already cached' );

			return;
		}

		// Use repository to bulk load (implement in repo if needed)
		foreach ( $to_load as $product_id ) {
			$this->get_product_unavailable_cached( $product_id );
		}

		Logger::log(
			sprintf(
				'[UNAVAILABILITY] Preloaded %d products',
				count( $to_load )
			)
		);
	}

	/**
	 * Checks if the widget should be displayed on other pages than product page.
	 * The widget is available to display if there is at least one available item in the basket.
	 *
	 * @return bool
	 */
	public function is_available_to_display_on_other_pages(): bool {
		return $this->is_basket_available();
	}

	/**
	 * Check if there is at least one available item in the basket.
	 *
	 * @return bool
	 */
	public function is_basket_available(): bool {
		// Skip check if cart doesn't exist (cache-friendly pages)
		if ( ! WC()->cart ) {
			return true;
		}

		$this->load_basket_from_session();
		$basket = WC()->cart->get_cart_contents();

		if ( empty( $basket ) ) {
			return true;
		}

		$has_available_item = false;

		foreach ( $basket as $key => $item ) {
			// Case 1: Item already processed with availability flag
			if ( isset( $item['_inpostpay_unavailable'] ) ) {
				if ( $item['_inpostpay_unavailable'] === true ) {
					// Check if BOTH delivery methods are unavailable
					if ( isset( $item['_inpostpay_unavailable_delivery_type'] ) &&
						$item['_inpostpay_unavailable_delivery_type'] === UnavailableEntity::BOTH ) {
						continue;
					}

					$has_available_item = true;
					break;
				}
				$has_available_item = true;
				break;
			}

			// Case 2: Item not yet processed - use cached lookup
			$basket_item_unavailable = $this->get_product_unavailable_cached( $item['product_id'] );
			$delivery_type           = null;

			if ( $basket_item_unavailable && $basket_item_unavailable->get_delivery_type() ) {
				$basket[ $key ]['_inpostpay_unavailable']               = true;
				$basket[ $key ]['_inpostpay_unavailable_delivery_type'] = $basket_item_unavailable->get_delivery_type();
				$delivery_type = $basket_item_unavailable->get_delivery_type();
			}

			$unavailable_category_ids = $this->get_category_ids_cached();
			$product_category_ids     = $this->get_all_category_ids_including_parents( $item['data']->get_category_ids() );

			$flat_unavailable_category_ids = array_map(
				static function ( $item ) {
					return is_array( $item ) ? $item[0] : $item;
				},
				$unavailable_category_ids
			);

			$matching_category_ids = array_intersect( $product_category_ids, $flat_unavailable_category_ids );

			if ( ! empty( $matching_category_ids ) ) {
				$category_id          = reset( $matching_category_ids );
				$category_unavailable = $this->get_category_unavailable_cached( $category_id );

				if ( $category_unavailable ) {
					$basket[ $key ]['_inpostpay_unavailable']               = true;
					$basket[ $key ]['_inpostpay_unavailable_delivery_type'] = $category_unavailable->get_delivery_type();

					// If both product and category have restrictions, use the most restrictive (BOTH)
					if ( $delivery_type !== null ) {
						if ( $delivery_type === UnavailableEntity::BOTH ||
							$category_unavailable->get_delivery_type() === UnavailableEntity::BOTH ) {
							$delivery_type = UnavailableEntity::BOTH;
						} elseif ( $delivery_type !== $category_unavailable->get_delivery_type() ) {
							$delivery_type = UnavailableEntity::BOTH;
						}
					} else {
						$delivery_type = $category_unavailable->get_delivery_type();
					}
				}
			}

			if ( UnavailableEntity::BOTH === $delivery_type ) {
				$basket[ $key ]['_inpostpay_unavailable']               = true;
				$basket[ $key ]['_inpostpay_unavailable_delivery_type'] = UnavailableEntity::BOTH;
			} elseif ( UnavailableEntity::APM === $delivery_type || UnavailableEntity::COURIER === $delivery_type ) {
				$basket[ $key ]['_inpostpay_unavailable']               = true;
				$basket[ $key ]['_inpostpay_unavailable_delivery_type'] = $delivery_type;
				$has_available_item                                     = true;
				break;
			} else {
				$basket[ $key ]['_inpostpay_unavailable'] = false;
				$has_available_item                       = true;
				break;
			}
		}

		WC()->cart->set_cart_contents( $basket );

		return $has_available_item;
	}

	/**
	 * Gets unavailable delivery types for a given product.
	 *
	 * @param WC_Product $product Product.
	 *
	 * @return string|null Unavailable delivery type.
	 */
	public function unavailable_delivery_types_for_product( WC_Product $product ): ?string {
		$product_id = $product->get_id();
		if ( $product->is_type( 'variable' ) || $product->is_type( 'variation' ) ) {
			$product_id = $product->get_parent_id();
		}

		$product_category_ids = wp_get_post_terms( $product_id, 'product_cat', array( 'fields' => 'ids' ) );
		$all_category_ids     = $this->get_all_category_ids_including_parents( $product_category_ids );
		// Logger::log( 'ALL PRODUCT CATEGORIES: ' . var_export( $all_category_ids, true ) . ' FOR PRODUCT: ' . $product_id . '' );
		$unavailable_category_ids = $this->get_category_ids_cached();

		$flattened_category_ids = array_map(
			static function ( $item ) {
				return is_array( $item ) ? $item[0] : $item;
			},
			$unavailable_category_ids
		);

		$matching_category_ids = array_intersect( $all_category_ids, $flattened_category_ids );

		$unavailable_delivery_type = null;
		// Logger::log( 'MATHING: ' . var_export( $matching_category_ids, true ) . ' FOR PRODUCT: ' . $product_id . '' );

		if ( ! empty( $matching_category_ids ) ) {
			$category_unavailable = $this->get_category_unavailable_cached( reset( $matching_category_ids ) );
			// Logger::log( 'DELIVERYTYPE: ' . var_export( $category_unavailable, true ) . ' FOR PRODUCT: ' . $product_id . '' );
			$unavailable_delivery_type = $category_unavailable ? $category_unavailable->get_delivery_type() : null;
			Logger::log( 'UNAVAILABILITY: ' . $unavailable_delivery_type . ' FOR PRODUCT: ' . $product_id . '' );
		}

		/* @var UnavailableEntity|null $product_unavailable **/
		$product_unavailable               = $this->get_product_unavailable_cached( $product_id );
		$unavailable_delivery_type_product = $product_unavailable ? $product_unavailable->get_delivery_type() : null;

		if ( $unavailable_delivery_type_product ) {
			return $unavailable_delivery_type_product;
		}

		return $unavailable_delivery_type;
	}

	/**
	 * Loads basket from session.
	 * Skips if cart doesn't exist (no session initialized).
	 *
	 * @return void
	 */
	public function load_basket_from_session(): void {
		if ( ! WC()->cart ) {
			return;
		}

		WC()->cart->get_cart_from_session();
	}

	/**
	 * Clear cache (call after updating unavailable products/categories in admin)
	 *
	 * @return void
	 */
	public static function clear_cache(): void {
		self::$product_cache            = array();
		self::$category_ids_cache       = null;
		self::$product_entity_cache     = array();
		self::$category_entity_cache    = array();
		self::$category_hierarchy_cache = array();
		Logger::log( '[UNAVAILABILITY] Cache cleared' );
	}
}
