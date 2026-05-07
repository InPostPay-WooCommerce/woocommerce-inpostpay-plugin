<?php

namespace Ilabs\Inpost_Pay\rest;

use Ilabs\Inpost_Pay\InpostPay;
use Ilabs\Inpost_Pay\Lib\helpers\LSCacheHelper;
use Ilabs\Inpost_Pay\Logger;
use function Ilabs\Inpost_Pay\inpost_pay;


abstract class Base {

	protected array $post   = array();
	protected array $get    = array();
	protected array $delete = array();

	protected bool $restricted = false;

	public function register(): void {
		if ( $this->restricted && ! $this->can_access() ) {
			return;
		}

		$this->describe();
		add_action(
			'rest_api_init',
			function ( $server ) {
				foreach ( $this->post as $path => $function ) {
					$server->register_route(
						'inpost',
						$path,
						array(
							'methods'             => 'POST',
							'callback'            => fn( $request ) => $this->handle_rest_request( $request, $function ),
							'permission_callback' => function ( $request ) {
								return true;
							},
						)
					);
				}

				foreach ( $this->get as $path => $function ) {
					$server->register_route(
						'inpost',
						$path,
						array(
							'methods'             => 'GET',
							'callback'            => fn( $request ) => $this->handle_rest_request( $request, $function ),
							'permission_callback' => function ( $request ) {
								return true;
							},
						)
					);
				}

				foreach ( $this->delete as $path => $function ) {
					$server->register_route(
						'inpost',
						$path,
						array(
							'methods'             => 'DELETE',
							'callback'            => fn( $request ) => $this->handle_rest_request( $request, $function ),
							'permission_callback' => function ( $request ) {
								return true;
							},
						)
					);
				}
			}
		);
	}

	/**
	 * Check if the current request is allowed access.
	 *
	 * @return bool
	 */
	private function can_access(): bool {
		// Get the client IP address
		$IP = $_SERVER['X_REAL_IP'] ?? $_SERVER['HTTP_CF_CONNECTING_IP'] ?? $_SERVER['REMOTE_ADDR'];

		// Define the local IP addresses
		$localIPs = array( '127.0.0.1', '192.168', '10.', '172.25' );

		// Define the allowed IP addresses
		$allowedIPs = array(
			'35.240.110.20',
			'35.241.226.162',
			'35.204.76.37',
			'34.76.56.163',
			'34.91.99.254',
			'35.240.60.16',
			'34.76.84.87',
			'35.190.198.36',
			'34.118.93.24',
			'34.116.145.216',
		);

		// Check if the client IP is a local IP
		foreach ( $localIPs as $localIP ) {
			if ( strpos( $IP, $localIP ) === 0 ) {
				return true;
			}
		}

		// Check if the client IP is an allowed IP
		return in_array( $IP, $allowedIPs, true );
	}

	abstract protected function describe();

	protected function handle_rest_request( $request, callable $function ) {

		if ( defined( 'REST_REQUEST' ) && REST_REQUEST ) {

			remove_all_actions( 'wp_head' );
			remove_all_actions( 'wp_enqueue_scripts' );
			remove_all_actions( 'wp_print_styles' );
			remove_all_actions( 'wp_print_scripts' );
			remove_all_actions( 'wp_footer' );

			while ( ob_get_level() > 0 ) {
				@ob_end_clean();
			}

			header( 'Content-Type: application/json; charset=utf-8', true );
		}

		$this->allow_origin_header();
		LSCacheHelper::no_cache();
		RestRequest::setRequested();

		return $function( $request );
	}

	private function allow_origin_header(): void {
		if ( $this->restricted ) {
			header( 'Access-Control-Allow-Origin: *' );

			// plugin-version-header.
			$current_plugin_version = inpost_pay()->get_plugin_version();
			header( 'inpay-plugin-version: ' . $current_plugin_version );

		} else {
			if ( array_key_exists( 'HTTP_ORIGIN', $_SERVER ) ) {
				$origin = $_SERVER['HTTP_ORIGIN'];
			} elseif ( array_key_exists( 'HTTP_REFERER', $_SERVER ) ) {
				$origin = $_SERVER['HTTP_REFERER'];
			} else {
				$origin = $_SERVER['REMOTE_ADDR'];
			}
			header( "Access-Control-Allow-Origin: $origin" );

			// plugin-version-header.
			$current_plugin_version = inpost_pay()->get_plugin_version();
			header( 'inpay-plugin-version: ' . $current_plugin_version );

		}
	}

