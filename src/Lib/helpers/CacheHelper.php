<?php
/**
 * Cache Helper class.
 *
 * @package Ilabs\Inpost_Pay\Lib\Helpers
 */

declare( strict_types=1 );

namespace Ilabs\Inpost_Pay\Lib\helpers;

use WP_Object_Cache;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Helper class for managing WordPress cache operations.
 */
class CacheHelper {
	/**
	 * Cache group name.
	 *
	 * @var string
	 */
	const CACHE_GROUP = 'inpost-pay';

	/**
	 * Object cache enabled flag.
	 *
	 * @var bool|null
	 */
	private static ?bool $object_cache_enabled = null;

	/**
	 * Disable WordPress cache.
	 *
	 * Flushes cache group or entire cache depending on cache backend capabilities.
	 *
	 * @return void
	 */
	public static function disable_wp_cache(): void {
		if ( self::is_object_cache() ) {
			if ( wp_cache_supports( 'flush_group' ) ) {
				wp_cache_flush_group( self::CACHE_GROUP );
			}
		} else {
			// File-based or no persistent cache (including W3 Total Cache disk mode): flush to prevent stale data across requests.
			wp_cache_flush();
		}
	}

	/**
	 * Check if object cache is enabled.
	 *
	 * Detects Redis, Memcached, or LiteSpeed object cache.
	 *
	 * @return bool True if object cache is enabled.
	 */
	public static function is_object_cache(): bool {
		if ( null !== self::$object_cache_enabled ) {
			return self::$object_cache_enabled;
		}
		if ( self::is_redis_cache() ) {
			self::$object_cache_enabled = true;
		}
		if ( self::is_memcached() ) {
			self::$object_cache_enabled = true;
		}
		if ( self::is_ls_object_cache() ) {
			self::$object_cache_enabled = true;
		}
		if ( null === self::$object_cache_enabled ) {
			self::$object_cache_enabled = false;
		}

		return self::$object_cache_enabled;
	}

	/**
	 * Check if Redis cache is enabled.
	 *
	 * @return bool True if Redis cache is active.
	 */
	private static function is_redis_cache(): bool {
		global $wp_object_cache;
		if ( isset( $wp_object_cache ) && $wp_object_cache instanceof WP_Object_Cache ) {
			if ( method_exists( $wp_object_cache, 'redis_status' ) ) {
				return (bool) $wp_object_cache->redis_status();
			}
		}

		return false;
	}

	/**
	 * Check if Memcached is enabled.
	 *
	 * @return bool True if Memcached is active.
	 */
	private static function is_memcached(): bool {
		global $wp_object_cache;
		if ( isset( $wp_object_cache )
			&& is_object( $wp_object_cache )
			&& property_exists( $wp_object_cache, 'm' )
			&& $wp_object_cache->m instanceof \Memcached
		) {
			return true;
		}

		return false;
	}

	/**
	 * Check if LiteSpeed object cache is enabled.
	 *
	 * @return bool True if LiteSpeed cache is active.
	 */
	private static function is_ls_object_cache(): bool {
		if ( class_exists( \LiteSpeed\Object_Cache::class ) ) {
			// @phpstan-ignore-next-line LiteSpeed plugin classes are optional.
			return (bool) apply_filters( 'litespeed_conf', \LiteSpeed\Base::O_CACHE );
		}

		return false;
	}

	/**
	 * Set cache data.
	 *
	 * @param string $key Cache key.
	 * @param mixed  $data Data to cache.
	 * @param int    $cache_time Cache expiration time in seconds. Default 3600.
	 *
	 * @return void
	 */
	public static function set_cache_data( string $key, $data, int $cache_time = 3600 ): void {
		if ( self::is_object_cache() ) {
			wp_cache_set( $key, $data, self::get_cache_group(), $cache_time );
		}
	}

	/**
	 * Get cache group name.
	 *
	 * @return string Cache group identifier.
	 */
	public static function get_cache_group(): string {
		return self::CACHE_GROUP;
	}

	/**
	 * Get cache data.
	 *
	 * @param string $key Cache key.
	 *
	 * @return mixed|false Cached data or false if not found.
	 */
	public static function get_cache_data( string $key ) {
		if ( self::is_object_cache() ) {
			return wp_cache_get( $key, self::get_cache_group(), true );
		}

		return false;
	}

	/**
	 * Flush entire cache.
	 *
	 * @return void
	 */
	public static function flush_cache(): void {
		wp_cache_flush();
	}
}
