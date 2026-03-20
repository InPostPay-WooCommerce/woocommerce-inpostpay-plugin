<?php

namespace Ilabs\Inpost_Pay\Lib\exception;

class AuthorizationException extends \Exception {

	/**
	 * @param $error
	 */
	public function __construct( $error ) {
		parent::__construct( 'Invalid authorization exception: ' . $error );
	}
}
