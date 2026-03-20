<?php

namespace Ilabs\Inpost_Pay\rest\exception;

use Ilabs\Inpost_Pay\rest\ApiException;

class ProductNotFoundException extends ApiException {

	public function __construct() {
		parent::__construct(
			'PRODUCT_NOT_FOUND',
			"Product not found.",
			404
		);
	}
}