	protected function check_signature( $request, $force = false ): void {
		$authorized = false;
		$headers    = $request->get_headers();

		// Logger::log('[CHECK_SIGNATURE] Headers: ' . var_export( $headers, true ) );

		$request_key_hash  = ( ! empty( $headers['x_public_key_hash'][0] ) ) ? $headers['x_public_key_hash'][0] : '';
		$request_signature = ( ! empty( $headers['x_signature'][0] ) ) ? $headers['x_signature'][0] : '';
		$request_time      = ( ! empty( $headers['x_signature_timestamp'][0] ) ) ? $headers['x_signature_timestamp'][0] : '';
		$request_ver       = ( ! empty( $headers['x_public_key_ver'][0] ) ) ? $headers['x_public_key_ver'][0] : '';

		// Logger::log('[CHECK_SIGNATURE] request_key_hash: ' . var_export( $headers, true ) );
		// Logger::log('[CHECK_SIGNATURE] request_signature: ' . var_export( $request_signature, true ) );
		// Logger::log('[CHECK_SIGNATURE] request_time: ' . var_export( $request_time, true ) );
		// Logger::log('[CHECK_SIGNATURE] request_ver: ' . var_export( $request_ver, true ) );

		$cached_keys = $this->get_signature_keys( $force );

		// Logger::log('[CHECK_SIGNATURE] cached_keys: ' . var_export( $cached_keys, true ) );

		if ( ! empty( $cached_keys->hashes ) && in_array( $request_key_hash, $cached_keys->hashes ) ) {
			Logger::log( '[CHECK_SIGNATURE] Hash found in cached_keys' );
		} else {
			Logger::log( '[CHECK_SIGNATURE] Hash NOT found in cached_keys or empty hashes' );
		}

		if ( ! empty( $cached_keys->hashes ) && in_array( $request_key_hash, $cached_keys->hashes ) ) {
			$body                = $request->get_body();
			$request_body        = ( ! empty( $body ) ) ? $body : '';
			$request_body_hash   = hash( 'sha256', $request_body, true );
			$digest              = base64_encode( $request_body_hash );
			$merchant_id         = $cached_keys->merchant_external_id;
			$generated_signature = base64_encode( "$digest,$merchant_id,$request_ver,$request_time" );
			$api_key             = ( ! empty( $cached_keys->public_keys[0]->public_key_base64 ) ) ? $cached_keys->public_keys[0]->public_key_base64 : '';

			// Logger::log('[CHECK_SIGNATURE] body: ' . var_export( $body, true ) );
			// Logger::log('[CHECK_SIGNATURE] request_body: ' . var_export( $request_body, true ) );
			// Logger::log('[CHECK_SIGNATURE] request_body_hash: ' . var_export( $request_body_hash, true ) );
			// Logger::log('[CHECK_SIGNATURE] digest: ' . var_export( $digest, true ) );
			// Logger::log('[CHECK_SIGNATURE] merchant_id: ' . var_export( $merchant_id, true ) );
			// Logger::log('[CHECK_SIGNATURE] generated_signature: ' . var_export( $generated_signature, true ) );
			// Logger::log('[CHECK_SIGNATURE] api_key: ' . var_export( $api_key, true ) );

			$publicKey         = "-----BEGIN PUBLIC KEY-----\n" . $api_key . "\n-----END PUBLIC KEY-----";
			$publicKeyResource = openssl_get_publickey( $publicKey );

			if ( $publicKeyResource !== false ) {
				// Logger::log('[CHECK_SIGNATURE] authorized true 1' );
				$verifyResult = openssl_verify( $generated_signature, base64_decode( $request_signature ), $publicKeyResource, OPENSSL_ALGO_SHA256 );
				if ( $verifyResult === 1 ) {
					// Logger::log('[CHECK_SIGNATURE] authorized true 2' );
					$request_timestamp = strtotime( $request_time );
					if ( $request_timestamp <= time() + 240 ) {
						// Logger::log('[CHECK_SIGNATURE] authorized true 3' );
						$authorized = true;
					}
				}
			}
		}

		Logger::log( '[CHECK_SIGNATURE] authorized: ' . var_export( $authorized, true ) );

		if ( ! $authorized ) {
			if ( ! $force ) {
				$this->check_signature( $request, true );

				return;
			}

			$error = array( 'error_code' => 'INVALID_SIGNATURE' );

			if ( ! get_option( 'izi_custom_response_enabled', true ) ) {
				http_response_code( 401 );
				die( json_encode( $error ) );
			}

			wp_send_json_error( $error, 401 );
		}
	}

	public function get_signature_keys( $force = false ) {
		if ( $force ) {
			delete_transient( 'izi_signing_keys' );
		}
		$keys = get_transient( 'izi_signing_keys' );
		if ( ! $keys ) {
			$response = InpostPay::get_instance()->get_lib()->get_controller()->getSignatureKeys();

			if ( ! empty( $response ) && ! empty( $response->public_keys ) ) {
				$hashes = array();
				foreach ( $response->public_keys as $key => $value ) {
					$hashes[] = hash( 'sha256', $value->public_key_base64 );
				}
				$response->hashes = $hashes;
				set_transient( 'izi_signing_keys', $response, HOUR_IN_SECONDS );
				$keys = $response;
			}
		}
		return $keys;
	}
}
