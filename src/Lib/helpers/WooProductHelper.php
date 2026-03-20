<?php
/**
 * WooProductService class.
 *
 * Provides safe product loading with local caching.
 *
 * @package Ilabs\Inpost_Pay\Lib\helpers
 */

declare( strict_types=1 );

namespace Ilabs\Inpost_Pay\Lib\helpers;

use WC_Product;

/**
 * Class WooProductService
 *
 * Handles safe and cached loading of WooCommerce products.
 */
class WooProductHelper {

    /**
     * Service key for the container.
     *
     * @var string
     */
    public const SERVICE_KEY = 'service.woo_product_helper';

    /**
     * Local in-request cache for loaded products.
     *
     * @var array<int, WC_Product|null>
     */
    private array $cache = array();

    /**
     * Safely loads WooCommerce products by IDs.
     *
     * @param int[] $product_ids List of product IDs to load.
     *
     * @return array<int, WC_Product|null> Products indexed by their IDs.
     */
    public function load_products_safe( array $product_ids ): array {
        if ( empty( $product_ids ) ) {
            return array();
        }

        $missing_ids = array_diff( $product_ids, array_keys( $this->cache ) );

        if ( ! empty( $missing_ids ) ) {
            $products = wc_get_products(
                array(
                    'include' => $missing_ids,
                    'limit'   => -1,
                )
            );

            foreach ( $products as $product ) {
                $this->cache[ $product->get_id() ] = $product;
            }

            foreach ( $missing_ids as $id ) {
                if ( ! isset( $this->cache[ $id ] ) ) {
                    $this->cache[ $id ] = null;
                }
            }
        }

        $result = array();
        foreach ( $product_ids as $id ) {
            $result[ $id ] = $this->cache[ $id ] ?? null;
        }

        return $result;
    }

    /**
     * Safely loads a single product by ID.
     *
     * @param int $product_id Product ID.
     *
     * @return WC_Product|null Product instance or null if not found.
     */
    public function load_product_safe( int $product_id ): ?WC_Product {
        $products = $this->load_products_safe( array( $product_id ) );

        return $products[ $product_id ] ?? null;
    }

    /**
     * Clears the local cache.
     *
     * @return void
     */
    public function clear_cache(): void {
        $this->cache = array();
    }
}
