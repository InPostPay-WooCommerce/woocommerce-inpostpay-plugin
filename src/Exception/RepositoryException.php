<?php
/**
 * Base repository exception.
 *
 * @package Ilabs\WpEntityLayer\Exception
 */

declare( strict_types=1 );

namespace Ilabs\Inpost_Pay\Exception;

use RuntimeException;
use Throwable;

/**
 * Exception thrown when repository operations fail.
 */
class RepositoryException extends RuntimeException {

	/**
	 * Constructor.
	 *
	 * @param string         $message  Exception message.
	 * @param int            $code     Exception code.
	 * @param Throwable|null $previous Previous exception instance.
	 */
	public function __construct(
		string $message = 'Repository operation failed.',
		int $code = 0,
		?Throwable $previous = null
	) {
		parent::__construct( $message, $code, $previous );
	}
}
