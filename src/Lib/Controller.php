<?php
/**
 * Controller class for handling InPost Pay API requests.
 *
 * @package Ilabs\Inpost_Pay\Lib
 */

namespace Ilabs\Inpost_Pay\Lib;

use Ilabs\Inpost_Pay\Logger;

/**
 * Controller class that extends Remote functionality for InPost Pay operations.
 *
 * This class handles basket binding operations and signature key retrieval
 * for the InPost Pay payment gateway.
 */
class Controller extends Remote {

	/**
	 * Deletes bindings for a shopping basket.
	 *
	 * This method first unsets any existing binding, then calls the parent class's
	 * method to handle the deletion of bindings. After that, it drops any basket identification
	 * data that is no longer needed.
	 *
	 * @return mixed The response from the parent class's method.
	 */
	public function basket_binding_delete() {
		BindingProvider::unsetBinding();
		$response = parent::basket_binding_delete();
		BasketIdentification::drop();

		return $response;
	}

	/**
	 * Retrieves signature keys from the InPost Pay API.
	 *
	 * @param bool $force Whether to force a new request. Default false.
	 *
	 * @return mixed The API response containing signature keys.
	 */
	public function getSignatureKeys( $force = false ) {
		return $this->request( 'v1/izi/signing-keys/public' );
	}
}
