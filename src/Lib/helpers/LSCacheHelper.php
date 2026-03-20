<?php

namespace Ilabs\Inpost_Pay\Lib\helpers;

class LSCacheHelper {

	/**
	 * @var bool|null
	 */
	private static ?bool $plugin_active = null;

	private static function is_plugin_active(): bool {
		if ( self::$plugin_active !== null ) {
			return self::$plugin_active;
		}
		if ( is_plugin_active( 'litespeed-cache/litespeed-cache.php' ) ) {
			// LsCache is active
			self::$plugin_active = true;
		} else {
			// LsCache is inactive
			self::$plugin_active = false;
		}

		return self::$plugin_active;
	}

	public static function no_cache() {
		if ( self::is_plugin_active() ) {
			do_action( 'litespeed_control_set_nocache', 'nocache' );
		}
	}

	public static function set_private_cache() {
		if ( self::is_plugin_active() ) {
			do_action( 'litespeed_control_set_private' );
		}
	}
}
