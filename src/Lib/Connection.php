<?php
/**
 * Connection class for handling API requests with authorization.
 *
 * @package Ilabs\Inpost_Pay\Package
 */

declare( strict_types=1 );

namespace Ilabs\Inpost_Pay\Lib;

use Ilabs\Inpost_Pay\Lib\exception\AuthorizationException;
use Ilabs\Inpost_Pay\Logger;

/**
 * Connection class for handling API requests with authorization.
 *
 * @package Ilabs\Inpost_Pay\Package
 */
class Connection extends Fetcher {

	private Authorization $authorization;

	private ?string $token;

	/**
	 * Connection constructor.
	 *
	 * Initializes the authorization service and attempts to get an access token.
	 */
	public function __construct() {
		$this->authorization = new Authorization();
		try {
			$this->token = $this->authorization->getToken();
		} catch ( AuthorizationException $e ) {
			$this->token = null;
		}

		parent::__construct();
	}

	/**
	 * Makes an API request to the InPostPay server.
	 *
	 * @param string     $command The type of API request (e.g., "users", "posts").
	 * @param string     $type The type of HTTP request. Defaults to "GET".
	 * @param null|mixed $data Additional data to send with the request. Defaults to an empty array.
	 * @param bool       $with_code If true, includes error codes in the response. Defaults to false.
	 * @param bool       $raw If true, returns the raw response from the server instead of processing it. Defaults to false.
	 *
	 * @return \stdClass|array The API response data as stdClass if successful and the token is set; an empty array otherwise.
	 */
	public function request(
		string $command,
		string $type = 'GET',
		$data = null,
		bool $with_code = false,
		bool $raw = false
	) {

		Logger::request(
			$command,
			$type,
			$with_code,
			$raw,
			$data,
		);

		if ( null !== $this->token ) {

			$response = $this->fetch(
				InPostIzi::getApiUrl() . "/$command",
				$type,
				$data,
				$with_code,
				$raw
			);

			Logger::response(
				empty( $response ) ? '(empty)' : print_r( $response, true ),
			);

			return $response;
		}

		return array();
	}


	/**
	 * Returns the HTTP headers for API requests.
	 *
	 * @return array An array of strings representing the HTTP headers.
	 */
	public function headers(): array {
		return array(
			"Authorization: Bearer {$this->token}",
		);
	}
}
