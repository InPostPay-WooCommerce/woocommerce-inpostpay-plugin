<?php

namespace Ilabs\Inpost_Pay\Lib\Authentication;

use Ilabs\Inpost_Pay\Lib\exception\EmptyCredentialsForOrderAuthenticationException;
use Ilabs\Inpost_Pay\Lib\exception\InvalidAuthenticationType;

class AuthenticationFactory {
	/**
	 * @throws InvalidAuthenticationType
	 */
	public static function create( $type ): AuthenticationInterface {
		switch ( $type ) {
			case 'order':
				return new OrderAuthentication();
			default:
				throw new InvalidAuthenticationType( $type );
		}
	}
}
