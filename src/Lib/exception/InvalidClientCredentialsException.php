<?php

namespace Ilabs\Inpost_Pay\Lib\exception;

class InvalidClientCredentialsException extends AuthorizationException {

	public function __construct() {
		parent::__construct( 'Invalid client credentials' );
	}
}
