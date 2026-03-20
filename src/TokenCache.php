<?php
/**
 * TokenCache class.
 *
 * @package Ilabs\Inpost_Pay
 * @since   2.0.7
 */

declare( strict_types=1 );

namespace Ilabs\Inpost_Pay;

use Ilabs\Inpost_Pay\EntityLayer\Cache\PersistentCache;
use Ilabs\Inpost_Pay\Lib\Authorization;
use Ilabs\Inpost_Pay\Lib\exception\AuthorizationException;
use Ilabs\Inpost_Pay\Lib\exception\InvalidClientCredentialsException;
use Ilabs\Inpost_Pay\Lib\exception\InvalidClientSecretException;

/**
 * Token cache using persistent storage.
 */
class TokenCache {

	/**
	 * Cache instance.
	 *
	 * @var PersistentCache
	 */
	private PersistentCache $cache;

	/**
	 * Cache key for token data.
	 *
	 * @var string
	 */
	private string $cache_key = 'izi_keyclock_token_data';

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->cache = new PersistentCache();
	}

	/**
	 * Get cached token.
	 *
	 * @param bool $renew Force token renewal.
	 *
	 * @return string|null Token or null if expired/missing.
	 */
	public function getCachedToken( bool $renew = false ): ?string {
		static $recursion_depth = 0;

		if ( $recursion_depth > 1 ) {
			Logger::log( 'TOKEN: Recursion limit reached, preventing infinite loop' );
			$recursion_depth = 0;
			return null;
		}

		$recursion_depth++;

		$data = $this->cache->get( $this->cache_key );

		if ( ! $data || ! isset( $data['token'], $data['expiration'] ) ) {
			Logger::log( 'TOKEN: No cached token data' );
			if ( ! $renew ) {
				Logger::log( 'TOKEN: try to renew token' );
				$this->renewToken();
				$token = $this->getCachedToken( true );
				$recursion_depth--;
				return $token;
			}
			$recursion_depth--;
			return null;
		}

		$created_at = isset( $data['created_at'] ) ? (int) $data['created_at'] : 0;
		$expiration = (int) $data['expiration'];
		$leeway     = 30;
		$expires_at = $created_at + $expiration;

		if ( $created_at <= 0 || $expiration <= 0 || $expires_at <= ( time() + $leeway ) ) {
			Logger::log( 'TOKEN: Cached token expired' );
			if ( ! $renew ) {
				Logger::log( 'TOKEN: try to renew token' );
				$this->renewToken();
				$token = $this->getCachedToken( true );
				$recursion_depth--;
				return $token;
			}
			$recursion_depth--;
			return null;
		}

		$recursion_depth--;
		return $data['token'];
	}

	/**
	 * Set cached token.
	 *
	 * @param string $token      Token value.
	 * @param int    $expiration Expiration in seconds.
	 *
	 * @return void
	 */
	public function setCachedToken( string $token, int $expiration ): void {
		$data = array(
			'token'      => $token,
			'expiration' => $expiration,
			'created_at' => time(),
		);

		$this->cache->set( $this->cache_key, $data, $expiration );
	}

	/**
	 * Renew the token.
	 *
	 * @return void
	 * @throws AuthorizationException
	 * @throws InvalidClientCredentialsException
	 * @throws InvalidClientSecretException
	 */
	private function renewToken(): void {
		$authorization = new Authorization();
		$authorization->getToken();
	}
}
