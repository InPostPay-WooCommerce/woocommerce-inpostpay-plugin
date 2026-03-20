<?php

namespace Ilabs\Inpost_Pay\Lib;

use Ilabs\Inpost_Pay\Lib\exception\AuthorizationException;
use Ilabs\Inpost_Pay\Lib\exception\InvalidClientCredentialsException;
use Ilabs\Inpost_Pay\Lib\exception\InvalidClientSecretException;
use Ilabs\Inpost_Pay\Logger;

class Authorization extends Fetcher {
	private ?string $token = null;
	private ?int $expiration;

	/**
	 * @throws InvalidClientCredentialsException
	 * @throws AuthorizationException
	 * @throws InvalidClientSecretException
	 */
	public function getToken( bool $force = false ): ?string {
		if ( $this->token === null ) {
			$this->token = InPostIzi::getCachedToken();
			if ( ! $this->token || $force === true ) {
				$this->login();
				InPostIzi::setCachedToken( $this->token, $this->expiration );
			}
		}

		return $this->token;
	}

	/**
	 * @throws InvalidClientCredentialsException
	 * @throws AuthorizationException
	 * @throws InvalidClientSecretException
	 */
	public function login() {
		$url = InPostIzi::getAuthUrl() . '/auth/realms/external/protocol/openid-connect/token';

		$query = array(
			'client_id'     => InPostIzi::get_client_id(),
			'client_secret' => InPostIzi::get_client_secret(),
			'grant_type'    => 'client_credentials',
		);

		$response = $this->query( $url, $query );

		if ( ! isset( $response[0]->error ) ) {
			$this->token      = isset( $response, $response[0], $response[0]->access_token ) ? $response[0]->access_token : '';
			$this->expiration = isset( $response, $response[0], $response[0]->expires_in ) ? (int) $response[0]->expires_in : 0;
		} else {
			update_option( 'izi_is_authorized', false );
			switch ( $response[0]->error ) {
				case 'invalid_client':
					Logger::log( 'invalid_client' );
					throw new InvalidClientCredentialsException();
					break;
				case 'invalid_client_secret':
					Logger::log( 'invalid_client_secret' );
					throw new InvalidClientSecretException();
				default:
					Logger::log( 'default' );
					throw new AuthorizationException( esc_attr( $response[0]->error ) );
			}
		}
	}

	public function headers(): array {
		return array();
	}
}
