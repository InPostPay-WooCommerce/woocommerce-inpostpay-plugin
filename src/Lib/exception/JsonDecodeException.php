<?php

namespace Ilabs\Inpost_Pay\Lib\exception;

class JsonDecodeException extends \Exception {

	/**
	 * @param $error
	 */
	public function __construct( $error ) {
		parent::__construct( 'JSON decode error: ' . $error );
	}
}
