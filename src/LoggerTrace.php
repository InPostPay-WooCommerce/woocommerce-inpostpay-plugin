<?php
/**
 * LoggerTrace class.
 *
 * Provides helper methods for generating backtrace, request metadata,
 * and unique trace identifiers for debugging and logging.
 *
 * @package Ilabs\Inpost_Pay
 */

declare( strict_types=1 );

namespace Ilabs\Inpost_Pay;

/**
 * Class LoggerTrace
 *
 * Utility for compact debug backtraces and request meta information.
 */
class LoggerTrace {

	/**
	 * Generate a compact formatted backtrace string.
	 *
	 * @param int $limit Maximum number of frames to include.
	 * @param int $skip  Number of frames to skip from the start.
	 *
	 * @return string
	 */
	public static function compact_backtrace( int $limit = 20, int $skip = 0 ): string {
		$backtrace = debug_backtrace( DEBUG_BACKTRACE_IGNORE_ARGS );

		if ( $skip > 0 ) {
			$backtrace = array_slice( $backtrace, $skip );
		}

		if ( $limit > 0 ) {
			$backtrace = array_slice( $backtrace, 0, $limit );
		}

		$lines = array();

		foreach ( $backtrace as $index => $frame ) {
			$file  = isset( $frame['file'] ) ? basename( (string) $frame['file'] ) : '(internal)';
			$line  = $frame['line'] ?? '?';
			$func  = $frame['function'] ?? '?';
			$class = $frame['class'] ?? '';
			$type  = $frame['type'] ?? '';

			$lines[] = sprintf( '#%02d %s:%s %s%s%s', $index, $file, $line, $class, $type, $func );
		}

		return implode( "\n", $lines );
	}

	/**
	 * Get request metadata string for logs.
	 *
	 * Includes method, URI, and user agent.
	 *
	 * @return string
	 */
	public static function req_meta(): string {
		$uri    = $_SERVER['REQUEST_URI'] ?? '';
		$method = $_SERVER['REQUEST_METHOD'] ?? '';
		$ua     = $_SERVER['HTTP_USER_AGENT'] ?? '';

		return sprintf( 'METHOD=%s URI=%s UA=%s', $method, $uri, $ua );
	}

	/**
	 * Generate a unique trace identifier for current request.
	 *
	 * @return string
	 *
	 * @throws \Exception When random_bytes() fails.
	 */
	public static function trace_id(): string {
		static $id = null;

		if ( null === $id ) {
			$id = 'trace_' . substr(
				md5( __CLASS__ . microtime( true ) . random_bytes( 8 ) ),
				0,
				12
			);
		}

		return $id;
	}
}
