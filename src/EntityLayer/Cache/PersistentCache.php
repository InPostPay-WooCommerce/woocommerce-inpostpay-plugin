<?php
/**
 * Persistent cache implementation resistant to cache flushes.
 *
 * @package Ilabs\Inpost_Pay\Lib
 * @since 2.0.7
 */

declare( strict_types=1 );

namespace Ilabs\Inpost_Pay\EntityLayer\Cache;

/**
 * Two-tier caching system (object cache + wp_options).
 *
 * Data is stored as JSON in wp_options and cached in object cache.
 * Resistant to wp_cache_flush() as it always falls back to wp_options.
 */
class PersistentCache implements CacheInterface {

	/**
	 * Option prefix.
	 *
	 * @var string
	 */
	private string $option_prefix = 'inpost_pay_cache_';

	/**
	 * Cache group for object cache.
	 *
	 * @var string
	 */
	private string $cache_group = 'inpost_pay_persistent';

	/**
	 * Set value in cache.
	 *
	 * @param string $key        Cache key.
	 * @param mixed  $value      Value to cache.
	 * @param int    $expiration Expiration in seconds (0 = no expiration).
	 *
	 * @return bool Success status.
	 */
	public function set( string $key, $value, int $expiration = 0 ): bool {
		$option_name     = $this->option_prefix . $key;
		$expiration_time = $expiration > 0 ? time() + $expiration : 0;

		$data = array(
			'value'      => $value,
			'expiration' => $expiration_time,
			'updated_at' => time(),
		);

		$result = update_option( $option_name, $data, false );

		if ( $result ) {
			// Update object cache.
			wp_cache_set( $key, $value, $this->cache_group, $expiration );
		}

		return $result;
	}

	/**
	 * Check if key exists and is not expired.
	 *
	 * @param string $key Cache key.
	 *
	 * @return bool True if exists and valid.
	 */
	public function has( string $key ): bool {
		return null !== $this->get( $key );
	}

	/**
	 * Get value from cache.
	 *
	 * @param string $key Cache key.
	 *
	 * @return mixed|null Value or null if not found/expired.
	 */
	public function get( string $key ) {
		// Try object cache first.
		$cached = wp_cache_get( $key, $this->cache_group );
		if ( false !== $cached ) {
			return $cached;
		}

		// Fallback to wp_options.
		$option_name = $this->option_prefix . $key;
		$data        = get_option( $option_name );

		if ( false === $data ) {
			return null;
		}

		// Check expiration.
		if ( isset( $data['expiration'] ) && $data['expiration'] > 0 && $data['expiration'] < time() ) {
			$this->delete( $key );
			return null;
		}

		$value = $data['value'] ?? null;

		// Store in object cache for subsequent requests.
		if ( null !== $value ) {
			wp_cache_set( $key, $value, $this->cache_group );
		}

		return $value;
	}

	/**
	 * Delete value from cache.
	 *
	 * @param string $key Cache key.
	 *
	 * @return bool Success status.
	 */
	public function delete( string $key ): bool {
		$option_name = $this->option_prefix . $key;
		$result      = delete_option( $option_name );

		// Remove from object cache.
		wp_cache_delete( $key, $this->cache_group );

		return $result;
	}

	/**
	 * Clear all expired entries.
	 *
	 * @return int Number of deleted entries.
	 */
	public function clear_expired(): int {
		global $wpdb;

		$pattern = $this->option_prefix . '%';
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Maintenance operation to find expired cache entries.
		$options = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT option_name, option_value FROM {$wpdb->options} WHERE option_name LIKE %s",
				$pattern
			)
		);

		$deleted       = 0;
		$now           = time();
		$prefix_length = strlen( $this->option_prefix );

		foreach ( $options as $option ) {
			$data = maybe_unserialize( $option->option_value );
			if ( is_array( $data ) && isset( $data['expiration'] ) && $data['expiration'] > 0 && $data['expiration'] < $now ) {
				if ( delete_option( $option->option_name ) ) {
					// Remove from object cache.
					$key = substr( $option->option_name, $prefix_length );
					wp_cache_delete( $key, $this->cache_group );
					++$deleted;
				}
			}
		}

		return $deleted;
	}

	/**
	 * Clear all cache entries.
	 *
	 * @return int Number of deleted entries.
	 */
	public function flush(): int {
		global $wpdb;

		$pattern = $this->option_prefix . '%';
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Maintenance operation to find all cache entries for deletion.
		$options = $wpdb->get_col(
			$wpdb->prepare(
				"SELECT option_name FROM {$wpdb->options} WHERE option_name LIKE %s",
				$pattern
			)
		);

		$deleted       = 0;
		$prefix_length = strlen( $this->option_prefix );

		foreach ( $options as $option_name ) {
			if ( delete_option( $option_name ) ) {
				// Remove from object cache.
				$key = substr( $option_name, $prefix_length );
				wp_cache_delete( $key, $this->cache_group );
				++$deleted;
			}
		}

		return $deleted;
	}
}
