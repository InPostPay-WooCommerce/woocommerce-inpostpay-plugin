<?php

namespace Ilabs\Inpost_Pay\Lib\item\cookie;

class AdditionalCookies {

	public array $cookies = array();

	public function add_cookies( $cookies ) {
		$this->cookies = array_merge( $this->cookies, $cookies );
	}
}
