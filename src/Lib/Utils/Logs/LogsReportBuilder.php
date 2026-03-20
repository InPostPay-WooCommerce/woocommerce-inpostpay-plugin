<?php
/**
 * Report Builder.
 *
 * Structures collected data into a standardized report format for ZIP export.
 *
 * @package    Ilabs\Inpost_Pay
 * @subpackage Lib\Utils\Logs
 */

namespace Ilabs\Inpost_Pay\Lib\Utils\Logs;

/**
 * Class ReportBuilder.
 */
class LogsReportBuilder {

	/**
	 * Build structured report data.
	 *
	 * @param array  $logs Array of log filename => content.
	 * @param array  $config Plugin configuration array.
	 * @param array  $system System information array.
	 * @param string $current_date Date in Y-m-d format.
	 * @param array  $plugins Array of plugin data.
	 *
	 * @return array Structured report data.
	 */
	public function build(
		array $logs,
		array $config,
		array $system,
		string $current_date,
		array $plugins = array()
	): array {
		$report = array();

		foreach ( $logs as $filename => $content ) {
			$path            = sprintf( 'logs/%s/%s', $current_date, $filename );
			$report[ $path ] = $content;
		}

		$report['report/plugin-config.json'] = wp_json_encode(
			$config,
			JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
		);

		$report['report/system.json'] = wp_json_encode(
			$system,
			JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
		);

		if ( ! empty( $plugins ) ) {
			$report['report/plugins.json'] = wp_json_encode(
				$plugins,
				JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
			);
		}

		$report['README.txt'] = $this->generate_readme( $current_date );

		return $report;
	}

	/**
	 * Generate README content.
	 *
	 * Creates a human-readable overview of the exported data
	 * with generation metadata.
	 *
	 * @param string $date Generation date.
	 *
	 * @return string README content.
	 */
	private function generate_readme( string $date ): string {
		$plugin_version = $this->get_plugin_version();
		$timestamp      = current_time( 'Y-m-d H:i:s' );

		$readme  = "InPost Pay - Support Data Export\n";
		$readme .= "==================================\n\n";
		$readme .= sprintf( "Generated: %s\n", $timestamp );
		$readme .= sprintf( "Plugin Version: %s\n", $plugin_version );
		$readme .= sprintf( "Log Date: %s\n\n", $date );
		$readme .= "Contents:\n";
		$readme .= "---------\n";
		$readme .= "- logs/         : WooCommerce logs from today\n";
		$readme .= "- report/       : Plugin list, InPost Pay configuration and system information\n\n";
		$readme .= "Security Notice:\n";
		$readme .= "----------------\n";
		$readme .= "All sensitive data (keys, tokens) has been masked.\n";
		$readme .= "No customer data, orders, or personal information is included.\n\n";
		$readme .= "This export is intended for technical support purposes only.\n";

		return $readme;
	}

	/**
	 * Get plugin version.
	 *
	 * @return string Plugin version or 'Unknown'.
	 */
	private function get_plugin_version(): string {
		$plugin_data = get_file_data(
			dirname( WOOCOMMERCE_INPOST_PAY_PLUGIN_FILE ) . '/inpost-pay.php',
			array( 'Version' => 'Version' )
		);

		return $plugin_data['Version'] ?? 'Unknown';
	}
}
