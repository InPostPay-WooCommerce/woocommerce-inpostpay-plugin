<?php

namespace Ilabs\Inpost_Pay\rest\exception;

use Ilabs\Inpost_Pay\rest\ApiException;

class UnauthorizedException extends ApiException {

	public function __construct() {
		parent::__construct(
			'UNAUTHORIZED',
			"Given user is not authorized to access the resource.",
			401
		);
	}
}
