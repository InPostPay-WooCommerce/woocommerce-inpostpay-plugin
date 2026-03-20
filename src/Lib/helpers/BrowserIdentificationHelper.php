<?php
/**
 * Browser identification helper utilities.
 *
 * @package Ilabs\Inpost_Pay
 * @since 2.0.7
 */

namespace Ilabs\Inpost_Pay\Lib\helpers;

/**
 * Provides helper methods for generating browser-based identifiers.
 *
 * @since 2.0.7
 */
class BrowserIdentificationHelper {

	/**
	 * Generate a unique browser identifier based on browser fingerprint data.
	 *
	 * Creates a 16-character hash from user agent, accept language, and IP address.
	 *
	 * @since 2.0.7
	 *
	 * @return string Browser identifier (16 characters).
	 */
	public static function generate(): string {
		$userAgent  = isset( $_SERVER['HTTP_USER_AGENT'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) ) : '';
		$acceptLang = isset( $_SERVER['HTTP_ACCEPT_LANGUAGE'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_ACCEPT_LANGUAGE'] ) ) : '';
		$ipAddress  = self::getClientIp();

		$browserData = $userAgent . '|' . $acceptLang . '|' . $ipAddress;

		return substr( md5( $browserData ), 0, 16 );
	}

	/**
	 * Get the client IP address with proxy support.
	 *
	 * Checks various HTTP headers to determine the real client IP,
	 * accounting for proxies and load balancers.
	 *
	 * @since 2.0.7
	 *
	 * @return string Client IP address or empty string if not found.
	 */
	public static function getClientIp(): string {
		$ipKeys = array(
			'HTTP_CLIENT_IP',
			'HTTP_X_FORWARDED_FOR',
			'HTTP_X_FORWARDED',
			'HTTP_X_CLUSTER_CLIENT_IP',
			'HTTP_FORWARDED_FOR',
			'HTTP_FORWARDED',
			'REMOTE_ADDR',
		);

		foreach ( $ipKeys as $key ) {
			if ( isset( $_SERVER[ $key ] ) ) {
				$ip = sanitize_text_field( wp_unslash( $_SERVER[ $key ] ) );
				if ( filter_var( $ip, FILTER_VALIDATE_IP ) ) {
					return $ip;
				}
			}
		}

		return '';
	}
}
