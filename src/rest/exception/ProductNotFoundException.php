<?php
/**
 * Product not found exception.
 *
 * @package Ilabs\Inpost_Pay\rest\exception
 */

declare( strict_types=1 );

namespace Ilabs\Inpost_Pay\rest\exception;

use Ilabs\Inpost_Pay\rest\ApiException;

/**
 * Thrown when a requested product does not exist in WooCommerce.
 *
 * Maps to HTTP 404.
 */
class ProductNotFoundException extends ApiException {

	/**
	 * Constructor.
	 */
	public function __construct() {
		parent::__construct(
			'PRODUCT_NOT_FOUND',
			'Product not found.',
			404
		);
	}
}
