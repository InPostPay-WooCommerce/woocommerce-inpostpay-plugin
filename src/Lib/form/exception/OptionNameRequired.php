<?php

namespace Ilabs\Inpost_Pay\Lib\form\exception;

class OptionNameRequired extends \Exception {


	public function __construct() {
		parent::__construct( 'Option name is required' );
	}
}
