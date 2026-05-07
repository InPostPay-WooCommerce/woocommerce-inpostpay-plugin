<?php
/**
 * Logger.
 *
 * @package Ilabs\Inpost_Pay
 * @since   1.0.0
 */

namespace Ilabs\Inpost_Pay;

use Exception;
use Ilabs\Inpost_Pay\Lib\InPostIzi;
use Ilabs\Inpost_Pay\Lib\helpers\BrowserIdentificationHelper;

/**
 * Plugin logger wrapper.
 *
 * Provides helper methods for writing debug information to WooCommerce logger.
 * Logging is disabled when the `izi_debug` option is not enabled.
 *
 * @package Ilabs\Inpost_Pay
 * @since   1.0.0
 */
class Logger {
	public const PREFIX_HOT_PRODUCTS = '[HOT_PRODUCTS_DEBUG]';

	/**
	 * Cached client/session identifier used for correlating log entries.
	 *
	 * @var string|null
	 */
	private static ?string $clientSessionId = null;

	/**
	 * Cached flag for null logger mode.
	 *
	 * @var bool|null
	 */
	private static ?bool $nullLogger = null;

	/**
	 * Constructor.
	 *
	 * @return void
	 */
	public function __construct() {
	}

	/**
	 * Log error entry.
	 *
	 * @param mixed $data Data to log.
	 *
	 * @return void
	 */
	public static function error( $data ): void {
		if ( self::isNullLogger() ) {
			return;
		}

		self::write(
			sprintf(
				'[ERROR: %s]',
				( is_array( $data ) || is_object( $data ) ) ? print_r( $data, true ) : $data
			)
		);
	}

	/**
	 * Log general entry.
	 *
	 * @param mixed $data Data to log.
	 *
	 * @return void
	 */
	public static function log( $data ): void {
		if ( self::isNullLogger() ) {
			return;
		}

		self::write(
			sprintf(
				'[general: %s]',
				( is_array( $data ) || is_object( $data ) ) ? print_r( $data, true ) : $data
			)
		);
	}


	/**
	 * Log debug entry.
	 *
	 * @param mixed $data       Data to log.
	 * @param bool  $debugTrace Whether to append a backtrace.
	 *
	 * @return void
	 */
	public static function debug( $data, $debugTrace = false ): void {
		if ( self::isNullLogger() ) {
			return;
		}

		if ( $debugTrace ) {
			ob_start();
			debug_print_backtrace( DEBUG_BACKTRACE_IGNORE_ARGS, 5 );
			$trace = ob_get_contents();
			ob_end_clean();
			self::write(
				sprintf(
					'[debug: %s][trace: %s]',
					$data,
					$trace
				)
			);
		} else {
			self::write( $data );
		}
	}

	/**
	 * Log verbose (spam) entry.
	 *
	 * @param mixed $data Data to log.
	 *
	 * @return void
	 */
	public static function spam( $data ): void {
		if ( self::isNullLogger() ) {
			return;
		}

		self::write(
			sprintf(
				'[spam: %s]',
				print_r( $data, true )
			)
		);
	}

	/**
	 * Log response entry.
	 *
	 * @param mixed  $data Data to log.
	 * @param string $info Optional context.
	 *
	 * @return void
	 */
	public static function response( $data, $info = '' ): void {
		if ( self::isNullLogger() ) {
			return;
		}

		self::write(
			sprintf(
				'[response_get: %s][info: %s]',
				var_export( $data, true ),
				$info
			)
		);
	}

	/**
	 * @param string $command
	 * @param string $type
	 * @param $withCode
	 * @param $raw
	 * @param mixed  $data
	 *
	 * @throws Exception
	 */
	public static function request(
		string $command,
		string $type,
		$withCode,
		$raw,
		$data
	): void {
		if ( self::isNullLogger() ) {
			return;
		}

		ob_start();
		debug_print_backtrace( DEBUG_BACKTRACE_IGNORE_ARGS, 5 );
		$trace = ob_get_contents();
		ob_end_clean();
		self::write(
			sprintf(
				'[Connection] [request] [url: %s] [type: %s] [withCode: %s] [raw: %s] [data: %s] [backtrace: %s]',
				filter_var( $command, FILTER_VALIDATE_URL )
				? $command
				: rtrim( InPostIzi::getApiUrl(), '/' ) . '/' . ltrim( $command, '/' ),
				$type,
				print_r( $withCode, true ),
				var_export( $raw, true ),
				var_export( $data, true ),
				var_export( $trace, true )
			)
		);
	}

