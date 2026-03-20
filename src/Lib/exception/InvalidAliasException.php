<?php

namespace Ilabs\Inpost_Pay\Lib\exception;

class InvalidAliasException extends \Exception {
	public function __construct( string $alias ) {
		parent::__construct( 'Alias ID is invalid: "' . $alias . '". Must be non-empty and up to 64 characters.' );
	}
}
