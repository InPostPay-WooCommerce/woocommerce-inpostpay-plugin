<?php

namespace Ilabs\Inpost_Pay\rest\exception;

use Ilabs\Inpost_Pay\rest\ApiException;

class BadRequestException extends ApiException {

	public function __construct() {
		parent::__construct(
			'BAD_REQUEST',
			"Invalid request",
			400
		);
	}
}
