<?php

namespace Ilabs\Inpost_Pay\Integration\BLPaczka;

class BLPaczka_Integration {
	public bool $IS_ACTIVE = false;

	public function __construct() {
		$this->IS_ACTIVE = self::is_active();
	}

	/**
	 * Adds a nonce to the checkout process for BLPaczka pickup action.
	 *
	 * This function generates a nonce for the BLPaczka pickup action and
	 * adds it to the $_POST array, ensuring that the action is secured
	 * against CSRF attacks. The nonce is only added if the BLPaczka plugin
	 * is active.
	 */
	public function add_nonce_to_checkout(): void {
		if ( $this->IS_ACTIVE ) {
			$_POST['blpaczka_pickup_nonce'] = wp_create_nonce( 'blpaczka_pickup_action' );
		}
	}


	/**
	 * Check if BLPaczka plugin is active
	 *
	 * @return bool
	 */
	public static function is_active(): bool {
		return is_plugin_active( 'blpaczka/blpaczka.php' );
	}
}
