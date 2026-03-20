<?php

namespace Ilabs\Inpost_Pay\Lib;

use Ilabs\Inpost_Pay\Exception\SessionNotInitializedException;
use Ilabs\Inpost_Pay\Logger;

class BindingProvider {
	private static ?bool $is_bound = null;

	public static function getBinding(): bool {
		if ( self::$is_bound === null ) {
			if ( InPostIzi::getStorage()->issetSession( 'is_bound' ) ) {
				self::$is_bound = InPostIzi::getStorage()->findSession( 'is_bound' );
			} else {
				self::$is_bound = false;
			}
		}

		return self::$is_bound;
	}

	public static function setBinding(): void {
		InPostIzi::getStorage()->insertSession( 'is_bound', true );
		self::$is_bound = true;
	}

	public static function unsetBinding(): void {
		self::$is_bound = false;
		try {
			InPostIzi::getStorage()->insertSession( 'is_bound', false );
		} catch ( SessionNotInitializedException $e ) {
			// WC session not available (e.g. admin context without frontend cookie).
			// In-memory state is already updated above — no persistence needed.
			Logger::log( '[BindingProvider] unsetBinding: session not available, skipping persistence.' );
		}
	}
}
