<?php

namespace Ilabs\Inpost_Pay\Lib\exception;

class UserNotFoundException extends \Exception {

	public function __construct() {
		parent::__construct('User not found');
	}
}
