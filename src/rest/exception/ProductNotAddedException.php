<?php

namespace Ilabs\Inpost_Pay\rest\exception;

use Ilabs\Inpost_Pay\rest\ApiException;

class ProductNotAddedException extends ApiException {

	public function __construct( $message ) {
		parent::__construct(
			'PRODUCT_NOT_ADDED',
			strip_tags( $message ),
			409
		);
	}
}
