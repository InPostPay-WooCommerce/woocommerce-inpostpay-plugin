<?php
/**
 * Product not added exception.
 *
 * @package Ilabs\Inpost_Pay\rest\exception
 */

declare( strict_types=1 );

namespace Ilabs\Inpost_Pay\rest\exception;

use Ilabs\Inpost_Pay\rest\ApiException;

/**
 * Thrown when WooCommerce fails to add a product to the cart.
 *
 * Maps to HTTP 409.
 */
class ProductNotAddedException extends ApiException {

	/**
	 * Constructor.
	 *
	 * @param string $message Human-readable reason from WC notices.
	 */
	public function __construct( $message ) {
		parent::__construct(
			'PRODUCT_NOT_ADDED',
			wp_strip_all_tags( $message ),
			409
		);
	}
}
