<?php

namespace Ilabs\Inpost_Pay\Lib\exception;

class CookieEmptyValueException extends \Exception {


	public function __construct() {
		parent::__construct( 'Empty cookie value' );
	}
}
