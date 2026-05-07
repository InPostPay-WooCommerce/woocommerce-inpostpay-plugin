<?php
/**
 * Unauthorized exception.
 *
 * @package Ilabs\Inpost_Pay\rest\exception
 */

declare( strict_types=1 );

namespace Ilabs\Inpost_Pay\rest\exception;

use Ilabs\Inpost_Pay\rest\ApiException;

/**
 * Thrown when the request lacks valid credentials or permissions.
 *
 * Maps to HTTP 401.
 */
class UnauthorizedException extends ApiException {

	/**
	 * Constructor.
	 */
	public function __construct() {
		parent::__construct(
			'UNAUTHORIZED',
			'Given user is not authorized to access the resource.',
			401
		);
	}
}
