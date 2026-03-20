<?php
/**
 * Interface defining cache operations for entity repositories.
 *
 * @package Ilabs\Inpost_Pay\EntityLayer\Cache
 */

declare( strict_types=1 );

namespace Ilabs\Inpost_Pay\EntityLayer\Cache;

/**
 * Interface CacheInterface
 *
 * Provides basic cache methods for storing and retrieving entities.
 */
interface CacheInterface {

	/**
	 * Retrieve a value from the cache.
	 *
	 * @param string $key The cache key.
	 *
	 * @return mixed The cached value, or null if not found.
	 */
	public function get( string $key );

	/**
	 * Store a value in the cache.
	 *
	 * @param string $key   The cache key.
	 * @param mixed  $value The value to store.
	 * @param int    $ttl   Optional time-to-live in seconds. Default 3600.
	 *
	 * @return bool True on success, false on failure.
	 */
	public function set( string $key, $value, int $ttl = 3600 ): bool;

	/**
	 * Delete a value from the cache.
	 *
	 * @param string $key The cache key to delete.
	 *
	 * @return bool True if the key was deleted, false otherwise.
	 */
	public function delete( string $key ): bool;

	/**
	 * Check if a cache key exists.
	 *
	 * @param string $key The cache key to check.
	 *
	 * @return bool True if the key exists, false otherwise.
	 */
	public function has( string $key ): bool;
}
