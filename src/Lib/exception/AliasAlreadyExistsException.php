<?php

namespace Ilabs\Inpost_Pay\Lib\exception;

class AliasAlreadyExistsException extends \Exception {
	public function __construct( string $alias ) {
		parent::__construct( 'Alias ID "' . $alias . '" is already in use.' );
	}
}
