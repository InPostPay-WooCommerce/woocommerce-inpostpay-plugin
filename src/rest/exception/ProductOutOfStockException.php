<?php
/**
 * Product out of stock exception.
 *
 * @package Ilabs\Inpost_Pay\rest\exception
 */

declare( strict_types=1 );

namespace Ilabs\Inpost_Pay\rest\exception;

use Ilabs\Inpost_Pay\rest\ApiException;

/**
 * Thrown when the requested product has insufficient stock.
 *
 * Maps to HTTP 409.
 */
class ProductOutOfStockException extends ApiException {

	/**
	 * Constructor.
	 */
	public function __construct() {
		parent::__construct(
			'OUT_OF_STOCK',
			'Product out of stock.',
			409
		);
	}
}
