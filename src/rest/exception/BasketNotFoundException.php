<?php

namespace Ilabs\Inpost_Pay\rest\exception;

use Ilabs\Inpost_Pay\rest\ApiException;

class BasketNotFoundException extends ApiException {

	public function __construct() {
		parent::__construct(
			'BASKET_NOT_FOUND',
			"Basket not found.",
			404
		);
	}
}
