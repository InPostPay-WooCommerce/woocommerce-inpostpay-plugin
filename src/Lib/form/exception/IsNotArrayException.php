<?php

namespace Ilabs\Inpost_Pay\Lib\form\exception;

class IsNotArrayException extends \Exception {


	public function __construct( $key, $value ) {
		parent::__construct( sprintf( 'Is not array field: %s with value: %s', $key, $value ) );
	}
}
