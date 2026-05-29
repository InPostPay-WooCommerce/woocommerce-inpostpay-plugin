<?php
/**
 * Additional cookies item.
 *
 * @package Inpost_Pay
 */

namespace Ilabs\Inpost_Pay\Lib\item\cookie;

/**
 * Stores additional cookies.
 */
class AdditionalCookies {

	/**
	 * Cookies list.
	 *
	 * @var array
	 */
	public array $cookies = array();

	/**
	 * Add cookies to the list.
	 *
	 * @param array $cookies Cookies to add.
	 */
	public function add_cookies( $cookies ) {
		$this->cookies = array_merge( $this->cookies, $cookies );
	}
}
