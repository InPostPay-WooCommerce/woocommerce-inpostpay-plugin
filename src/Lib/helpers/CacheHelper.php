<?php

namespace Ilabs\Inpost_Pay\Lib\helpers;

use WP_Object_Cache;

class CacheHelper {
	const CACHE_GROUP = 'inpost-pay';

	private static ?bool $OBJECT_CACHE_ENABLED = null;


	public static function disableWPCache() {
		if ( wp_cache_supports( 'flush_group' ) ) {
			wp_cache_flush_group( 'plugins' );
		} else {
			wp_cache_flush();
		}
	}

	public static function getCacheGroup(): string {
		return self::CACHE_GROUP;
	}

	public static function setCacheData( $key, $data, $cache_time = 3600 ) {
		if ( self::isObjectCache() ) {
			wp_cache_set( $key, $data, self::getCacheGroup(), $cache_time );
		}
	}

	public static function getCacheData( $key ) {
		if ( self::isObjectCache() ) {
			return wp_cache_get( $key, self::getCacheGroup(), true );
		}

		return false;
	}

	public static function isObjectCache(): bool {
		if ( self::$OBJECT_CACHE_ENABLED !== null ) {
			return self::$OBJECT_CACHE_ENABLED;
		}
		if ( self::isRedisCache() ) {
			self::$OBJECT_CACHE_ENABLED = true;
		}
		if ( self::isLsObjectCache() ) {
			self::$OBJECT_CACHE_ENABLED = true;
		}
		if ( self::$OBJECT_CACHE_ENABLED === null ) {
			self::$OBJECT_CACHE_ENABLED = false;
		}

		return self::$OBJECT_CACHE_ENABLED;
	}

	private static function isRedisCache(): bool {
		global $wp_object_cache;
		if ( isset( $wp_object_cache ) && $wp_object_cache instanceof WP_Object_Cache ) {
			if ( method_exists( $wp_object_cache, 'redis_status' ) ) {
				if ( $wp_object_cache->redis_status() ) {
					return true;
				} else {
					return false;
				}
			}
		}

		return false;
	}

	private static function isLsObjectCache(): bool {
		if ( class_exists( \LiteSpeed\Object_Cache::class ) ) {
			return (bool) apply_filters( 'litespeed_conf', \LiteSpeed\Base::O_CACHE );
		}

		return false;
	}

	public static function flushCache() {
		wp_cache_flush();
	}

}
