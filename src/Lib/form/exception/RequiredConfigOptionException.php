<?php

namespace Ilabs\Inpost_Pay\Lib\form\exception;

class RequiredConfigOptionException extends \Exception {

	/**
	 * @param int|string $key
	 */
	public function __construct( $key ) {
		parent::__construct( sprintf( 'Required config option "%s" not found', $key ) );
	}
}
