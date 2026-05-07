<?php

namespace Ilabs\Inpost_Pay\rest\order;

use Ilabs\Inpost_Pay\Logger;
use Ilabs\Inpost_Pay\rest\Base;
use Ilabs\Inpost_Pay\WooCommerce\WooCommerceOrder;

class Get extends Base {
	public function __construct() {
		$this->restricted = true;
	}

	protected function describe() {
		$this->get['/inpost/v1/izi/order/(?P<id>[a-zA-Z0-9-]+)'] = function ( $request ) {
			$this->check_signature( $request );

			$oid = $request->get_param( 'id' );

			try {
				$order = WooCommerceOrder::getOrder( $oid );
				Logger::log( 'Get order from INPOST: ' . $oid . '.' );

				if ( ! $order ) {
					throw new \Exception( 'Order not found' );
				}

				$encoded = $order->encode();
				Logger::response( $encoded );

				if ( ! get_option( 'izi_custom_response_enabled', true ) ) {
					die( mb_convert_encoding( $encoded, 'UTF-8' ) );
				}

				header( 'Content-Type: application/json; charset=UTF-8' );
				echo mb_convert_encoding( $encoded, 'UTF-8' );
				exit;

			} catch ( \Exception $e ) {
				$error = array(
					'error_code'    => 'ORDER_READ_FAILURE',
					'error_message' => 'ORDER NOT FOUND',
				);

				if ( ! get_option( 'izi_custom_response_enabled', true ) ) {
					http_response_code( 404 );
					die( json_encode( $error ) );
				}

				wp_send_json_error( $error, 404 );
			}
		};
	}
}
