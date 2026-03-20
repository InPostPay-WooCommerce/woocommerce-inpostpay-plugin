<?php
/**
 * Remote API handler for InPost Pay integration.
 *
 * This file contains the Remote class which handles API communication
 * with InPost services for basket operations, order management,
 * and event tracking.
 *
 * @package Ilabs\Inpost_Pay\Lib
 * @since 1.0.0
 */

namespace Ilabs\Inpost_Pay\Lib;

use Ilabs\Inpost_Pay\Integration\Currency\CurrencyHelper;
use Ilabs\Inpost_Pay\Logger;

/**
 * Remote API handler for InPost Pay integration.
 *
 * Handles API communication with InPost services for basket operations,
 * order management, and event tracking.
 *
 * @package Ilabs\Inpost_Pay\Lib
 */
class Remote extends Connection {

	public static bool $done = false;
	private string $order_id;

	/**
	 * Retrieves information about the current basket.
	 *
	 * @return array
	 */
	public function basket_get(): array {
		return $this->request( 'v1/izi/basket/' . BasketIdentification::get() );
	}

	/**
	 * Sends a PUT request to the basket endpoint with the provided data.
	 *
	 * @param array|string $data The data to send in the request. If it's an array, it will be encoded as JSON; if it's already a string, it will be sent raw.
	 * @param bool         $raw If true, the data will be sent as a raw string without JSON encoding; otherwise, it will be decoded from JSON and sent with proper headers.
	 *
	 * @return mixed The response object from the request. In case of an unsupported currency, returns an error object with error_code and message properties.
	 */
	public function basket_put( $data, bool $raw = false ) {
		if ( self::$done ) {
			return null;
		}
		self::$done = true;

		if ( $raw ) {
			$to_send = $data;
		} else {
			$to_send = json_decode( $data );
		}

		if ( ! in_array( CurrencyHelper::getCurrentCurrency(), CurrencyHelper::AVAILABLE_CURRENCIES, true ) ) {
			return (object) array(
				'error_code' => 'UNSUPPORTED_CURRENCY',
				'message'    => 'Only PLN currency is supported.',
			);
		}

		[
			$response,
			$code,
		] = $this->request(
			'v2/izi/basket/' . BasketIdentification::get(),
			'PUT',
			$to_send,
			true,
			$raw
		);
		Logger::response(
			$data,
			'Merchant sends basket put for ' . BasketIdentification::get()
		);
		Logger::response(
			$code,
			'Basket app code for basket put'
		);
		Logger::response(
			json_encode( $response ),
			'Basket app response for basket put'
		);

		return $response;
	}


	/**
	 * Sends an event to the InPost Pay API.
	 *
	 * @param string      $order_id The identifier of the order to which the event is being sent.
	 * @param string      $status The current status of the order.
	 * @param array       $ref_list An associative array containing delivery references related to the order.
	 * @param string|null $order_status The additional status of the order, if available. Defaults to null.
	 */
	public function send_order_event( string $order_id, string $status, array $ref_list, ?string $order_status = null ): void {
		$data = array(
			'event_id'        => time(),
			'event_data_time' => gmdate( 'Y-m-d\TH:i:s.000\Z' ),
			'event_data'      => array(
				'order_merchant_status_description' => $status,
				'delivery_references_list'          => $ref_list,
			),
		);
		if ( $order_status ) {
			$data['event_data']['order_status'] = $order_status;
		}
		[ $response, $code ] = $this->request(
			"v1/izi/order/{$order_id}/event",
			'POST',
			$data,
			true
		);
		Logger::response(
			json_encode( $data, JSON_THROW_ON_ERROR ),
			'Merchant sends event for ' . $order_id
		);
		Logger::response(
			$code,
			'Basket app code for event'
		);
	}

	/**
	 * Deletes a binding for the current basket.
	 *
	 * @return mixed The response object from the request, containing the result of the deletion operation or error information if applicable.
	 */
	public function basket_binding_delete() {
		return $this->request(
			'v1/izi/basket/' . BasketIdentification::get() . '/binding',
			'DELETE'
		);
	}


	/**
	 * Sends a PUT request to the basket binding endpoint with the provided data.
	 *
	 * @param array|string $data The data to send in the request. If it's an array, it will be encoded as JSON; if it's already a string, it will be sent raw.
	 *
	 * @return mixed The response object from the request. In case of an unsupported currency, returns an error object with error_code and message properties.
	 */
	public function basket_binding_put( $data ) {
		if ( ! in_array( CurrencyHelper::getCurrentCurrency(), CurrencyHelper::AVAILABLE_CURRENCIES, true ) ) {
			return (object) array(
				'error_code' => 'UNSUPPORTED_CURRENCY',
				'message'    => 'Only PLN currency is supported.',
			);
		}

		$to_send = json_decode( $data, false, 512, JSON_THROW_ON_ERROR );

		[
			$response,
			$code,
		] = $this->request(
			'v2/izi/basket/' . BasketIdentification::get() . '/binding',
			'PUT',
			$to_send,
			true,
			false
		);

		return $response;
	}
}
