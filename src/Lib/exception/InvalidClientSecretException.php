<?php

namespace Ilabs\Inpost_Pay\Lib\exception;

class InvalidClientSecretException extends AuthorizationException {

	public function __construct() {
		parent::__construct( 'Invalid client secret' );

	}
}
