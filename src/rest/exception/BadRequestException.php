<?php
/**
 * Bad request exception.
 *
 * @package Ilabs\Inpost_Pay\rest\exception
 */

declare( strict_types=1 );

namespace Ilabs\Inpost_Pay\rest\exception;

use Ilabs\Inpost_Pay\rest\ApiException;

/**
 * Thrown when the request payload is malformed or missing required fields.
 *
 * Maps to HTTP 400.
 */
class BadRequestException extends ApiException {

	/**
	 * Constructor.
	 */
	public function __construct() {
		parent::__construct(
			'BAD_REQUEST',
			'Invalid request',
			400
		);
	}
}
