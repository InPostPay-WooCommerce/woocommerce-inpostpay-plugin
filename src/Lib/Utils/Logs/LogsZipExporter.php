<?php
/**
 * Logs ZIP Exporter
 *
 * Creates a ZIP archive with collected logs and streams it directly
 * to the browser as a download. Uses ZipStream-PHP for memory-efficient
 * streaming without temporary files.
 *
 * @package    Ilabs\Inpost_Pay
 * @subpackage Lib\Utils\Logs
 */

namespace Ilabs\Inpost_Pay\Lib\Utils\Logs;

use Isolated\Inpost_Pay\ZipStream\ZipStream\Option\Archive;
use Isolated\Inpost_Pay\ZipStream\ZipStream\ZipStream;

/**
 * Class LogsZipExporter
 *
 * Handles ZIP file generation and streaming for log export.
 */
class LogsZipExporter {

	/**
	 * Exports report data as a downloadable ZIP archive.
	 *
	 * @param array  $report_data Structured report data from ReportBuilder.
	 *                            Format: [ 'path/in/zip/file.txt' => 'content', ... ].
	 * @param string $date        Date for filename (Y-m-d format).
	 *
	 * @return void
	 */
	public function export( array $report_data, string $date ): void {
		$filename = $this->generate_filename( $date );

		$this->clear_output_buffers();
		$this->send_headers( $filename );

		$options = new Archive();
		$options->setOutputStream( fopen( 'php://output', 'wb' ) );
		$options->setSendHttpHeaders( false );

		try {
			$zip = new ZipStream( null, $options );

			foreach ( $report_data as $path => $content ) {
				$zip->addFile( $path, $content );
			}

			$zip->finish();
		} catch ( \Exception $e ) {
			if ( ! headers_sent() ) {
				wp_die(
					esc_html__( 'Failed to create ZIP archive.', 'inpost-pay' ),
					esc_html__( 'Export Error', 'inpost-pay' ),
					array( 'response' => 500 )
				);
			}
		}

		exit;
	}

	/**
	 * Generates a ZIP filename.
	 *
	 * @param string $date Date in Y-m-d format.
	 *
	 * @return string The generated filename.
	 */
	private function generate_filename( string $date ): string {
		return sprintf( 'inpostpay-logs-%s.zip', $date );
	}

	/**
	 * Clears all output buffers to prevent ZIP corruption.
	 *
	 * @return void
	 */
	private function clear_output_buffers(): void {
		while ( ob_get_level() > 0 ) {
			ob_end_clean();
		}
	}

	/**
	 * Sends HTTP headers required for ZIP file download.
	 *
	 * @param string $filename The name of the ZIP file.
	 *
	 * @return void
	 */
	private function send_headers( string $filename ): void {
		header( 'Content-Type: application/zip' );
		header( 'Content-Disposition: attachment; filename="' . $filename . '"' );
		header( 'Content-Transfer-Encoding: binary' );
		header( 'Cache-Control: must-revalidate, post-check=0, pre-check=0' );
		header( 'Pragma: public' );
		header( 'Expires: 0' );
		header( 'X-Accel-Buffering: no' );
	}
}
