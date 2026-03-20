<?php
/**
 * Cookie helper utilities used by InPost Pay integration.
 *
 * @package Ilabs\Inpost_Pay
 * @since 1.0.0
 */

namespace Ilabs\Inpost_Pay\Lib\helpers;

use Ilabs\Inpost_Pay\Lib\config\attribution\AttributionConfig;
use Ilabs\Inpost_Pay\Lib\item\cookie\AdditionalCookies;

/**
 * Provides helper methods for reading, parsing and updating cookies.
 *
 * @since 1.0.0
 */
class CookieHelper {

	/**
	 * Build an associative array of cookies needed by the integration.
	 *
	 * @since 1.0.0
	 *
	 * @return array<string, mixed> Cookies map.
	 */
	public static function getCookies(): array {
		$cookie = array();

		$cookie_list = self::getCookieHeaderInformation();

		if ( self::get( 'BrowserId' ) ) {
			$cookie['BrowserId'] = self::get( 'BrowserId' );
		}

		if ( self::get( 'woocommerce_items_in_cart' ) ) {
			$cookie['woocommerce_items_in_cart'] = self::get( 'woocommerce_items_in_cart' );
		}

		if ( self::get( 'woocommerce_cart_hash' ) ) {
			$cookie['woocommerce_cart_hash'] = self::get( 'woocommerce_cart_hash' );
		}

		if ( ( new AttributionConfig() )->is_enabled() ) {

			if ( self::get( 'sbjs_session' ) ) {
				$cookie['sbjs_session'] = self::get( 'sbjs_session' );
			}

			if ( self::get( 'sbjs_udata' ) ) {
				$cookie['sbjs_udata'] = self::get( 'sbjs_udata' );
			}

			if ( self::get( 'sbjs_first' ) ) {
				$cookie['sbjs_first'] = self::get( 'sbjs_first' );
			}

			if ( self::get( 'sbjs_current' ) ) {
				$cookie['sbjs_current'] = self::get( 'sbjs_current' );
			}

			if ( self::get( 'sbjs_first_add' ) ) {
				$cookie['sbjs_first_add'] = self::get( 'sbjs_first_add' );
			}

			if ( self::get( 'sbjs_current_add' ) ) {
				$cookie['sbjs_current_add'] = self::get( 'sbjs_current_add' );
			}

			if ( self::get( 'sbjs_migrations' ) ) {
				$cookie['sbjs_migrations'] = self::get( 'sbjs_migrations' );
			}
		}

		$found = false;
		foreach ( $_COOKIE as $key => $value ) {
			if ( false !== strpos( $key, 'wp_woocommerce_session_' ) ) {
				$found          = true;
				$cookie[ $key ] = sanitize_text_field( $value );
				break;
			}
		}

		if ( ! $found ) {
			foreach ( self::getCookieHeaderInformation() as $key => $value ) {
				if ( false !== strpos( $key, 'wp_woocommerce_session_' ) ) {
					$cookie[ $key ] = sanitize_text_field( $value['value'] );
					break;
				}
			}
		}

		$custom_cookies = new AdditionalCookies();

		do_action( 'inpost_pay_store_custom_cookies', $custom_cookies );

		if ( $custom_cookies->cookies ) {
			foreach ( $custom_cookies->cookies as $custom_cookie ) {
				$cookie[ $custom_cookie ] = self::get( $custom_cookie );
			}
		}

		if ( get_current_user_id() ) {
			$cookie['customer_id'] = get_current_user_id();
		}

		return $cookie;
	}

	/**
	 * Parse "Set-Cookie" headers already queued for output.
	 *
	 * @since 1.0.0
	 *
	 * @return array<string, array<string, mixed>> Parsed cookie map keyed by cookie name.
	 */
	public static function getCookieHeaderInformation(): array {
		$headers     = headers_list();
		$cookie_list = array();
		foreach ( $headers as $header ) {
			if ( strpos( $header, 'Set-Cookie:' ) === 0 ) {
				$cookie                         = self::parse( $header );
				$cookie_list[ $cookie['name'] ] = $cookie;
			}
		}

		return $cookie_list;
	}

