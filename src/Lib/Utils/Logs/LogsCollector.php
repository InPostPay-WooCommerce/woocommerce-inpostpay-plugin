<?php
/**
 * Data Collector.
 *
 * Collects logs, plugin configuration, and system information
 * for support diagnostics. Implements security filtering and data masking.
 *
 * @package    Ilabs\Inpost_Pay
 * @subpackage Lib\Utils\Logs
 */

namespace Ilabs\Inpost_Pay\Lib\Utils\Logs;

/**
 * Class Collector.
 */
class LogsCollector {

	/**
	 * Collect today's WooCommerce logs.
	 *
	 * @param string $date Current date in Y-m-d format.
	 *
	 * @return array Associative array of filename => file contents.
	 */
	public function collect_logs( string $date ): array {
		$logs    = array();
		$log_dir = defined( 'WC_LOG_DIR' ) ? WC_LOG_DIR : WP_CONTENT_DIR . '/uploads/wc-logs/';

		if ( ! is_dir( $log_dir ) || ! is_readable( $log_dir ) ) {
			return $logs;
		}

		$today_start = strtotime( $date . ' 00:00:00' );
		$today_end   = strtotime( $date . ' 23:59:59' );

		$files = glob( $log_dir . '*.log' );

		if ( false === $files ) {
			return $logs;
		}

		foreach ( $files as $file ) {
			$file_time = filemtime( $file );

			if ( $file_time >= $today_start && $file_time <= $today_end ) {
				$filename = basename( $file );
				// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
				$content = file_get_contents( $file );

				if ( false !== $content ) {
					$logs[ $filename ] = $content;
				}
			}
		}

		return $logs;
	}

	/**
	 * Collect plugin configuration.
	 */
	public function collect_plugin_config(): array {
		return ( new PluginSettingsCollector() )->collect();
	}

	/**
	 * Collect system information.
	 *
	 * @return array System information array
	 */
	public function collect_system_info(): array {
		$system_info = array(
			'php_version'         => phpversion(),
			'wordpress_version'   => get_bloginfo( 'version' ),
			'woocommerce_version' => $this->collect_woocommerce_version(),
			'environment_type'    => wp_get_environment_type(),
			'site_url'            => get_site_url(),
			'home_url'            => get_home_url(),
			'multisite'           => is_multisite(),
			'memory_limit'        => ini_get( 'memory_limit' ),
			'max_execution_time'  => ini_get( 'max_execution_time' ),
			'timezone'            => wp_timezone_string(),
			'locale'              => get_locale(),
		);

		$theme                       = wp_get_theme();
		$system_info['active_theme'] = array(
			'name'    => $theme->get( 'Name' ),
			'version' => $theme->get( 'Version' ),
			'author'  => $theme->get( 'Author' ),
		);

		$all_plugins    = get_plugins();
		$active_plugins = get_option( 'active_plugins', array() );

		$system_info['plugins'] = array(
			'total'  => count( $all_plugins ),
			'active' => count( $active_plugins ),
		);

		return $system_info;
	}

	/**
	 * Collects detailed plugin information (active and inactive).
	 */
	public function collect_plugin_info(): array {
		if ( ! function_exists( 'get_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$all_plugins    = get_plugins();
		$active_plugins = get_option( 'active_plugins', array() );

		$plugins = array();

		foreach ( $all_plugins as $file => $data ) {
			$is_active = in_array( $file, $active_plugins, true );

			$plugins[] = array(
				'name'        => $data['Name'] ?? '',
				'version'     => $data['Version'] ?? '',
				'author'      => wp_strip_all_tags( $data['Author'] ?? '' ),
				'author_uri'  => $data['AuthorURI'] ?? '',
				'description' => wp_strip_all_tags( $data['Description'] ?? '' ),
				'is_active'   => $is_active,
				'plugin_file' => $file,
				'plugin_uri'  => $data['PluginURI'] ?? '',
			);
		}

		return $plugins;
	}

	/**
	 * Get WooCommerce version safely.
	 *
	 * @return string WooCommerce version or 'N/A'.
	 */
	private function collect_woocommerce_version(): string {
		if ( defined( 'WC_VERSION' ) ) {
			return WC_VERSION;
		}

		if ( function_exists( 'WC' ) && isset( WC()->version ) ) {
			return WC()->version;
		}

		return 'N/A';
	}
}
