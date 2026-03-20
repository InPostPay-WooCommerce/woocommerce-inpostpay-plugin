<?php

namespace Ilabs\Inpost_Pay\Lib\Authentication;

use Ilabs\Inpost_Pay\Lib\exception\EmptyCredentialsForOrderAuthenticationException;
use Ilabs\Inpost_Pay\Lib\exception\UserNotFoundException;

interface AuthenticationInterface {

	/**
	 * @throws EmptyCredentialsForOrderAuthenticationException
	 * @throws UserNotFoundException
	 */
	public function authenticate(Credentials $credentials): ?\WP_User;
}
