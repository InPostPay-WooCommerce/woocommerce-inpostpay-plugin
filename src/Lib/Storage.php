<?php

/**
 * Storage.
 *
 * @package Ilabs\Inpost_Pay
 */

namespace Ilabs\Inpost_Pay\Lib;

use Ilabs\Inpost_Pay\Exception\SessionNotInitializedException;
use Ilabs\Inpost_Pay\Lib\helpers\Woo_Commerce_Session_Helper;

/**
 * Session storage wrapper for WooCommerce.
 *
 * Provides a thin abstraction over `WC_Session` for setting, getting and
 * removing values used by the InPost Pay plugin.
 *
 * Session is NOT initialized by this class - it must be explicitly initialized
 * in Add.php and Binding.php REST endpoints via CartSessionService::initiate_wc_cart().
 * All methods here operate on existing session or throw exception/return null.
 *
 * @package Ilabs\Inpost_Pay
 */
class Storage {

	/**
	 * Storage constructor.
	 *
	 * Session is initialized explicitly in Add.php and Binding.php via initiate_wc_cart().
	 * All methods here just read/write to WC()->session if it exists.
	 */
	public function __construct() {
		if ( Woo_Commerce_Session_Helper::has_session_cookie() && ! WC()->session ) {
			WC()->session = new \WC_Session_Handler();
			WC()->session->init();
		}
	}

	/**
	 * Store a value in WooCommerce session.
	 * Does NOT initialize session - throws exception if session doesn't exist.
	 *
	 * @param mixed $key   Session key.
	 * @param mixed $value Session value.
	 *
	 * @return void
	 * @throws SessionNotInitializedException When session not initialized.
	 */
	public function insertSession( $key, $value ) {
		if ( ! function_exists( 'WC' ) || ! WC()->session ) {
			throw new SessionNotInitializedException(
				'Cannot write to session - WC session not initialized. Initialize session in REST endpoints (Add.php/Binding.php) before calling insertSession().'
			);
		}

		WC()->session->set( $key, $value );
	}

	/**
	 * Get a value from WooCommerce session.
	 * Does NOT initialize session - returns null if session doesn't exist.
	 *
	 * @param mixed $key Session key.
	 *
	 * @return mixed|null Session value or null when session not initialized or key not set.
	 */
	public function findSession( $key ) {
		// Don't initialize session - just read if it exists
		if ( ! function_exists( 'WC' ) || ! WC()->session ) {
			return null;
		}

		if ( isset( WC()->session->$key ) ) {
			return WC()->session->get( $key );
		}

		return null;
	}

	/**
	 * Check if a session key exists.
	 * Does NOT initialize session - returns false if session doesn't exist.
	 *
	 * @param mixed $key Session key.
	 *
	 * @return bool True when key exists, false when session not initialized or key not set.
	 */
	public function issetSession( $key ): bool {
		if ( ! function_exists( 'WC' ) || ! WC()->session ) {
			return false;
		}

		return isset( WC()->session->$key );
	}

	/**
	 * Remove a value from WooCommerce session.
	 * Does NOT initialize session - throws exception if session doesn't exist.
	 *
	 * @param mixed $key Session key.
	 *
	 * @return void
	 * @throws SessionNotInitializedException When session not initialized.
	 */
	public function eraseSession( $key ): void {
		if ( ! function_exists( 'WC' ) || ! WC()->session ) {
			throw new SessionNotInitializedException(
				'Cannot erase from session - WC session not initialized.'
			);
		}

		WC()->session->__unset( $key );
	}

	/**
	 * Destroy current WooCommerce session.
	 * Does NOT initialize session - silently returns if session doesn't exist.
	 *
	 * Deletes the stored session for the current customer unique ID.
	 *
	 * @return void
	 */
	public function destroySession(): void {
		if ( function_exists( 'WC' ) && WC()->session && function_exists( 'wc_empty_cart' ) ) {
			WC()->session->delete_session( WC()->session->get_customer_unique_id() );
		}
	}

	/**
	 * Get current session customer ID.
	 * Does NOT initialize session - returns empty string if session doesn't exist.
	 *
	 * @return string Customer ID from WooCommerce session, or empty string.
	 */
	public function getSessionCustomerId(): string {
		if ( ! function_exists( 'WC' ) || ! WC()->session ) {
			return '';
		}

		return WC()->session->get_customer_id();
	}

	/**
	 * Persist session data.
	 * Does NOT initialize session - silently returns if session doesn't exist.
	 *
	 * Forces saving session data to storage.
	 *
	 * @return void
	 */
	public function sessionClose() {
		if ( function_exists( 'WC' ) && WC()->session ) {
			WC()->session->save_data();
		}
	}
}
