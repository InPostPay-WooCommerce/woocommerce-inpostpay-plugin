<?php
/**
 * Basket not found exception.
 *
 * @package Ilabs\Inpost_Pay\rest\exception
 */

declare( strict_types=1 );

namespace Ilabs\Inpost_Pay\rest\exception;

use Ilabs\Inpost_Pay\rest\ApiException;

/**
 * Thrown when a basket entity cannot be located by API key.
 *
 * Maps to HTTP 404.
 */
class BasketNotFoundException extends ApiException {

	/**
	 * Constructor.
	 */
	public function __construct() {
		parent::__construct(
			'BASKET_NOT_FOUND',
			'Basket not found.',
			404
		);
	}
}
