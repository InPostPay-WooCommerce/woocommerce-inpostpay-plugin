<?php
/**
 * Fetcher class for handling HTTP requests.
 *
 * This class provides methods for making HTTP requests using cURL,
 * supporting both GET and POST requests with JSON payloads.
 *
 * @package Ilabs\Inpost_Pay\Package
 */

declare( strict_types=1 );

namespace Ilabs\Inpost_Pay\Lib;

use Ilabs\Inpost_Pay\Logger;
use function Ilabs\Inpost_Pay\inpost_pay;

/**
 * Fetcher class for handling HTTP requests.
 *
 * This class provides methods for making HTTP requests using cURL,
 * supporting both GET and POST requests with JSON payloads.
 */
class Fetcher {
	/**
	 * cURL resource instance.
	 *
	 * @var resource|false
	 */
	private $curl;

	/**
	 * Constructor.
	 */
	public function __construct() {
	}

	/**
	 * Send form-encoded POST request.
	 *
	 * @param string $url     The request URL.
	 * @param array  $payload The form data to send.
	 *
	 * @return mixed The response data.
	 */
	public function query( $url, $payload ) {
		$this->init();

		$this->curl_join_headers( array( 'content-type: application/x-www-form-urlencoded' ) );
		curl_setopt( $this->curl, CURLOPT_POSTFIELDS, http_build_query( $payload ) );

		return $this->execute( $url );
	}

	/**
	 * Initialize cURL session with default options.
	 *
	 * Sets up SSL, return transfer, and redirect following options.
	 * Also closes session write to prevent blocking.
	 */
	public function init(): void {
		session_write_close();
		$this->curl = curl_init();

		curl_setopt( $this->curl, CURLOPT_SSLVERSION, 1 );
		curl_setopt( $this->curl, CURLINFO_HEADER_OUT, 1 );
		curl_setopt( $this->curl, CURLOPT_RETURNTRANSFER, 1 );
		curl_setopt( $this->curl, CURLOPT_FOLLOWLOCATION, 1 );
	}

	/**
	 * Merge default headers with provided headers.
	 *
	 * @param array $headers Additional headers to merge.
	 */
	public function curl_join_headers( $headers ) {
		curl_setopt( $this->curl, CURLOPT_HTTPHEADER, array_merge( $this->headers(), $headers ) );
	}

	/**
	 * Get default headers for requests.
	 *
	 * @return array Default headers array.
	 */
	public function headers() {
		return array();
	}

	/**
	 * Execute cURL request and return response.
	 *
	 * @param string $url The URL to request.
	 *
	 * @return array [decoded_response, http_code].
	 */
	public function execute( $url ): array {
		curl_setopt( $this->curl, CURLOPT_URL, $url );
		curl_setopt( $this->curl, CURLOPT_TIMEOUT, 10 );
		$output   = curl_exec( $this->curl );
		$httpcode = curl_getinfo( $this->curl, CURLINFO_HTTP_CODE );
		curl_close( $this->curl );

		return array( is_string( $output ) ? json_decode( $output ) : null, $httpcode );
	}

	/**
	 * Send HTTP request with JSON payload.
	 *
	 * @param string     $url      The request URL.
	 * @param string     $type     HTTP method (GET, POST, etc.). Default 'GET'.
	 * @param array|null $payload  Request payload. Default null.
	 * @param bool       $with_code Whether to return HTTP code. Default false.
	 * @param bool       $raw      Whether to send raw payload without JSON encoding. Default false.
	 *
	 * @return mixed The response data or [data, code] if withCode is true.
	 */
	public function fetch( $url, $type = 'GET', $payload = null, $with_code = false, $raw = false ) {
		$this->init();
		$current_plugin_version = inpost_pay()->get_plugin_version();
		$headers                = array(
			'Content-Type:application/json',
			'inpay-plugin-version: ' . $current_plugin_version,
		);
		curl_setopt( $this->curl, CURLOPT_CUSTOMREQUEST, $type );
		$payloadLength = 0;
		if ( $payload && ( ( is_array( $payload ) && count( $payload ) ) || ! is_array( $payload ) ) ) {
			curl_setopt( $this->curl, CURLOPT_POST, 1 );
			$json_payload = '';
			if ( $raw ) {
				$json_payload = $payload;
			} else {
				$json_payload = mb_convert_encoding( wp_json_encode( $payload, JSON_UNESCAPED_SLASHES ), 'UTF-8' );
			}

			$payloadLength = strlen( $json_payload );
			$headers[]     = 'Content-length: ' . $payloadLength;
			if ( method_exists( Logger::class, 'rawData' ) ) {
				Logger::rawData( $json_payload );
			}
			curl_setopt( $this->curl, CURLOPT_POSTFIELDS, $json_payload );
		} else {
			$headers[] = 'Content-length:0';
		}

		$this->curl_join_headers( $headers );
		$data = $this->execute( $url );

		Logger::request(
			$url,
			$type,
			$with_code,
			$payload,
			$json_payload ?? '',
		);

		return $with_code ? $data : $data[0];
	}

	/**
	 * Build URL-encoded parameters from array.
	 *
	 * Converts associative array to URL-encoded string format: name[key]=value&name[key2]=value2
	 *
	 * @param string $name  The parameter name prefix.
	 * @param array  $url_params The associative array of values.
	 *
	 * @return string URL-encoded parameter string.
	 */
	public function build_params_from_array( string $name, array $url_params ): string {
		$params = array();
		foreach ( $url_params as $key => $value ) {
			$params[] = $name . '[' . rawurlencode( (string) $key ) . ']=' . rawurlencode( (string) $value );
		}

		return implode( '&', $params );
	}
}
