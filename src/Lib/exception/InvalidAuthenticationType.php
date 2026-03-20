<?php

namespace Ilabs\Inpost_Pay\Lib\exception;

class InvalidAuthenticationType extends \Exception {


	public function __construct( $type ) {

		parent::__construct('Invalid authentication type: '.$type);
	}
}