	/**
	 * Parse a single "Set-Cookie" header line.
	 *
	 * @param string $cookieHeader Full header line (including "Set-Cookie:").
	 *
	 * @return array<string, mixed>|null Parsed cookie data or null on failure.
	 *
	 * @since 1.0.0
	 *
	 */
	public static function parse( string $cookieHeader ): ?array {
		if ( empty( $cookieHeader ) ) {
			return null;
		}

		if ( \preg_match( '/^Set-Cookie: (.*?)=(.*?)(?:; (.*?))?$/i', $cookieHeader, $matches ) ) {
			$cookie['name']      = $matches[1];
			$cookie['path']      = null;
			$cookie['http_only'] = 'false';
			$cookie['value']     = \urldecode( $matches[2] );
			$cookie['same_site'] = null;

			if ( count( $matches ) >= 4 ) {
				$attributes = \explode( '; ', $matches[3] );

				foreach ( $attributes as $attribute ) {
					if ( strcasecmp( $attribute, 'HttpOnly' ) === 0 ) {
						$cookie['http_only'] = 'true';
					} elseif ( strcasecmp( $attribute, 'Secure' ) === 0 ) {
						$cookie['secure'] = 'true';
					} elseif ( stripos( $attribute, 'Expires=' ) === 0 ) {
						$cookie['expires'] = (int) strtotime( substr( $attribute, 8 ) );
					} elseif ( stripos( $attribute, 'Domain=' ) === 0 ) {
						$cookie['domain'] = substr( $attribute, 7 );
					} elseif ( stripos( $attribute, 'Path=' ) === 0 ) {
						$cookie['path'] = substr( $attribute, 5 );
					} elseif ( stripos( $attribute, 'SameSite=' ) === 0 ) {
						$cookie['same_site'] = substr( $attribute, 9 );
					}
				}
			}

			return $cookie;
		}

		return null;
	}

	/**
	 * Get a cookie value from the current request.
	 *
	 * Value is sanitized using `sanitize_text_field()`.
	 *
	 * @since 1.0.0
	 *
	 * @param string $key Cookie name.
	 * @return string|null Cookie value or null when missing.
	 */
	public static function get( string $key ): ?string {
		if ( isset( $_COOKIE[ $key ] ) ) {
			return sanitize_text_field( wp_unslash( $_COOKIE[ $key ] ) );
		}

		return null;
	}

	/**
	 * Delete a cookie and remove it from the current request cookie bag.
	 *
	 * @since 1.0.0
	 *
	 * @param string $name Cookie name.
	 * @return void
	 */
	public static function delete( string $name ): void {
		if ( ! headers_sent() ) {
			setcookie(
				$name,
				'',
				array(
					'expires'  => time() - 3600,
					'secure'   => true,
					'httponly' => false,
					'domain'   => wp_parse_url( home_url(), PHP_URL_HOST ),
					'path'     => '/',
				)
			);
		}

		if ( isset( $_COOKIE[ $name ] ) ) {
			unset( $_COOKIE[ $name ] );
		}
	}

	/**
	 * Read a cookie definition from outgoing headers (if already set during the request).
	 *
	 * @since 1.0.0
	 *
	 * @param string $name Cookie name.
	 * @return array<string, mixed>|null Cookie data array or null when not found.
	 */
	public static function get_from_header( string $name ): ?array {
		$cookies = self::getCookieHeaderInformation();

		return $cookies[ $name ] ?? null;
	}

	/**
	 * Update expiration time for the "basket_binding_api_key" cookie.
	 *
	 * @since 1.0.0
	 *
	 * @param int $expiration Expiration timestamp.
	 * @return void
	 */
	public static function update_basket_binding_api_key_expiration( int $expiration ): void {
		if ( ! headers_sent() && self::get( 'basket_binding_api_key' ) ) {
			setcookie(
				'basket_binding_api_key',
				self::get( 'basket_binding_api_key' ),
				array(
					'expires'  => $expiration,
					'secure'   => true,
					'httponly' => false,
					'domain'   => wp_parse_url( home_url(), PHP_URL_HOST ),
					'path'     => '/',
				)
			);
		}
	}

	/**
	 * Set the "basket_binding_api_key" cookie for the first time.
	 *
	 * @since 1.0.0
	 *
	 * @param string $api_key The API key to set.
	 * @return void
	 */
	public static function set_basket_binding_api_key( string $api_key ): void {
		if ( ! headers_sent() ) {
			setcookie(
				'basket_binding_api_key',
				$api_key,
				array(
					'expires'  => Woo_Commerce_Session_Helper::get_session_expiration_time(),
					'secure'   => true,
					'httponly' => false,
					'domain'   => wp_parse_url( home_url(), PHP_URL_HOST ),
					'path'     => '/',
				)
			);
			$_COOKIE['basket_binding_api_key'] = $api_key;
		}
	}
}
