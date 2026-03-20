<?php
/**
 * WordPress-based cache implementation for entity repositories.
 *
 * @package Ilabs\Inpost_Pay\EntityLayer\Cache
 */

declare( strict_types=1 );

namespace Ilabs\Inpost_Pay\EntityLayer\Cache;

/**
 * Class WordPressCache
 *
 * Provides a cache implementation using WordPress transients.
 */
class WordPressCache implements CacheInterface {

	public const SERVICE_KEY = 'entity_layer.cache.wordpress';

	/**
	 * Prefix for transient keys.
	 *
	 * @var string
	 */
	private string $prefix;

	/**
	 * Constructor.
	 *
	 * @param string $prefix Optional key prefix for transients. Default 'wp_entity_'.
	 */
	public function __construct( string $prefix = 'wp_entity_' ) {
		$this->prefix = $prefix;
	}

	/**
	 * Retrieve a value from cache.
	 *
	 * @param string $key The cache key.
	 *
	 * @return mixed The cached value, or false if not found.
	 */
	public function get( string $key ) {
		return get_transient( $this->prefix . $key );
	}

	/**
	 * Store a value in cache.
	 *
	 * @param string $key   The cache key.
	 * @param mixed  $value The value to store.
	 * @param int    $ttl   Optional time-to-live in seconds. Default 3600.
	 *
	 * @return bool True on success, false on failure.
	 */
	public function set( string $key, $value, int $ttl = 3600 ): bool {
		return (bool) set_transient( $this->prefix . $key, $value, $ttl );
	}

	/**
	 * Delete a value from cache.
	 *
	 * @param string $key The cache key to delete.
	 *
	 * @return bool True if deleted, false otherwise.
	 */
	public function delete( string $key ): bool {
		return (bool) delete_transient( $this->prefix . $key );
	}

	/**
	 * Check if a cache key exists.
	 *
	 * @param string $key The cache key.
	 *
	 * @return bool True if the key exists, false otherwise.
	 */
	public function has( string $key ): bool {
		return ( false !== $this->get( $key ) );
	}
}
