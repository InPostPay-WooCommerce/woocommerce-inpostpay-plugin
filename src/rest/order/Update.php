<?php
/**
 * InPost Pay Order Update REST API Endpoint
 *
 * This file contains the REST API endpoint handler for processing
 * InPost Pay webhook events related to order status updates.
 *
 * @package    Ilabs\Inpost_Pay
 * @subpackage rest\order
 * @author     Ilabs
 * @since      1.0.0
 */

namespace Ilabs\Inpost_Pay\rest\order;

use Ilabs\Inpost_Pay\Lib\OrderAliasHelper;
use Ilabs\Inpost_Pay\Logger;
use Ilabs\Inpost_Pay\rest\Base;
use WP_REST_Request;

/**
 * Class Update
 *
 * Handles webhook events from InPost Pay for order status updates.
 * This class processes incoming payment status changes and updates
 * the corresponding WooCommerce order accordingly.
 *
 * @package    Ilabs\Inpost_Pay\rest\order
 * @since      1.0.0
 */
class Update extends Base {

	/**
	 * Constructor.
	 *
	 * Initializes the Update endpoint handler and sets it as a restricted endpoint.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		$this->restricted = true;
	}

	/**
	 * Describes and registers the REST API endpoint for order updates.
	 *
	 * Registers a POST endpoint that receives webhook events from InPost Pay
	 * when an order's payment status changes. The endpoint verifies the request
	 * signature, processes the event data, and updates the WooCommerce order
	 * accordingly.
	 *
	 * @since 1.0.0
	 *
	 * @api {POST} /inpost/v1/izi/order/(?P<id>[a-zA-Z0-9-]+)/event
	 *
	 * @return void
	 */
	protected function describe() {
		/**
		 * Handles the order update webhook event from InPost Pay.
		 *
		 * @param WP_REST_Request $request The REST API request object containing event data.
		 * @return void Outputs JSON response directly.
		 */
		$this->post['/inpost/v1/izi/order/(?P<id>[a-zA-Z0-9-]+)/event'] = function ( $request ) {

			$this->check_signature( $request );

			$id   = $request->get_param( 'id' );
			$data = $request->get_body();
			$date = gmdate( 'Y-m-d H:i:s' );
			Logger::orderEvent( $data, "Event dla orderu {$id} z {$date}" );
			$data  = json_decode( $data );
			$order = OrderAliasHelper::resolve( $id );

			if ( ! $order ) {
				if ( ! get_option( 'izi_custom_response_enabled', true ) ) {
					http_response_code( 404 );
					die(
						json_encode(
							array(
								'error_code'    => '404',
								'error_message' => 'Order Not Found',
							)
						)
					);
				}

				wp_send_json(
					array(
						'error_code'    => '404',
						'error_message' => 'Order Not Found',
					),
					404
				);
			}
			$status_from_settings = esc_attr( get_option( 'izi_event_' . $data->event_data->payment_status ) );

			if ( property_exists( $data->event_data, 'payment_status' ) ) {
				$order->update_meta_data( 'izi_payment_status', $data->event_data->payment_status );
			}

			if ( property_exists( $data->event_data, 'order_status' ) ) {
				$order->update_meta_data( 'izi_order_status', $data->event_data->order_status );
			}

			if ( property_exists( $data->event_data, 'payment_id' ) ) {
				$order->update_meta_data( 'izi_payment_id', $data->event_data->payment_id );
			}

			if ( property_exists( $data->event_data, 'payment_reference' ) ) {
				$order->update_meta_data( 'izi_payment_reference', $data->event_data->payment_reference );
			}

			if ( property_exists( $data->event_data, 'payment_type' ) ) {
				$order->update_meta_data( 'izi_payment_type', $data->event_data->payment_type );
			}

			$previous_status = $order->get_status();
			$order->update_status( $status_from_settings );
			$order->save();
			do_action( 'woocommerce_order_status_changed', $order->get_id(), $order->get_status(), $previous_status, $order );
			do_action( 'inpost_pay_order_updated', $order->get_id(), $data );
			$status        = $order->get_status();
			$status_labels = get_option( 'izi_status_map' );
			$status        = ( ! empty( $status_labels[ 'wc-' . $status ] ) ) ? $status_labels[ 'wc-' . $status ] : $status;
			$data          = array(
				'order_merchant_status_description' => $status,
			);

			if ( ! get_option( 'izi_custom_response_enabled', true ) ) {
				die( mb_convert_encoding( json_encode( $data ), 'UTF-8' ) );
			}

			wp_send_json( $data );
		};
	}
}
