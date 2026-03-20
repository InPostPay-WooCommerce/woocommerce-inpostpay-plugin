<?php

namespace Ilabs\Inpost_Pay\Lib\form\exception;

class NotFoundConfigOptionException extends \Exception {

	/**
	 * @param string $name
	 */
	public function __construct( string $name ) {
		parent::__construct( sprintf( 'Config option "%s" not found', $name ) );

	}
}
