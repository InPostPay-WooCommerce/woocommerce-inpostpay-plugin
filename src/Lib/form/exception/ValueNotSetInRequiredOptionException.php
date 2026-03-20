<?php

namespace Ilabs\Inpost_Pay\Lib\form\exception;

class ValueNotSetInRequiredOptionException extends \Exception {

	/**
	 * @param string $name
	 */
	public function __construct( string $name ) {

		parent::__construct( sprintf( 'Value not set in required option "%s"', $name ) );
	}
}