	/**
	 * Log data read event.
	 *
	 * @param mixed  $data   Data to log.
	 * @param string $header Optional header.
	 *
	 * @return void
	 */
	public static function dataRead( $data, $header = '' ): void {
		if ( self::isNullLogger() ) {
			return;
		}

		self::write(
			sprintf(
				'[data_event: %s]',
				print_r( $data, true )
			)
		);
	}

	/**
	 * Log order event.
	 *
	 * @param mixed  $data   Data to log.
	 * @param string $header Optional header.
	 *
	 * @return void
	 */
	public static function orderEvent( $data, $header = '' ): void {
		if ( self::isNullLogger() ) {
			return;
		}

		self::write(
			sprintf(
				'[order_event: %s]',
				print_r( $data, true )
			)
		);
	}

	/**
	 * Log basket event.
	 *
	 * @param mixed  $data   Data to log.
	 * @param string $header Optional header.
	 *
	 * @return void
	 */
	public static function basketEvent( $data, $header = '' ): void {
		if ( self::isNullLogger() ) {
			return;
		}

		self::write(
			sprintf(
				'[basket_event: %s]',
				print_r( $data, true )
			)
		);
	}

	/**
	 * Log headers already sent by PHP.
	 *
	 * @return void
	 */
	public static function log_headers_sent(): void {
		self::isNullLogger();

		$headers = headers_list();

		self::write(
			sprintf(
				'[headers_sent: %s]',
				print_r( $headers, true )
			)
		);
	}

	/**
	 * @throws Exception
	 */
	private static function write( $data ): void {

		$logger = inpost_pay()->get_woocommerce_logger();
		$data   = sprintf( '[ID: %s] %s', self::getSessionCustomerId(), $data );
		$logger->log_debug( $data );
	}

	/**
	 * Get a per-client identifier for log grouping.
	 *
	 * @return string|null Identifier.
	 */
	private static function getSessionCustomerId(): ?string {
		if ( self::$clientSessionId === null ) {
			$browserId         = BrowserIdentificationHelper::generate();
			$sessionCustomerId = InPostIzi::getStorage()->getSessionCustomerId();

			if ( $sessionCustomerId ) {
				self::$clientSessionId = $browserId . '_' . $sessionCustomerId;
			} else {
				self::$clientSessionId = $browserId;
			}
		}

		return self::$clientSessionId;
	}

	/**
	 * Check whether logging is disabled.
	 *
	 * @return bool True when logger is disabled.
	 */
	private static function isNullLogger(): bool {
		if ( self::$nullLogger === null ) {
			self::$nullLogger = ! get_option( 'izi_debug' );
		}
		if ( self::$nullLogger ) {
			return true;
		}

		return false;
	}

	/**
	 * Log raw payload.
	 *
	 * @throws Exception
	 */
	public static function rawData( $data, $header = '' ): void {
		self::write(
			sprintf(
				'[header: %s][raw: %s]',
				$header,
				print_r( $data, true )
			)
		);
	}

	/**
	 * Log REST API request details.
	 *
	 * @param mixed $content Content to log.
	 *
	 * @return void
	 * @throws Exception When underlying logger fails.
	 */
	public static function rest_api_request( $content ): void {
		$url = $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

		$log = sprintf( '[url: %s][rest_api_request: %s]', $url, $content );
		self::write( $log );
	}

	/**
	 * Add timestamp prefix.
	 *
	 * @param string $header Header value.
	 *
	 * @return string Header with timestamp.
	 */
	private static function addTimestamp( $header ): string {
		$date = date( 'Y-m-d H:i:s' );

		return "[{$date}] " . $header;
	}
}
