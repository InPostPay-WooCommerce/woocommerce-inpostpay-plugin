<?php

namespace Ilabs\Inpost_Pay\rest;

use JsonException;
use WP_REST_Response;

abstract class ApiException implements ApiExceptionInterface {

	private WP_REST_Response $response;

	public function __construct( $error_code, $error_message, $code = 500 ) {
		$this->response = new WP_REST_Response(
			array(
				'error_code'    => $error_code,
				'error_message' => $error_message,
			),
			$code
		);
	}

	public function response() {
		return rest_ensure_response( $this->response );
	}
}
