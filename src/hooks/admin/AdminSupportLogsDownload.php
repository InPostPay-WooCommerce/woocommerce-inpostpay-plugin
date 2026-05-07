<?php
/**
 * Support Logs Download Hook.
 *
 * @package    Ilabs\Inpost_Pay
 * @subpackage Hooks
 */

namespace Ilabs\Inpost_Pay\hooks\admin;

use Ilabs\Inpost_Pay\hooks\Base;
use Ilabs\Inpost_Pay\Lib\Utils\Logs\LogsCollector;
use Ilabs\Inpost_Pay\Lib\Utils\Logs\LogsReportBuilder;
use Ilabs\Inpost_Pay\Lib\Utils\Logs\LogsZipExporter;

/**
 * Class SupportLogsDownload.
 */
class AdminSupportLogsDownload extends Base {

	/**
	 * Attach WordPress hooks.
	 *
	 * @return void
	 */
	public function attach_hook(): void {
		add_action( 'admin_post_izi_download_logs', array( $this, 'handleDownload' ) );
	}

	/**
	 * Handle log download request.
	 *
	 * @return void
	 */
	public function handleDownload(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die(
				esc_html__( 'You do not have permission to access this resource.', 'inpost-pay' ),
				esc_html__( 'Permission Denied', 'inpost-pay' ),
				array( 'response' => 403 )
			);
		}

		check_admin_referer( 'izi_download_logs' );

		if ( ! class_exists( 'ZipArchive' ) ) {
			wp_die(
				esc_html__( 'PHP ZipArchive extension is required to download logs.', 'inpost-pay' ),
				esc_html__( 'Missing Extension', 'inpost-pay' ),
				array( 'response' => 500 )
			);
		}

		$collector      = new LogsCollector();
		$report_builder = new LogsReportBuilder();
		$exporter       = new LogsZipExporter();

		$current_date = current_time( 'Y-m-d' );

		$logs    = $collector->collect_logs( $current_date );
		$config  = $collector->collect_plugin_config();
		$system  = $collector->collect_system_info();
		$plugins = $collector->collect_plugin_info();

		$report_data = $report_builder->build( $logs, $config, $system, $current_date, $plugins );

		$exporter->export( $report_data, $current_date );

		exit;
	}
}
