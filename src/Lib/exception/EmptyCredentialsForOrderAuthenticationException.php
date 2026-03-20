<?php

namespace Ilabs\Inpost_Pay\Lib\exception;

class EmptyCredentialsForOrderAuthenticationException extends \Exception {

	public function __construct() {
		parent::__construct( 'Empty credentials for order authentication');
	}
}
