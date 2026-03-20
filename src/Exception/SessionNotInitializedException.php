<?php
/**
 * Session not initialized exception.
 *
 * @package Ilabs\Inpost_Pay\Exception
 */

declare( strict_types=1 );

namespace Ilabs\Inpost_Pay\Exception;

use RuntimeException;
use Throwable;

/**
 * Exception thrown when trying to write to session that doesn't exist.
 */
class SessionNotInitializedException extends RuntimeException {

	/**
	 * Constructor.
	 *
	 * @param string         $message  Exception message.
	 * @param int            $code     Exception code.
	 * @param Throwable|null $previous Previous exception instance.
	 */
	public function __construct(
		string $message = 'Cannot write to WooCommerce session - session not initialized. Call initSession() first in REST endpoints (Add.php, Binding.php).',
		int $code = 0,
		?Throwable $previous = null
	) {
		parent::__construct( $message, $code, $previous );
	}
}
