<?php

namespace Ilabs\Inpost_Pay\Lib\form\exception;

class NotAllowedConfigOptionException extends \Exception {

	/**
	 * @param int|string $key
	 */
	public function __construct( $key ) {
		parent::__construct(
			sprintf( 'Config option "%s" is not allowed', $key )
		);
	}
}
