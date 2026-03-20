<?php
/**
 * Exception class for missing Service Container.
 *
 * @package Ilabs\Inpost_Pay\Exception
 */

declare( strict_types=1 );

namespace Ilabs\Inpost_Pay\Exception;

use RuntimeException;
use Throwable;

/**
 * Exception thrown when the Service Container is missing or not initialized.
 */
class MissingServiceContainerException extends RuntimeException {

	/**
	 * Constructor.
	 *
	 * @param string         $message  Exception message.
	 * @param int            $code     Exception code.
	 * @param Throwable|null $previous Previous exception instance.
	 */
	public function __construct(
		string $message = 'Service Container does not exist.',
		int $code = 500,
		?Throwable $previous = null
	) {
		parent::__construct( $message, $code, $previous );
	}
}
