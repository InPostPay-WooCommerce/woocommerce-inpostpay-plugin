<?php
/**
 * WooCommerce session helper.
 *
 * Provides utility methods for retrieving session expiration information.
 *
 * @package Ilabs\Inpost_Pay\Lib\helpers
 * @since 2.0.6
 */

namespace Ilabs\Inpost_Pay\Lib\helpers;

/**
 * Class Woo_Commerce_Session_Helper
 */
class Woo_Commerce_Session_Helper {

	/**
	 * Get the WooCommerce session expiration date.
	 *
	 * Returns the session expiration date formatted for API use.
	 *
	 * @return string The expiration date in the format specified by DateHelper::DATE_API_FORMAT.
	 */
	public static function get_session_expiation_date(): string {
		return gmdate( DateHelper::DATE_API_FORMAT, self::get_session_expiration_time() );
	}

	/**
	 * Get the WooCommerce session expiration timestamp.
	 *
	 * Returns the UNIX timestamp (seconds) for session expiration.
	 *
	 * When the WooCommerce session is available and has a valid cookie, the value is
	 * taken from the session cookie (index 1). Otherwise, it falls back to the
	 * default WooCommerce expiration based on user login state and the
	 * `wc_session_expiration` filter.
	 *
	 * @return int UNIX timestamp. Returns a best-effort value.
	 */
	public static function get_session_expiration_time(): int {
		if ( ! function_exists( 'WC' ) || ! isset( WC()->session ) || ! WC()->session ) {
			return time() + self::get_default_expiration_seconds();
		}

		$cookie = WC()->session->get_session_cookie();
		if ( is_array( $cookie ) && isset( $cookie[1] ) ) {
			return (int) $cookie[1];
		}
		return time() + self::get_default_expiration_seconds();
	}

	/**
	 * Get default WooCommerce session expiration seconds.
	 *
	 * Mirrors WooCommerce defaults, but decides guest vs user based on the
	 * WooCommerce session cookie (guest sessions are prefixed with "t_").
	 *
	 * @return int Expiration seconds.
	 */
	private static function get_default_expiration_seconds(): int {
		$default_expiration_seconds = self::is_guest_session() ? 2 * DAY_IN_SECONDS : WEEK_IN_SECONDS;
		$expiration_seconds         = (int) apply_filters( 'wc_session_expiration', $default_expiration_seconds );
		if ( $expiration_seconds <= 0 ) {
			$expiration_seconds = $default_expiration_seconds;
		}

		return $expiration_seconds;
	}

	/**
	 * Determine if current WooCommerce session should be treated as a guest session.
	 *
	 * WooCommerce marks guest sessions with a customer id prefixed by "t_".
	 * If the cookie is missing, assume guest.
	 *
	 * @return bool True when guest session.
	 */
	private static function is_guest_session(): bool {
		if ( ! defined( 'COOKIEHASH' ) ) {
			return true;
		}

		$cookie_name  = 'wp_woocommerce_session_' . COOKIEHASH;
		$cookie_value = isset( $_COOKIE[ $cookie_name ] ) ? sanitize_text_field( wp_unslash( $_COOKIE[ $cookie_name ] ) ) : '';
		if ( '' === $cookie_value ) {
			return true;
		}

		$parts = strpos( $cookie_value, '||' ) !== false ? explode( '||', $cookie_value ) : explode( '|', $cookie_value );
		if ( count( $parts ) !== 4 ) {
			return true;
		}

		$customer_id = (string) $parts[0];
		if ( '' === $customer_id ) {
			return true;
		}

		return strpos( $customer_id, 't_' ) === 0;
	}

	/**
	 * Get the WooCommerce session additional time until expiration.
	 *
	 * Returns the number of seconds remaining until the session expires.
	 *
	 * @return int Seconds remaining until expiration.
	 */
	public static function get_session_expiration_remaining_time(): int {
		return self::get_session_expiration_time() - time();
	}

	/**
	 * Check if WooCommerce session cookie exists.
	 *
	 * @return bool True if session cookie exists, false otherwise.
	 */
	public static function has_session_cookie(): bool {
		if ( ! defined( 'COOKIEHASH' ) ) {
			return false;
		}

		$cookie_name = 'wp_woocommerce_session_' . COOKIEHASH;

		return isset( $_COOKIE[ $cookie_name ] ) && ! empty( $_COOKIE[ $cookie_name ] );
	}
}
