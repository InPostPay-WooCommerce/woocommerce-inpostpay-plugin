<?php

namespace Ilabs\Inpost_Pay\Lib\exception;

use Exception;
use Ilabs\Inpost_Pay\Logger;

class CantCreateAttribution extends Exception {

	private const message = 'Cant create attribution. Explain: %s';

	/**
	 * @param string $message
	 */
	public function __construct( string $message ) {
		Logger::log( sprintf( self::message, $message ) );
		parent::__construct( sprintf( self::message, $message ) );
	}
}
