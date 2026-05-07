<?php
/**
 * Shutdown handler for the InPost Pay plugin.
 *
 * @package InPost Pay
 * @author  iLabs
 * @since   2.0.7.1
 */

declare( strict_types=1 );

namespace Ilabs\Inpost_Pay;

/**
 * Registers a PHP shutdown function that deactivates the plugin on fatal errors.
 *
 * Must be registered before any plugin bootstrap code runs, so that even a fatal
 * error during early loading is caught.
 *
 * Skipped for AJAX, REST API and WP-Cron requests — a fatal in those contexts
 * should not deactivate the plugin for all subsequent page loads.
 */
class ShutdownHandler {

	/**
	 * Absolute path to the main plugin file.
	 *
	 * @var string
	 */
	private string $plugin_file;

	/**
	 * @param string $plugin_file Absolute path to the main plugin file (__FILE__ from inpost-pay.php).
	 */
	private function __construct( string $plugin_file ) {
		$this->plugin_file = $plugin_file;
	}

	/**
	 * Register the shutdown handler.
	 *
	 * @param string $plugin_file Absolute path to the main plugin file.
	 */
	public static function register( string $plugin_file ): void {
		$handler = new self( $plugin_file );
		register_shutdown_function( array( $handler, 'handle' ) );
	}

	/**
	 * Shutdown callback. Deactivates the plugin if a fatal error originated in its directory.
	 */
	public function handle(): void {
		if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
			return;
		}
		if ( defined( 'REST_REQUEST' ) && REST_REQUEST ) {
			return;
		}
		if ( defined( 'DOING_CRON' ) && DOING_CRON ) {
			return;
		}

		$error = error_get_last();

		if ( ! $error ) {
			return;
		}

		$fatal_types = array( E_ERROR, E_PARSE, E_COMPILE_ERROR );
		$is_fatal    = in_array( $error['type'], $fatal_types, true );
		$is_ours     = false !== strpos( $error['file'], plugin_dir_path( $this->plugin_file ) );

		if ( ! $is_fatal || ! $is_ours ) {
			return;
		}

		if ( ! function_exists( 'deactivate_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$message = 'Wtyczka InPost Pay została dezaktywowana z powodu błędu: ' . $error['message'];

		deactivate_plugins( plugin_basename( $this->plugin_file ) );
		// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
		error_log( $message );
		wp_die( esc_html( $message ) );
	}
}
