<?php

namespace Ilabs\Inpost_Pay\rest\exception;

use Ilabs\Inpost_Pay\rest\ApiException;

class ProductOutOfStockException extends ApiException {

	public function __construct() {
		parent::__construct(
			'OUT_OF_STOCK',
			"Product out of stock.",
			409
		);
	}
}
