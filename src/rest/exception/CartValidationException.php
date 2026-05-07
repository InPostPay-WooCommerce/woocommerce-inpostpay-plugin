<?php
/**
 * Cart validation exception.
 *
 * @package Ilabs\Inpost_Pay\rest\exception
 */

declare( strict_types=1 );

namespace Ilabs\Inpost_Pay\rest\exception;

/**
 * Thrown when WooCommerce cart add_to_cart() fails due to validation,
 * stock, quantity limits, or hook rejection.
 *
 * Maps to HTTP 409 by default. Code can be overridden (e.g. 400, 404).
 *
 * @since 2.0.8
 */
class CartValidationException extends \RuntimeException {

	/**
	 * Constructor.
	 *
	 * @param string $message Human-readable reason (from WC notices or custom).
	 * @param int    $code    HTTP-like status code.
	 */
	public function __construct( string $message, int $code = 400 ) {
		parent::__construct( $message, $code );
	}
}
