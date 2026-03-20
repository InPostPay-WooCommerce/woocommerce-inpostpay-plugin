<?php

namespace Ilabs\Inpost_Pay\rest\basket;

use Ilabs\Inpost_Pay\Lib\BasketIdentification;
use Ilabs\Inpost_Pay\Lib\InPostIzi;
use Ilabs\Inpost_Pay\Logger;
use Ilabs\Inpost_Pay\models\CartSessionService;
use Ilabs\Inpost_Pay\rest\Base;
use Ilabs\Inpost_Pay\WooCommerce\WooCommerceBasket;
use function Ilabs\Inpost_Pay\inpost_pay_container;

class Confirmation extends Base {
	private CartSessionService $cart_session;

	public function __construct() {
		/**
		 * Get from container DI.
		 *
		 * @var CartSessionService $cart_session
		 */
		$this->cart_session = inpost_pay_container()->get( CartSessionService::SERVICE_KEY );
		$this->restricted   = true;
	}

	protected function describe() {
		$this->post['/inpost/v1/izi/basket/(?P<id>[a-zA-Z0-9-]+)/confirmation'] = function ( $request ) {
			Logger::log( 'Confirmation for ID: ' . $request->get_param( 'id' ) );
			$this->check_signature( $request );

			$content = $request->get_body();
			$id      = $request->get_param( 'id' );

			if ( null === $this->cart_session->get_entity_by_cart_id( $id ) ) {
				Logger::log( 'No cart session found for ID: ' . $id );
				wp_send_json_error(
					array(
						'error_code'    => 'BASKET_NOT_FOUND',
						'error_message' => 'Basket not found.',
					),
					404
				);
			}

			Logger::rest_api_request( $content );

			$status = json_decode( $content, true )['status'];
			if ( $status === 'REJECT' ) {
				die( json_encode( array( 'STATUS' => 'REJECT' ) ) );
			}

			$this->cart_session->set_confirmation_to_cart( $id, $content );
			$this->cart_session->set_session_by_cart_id( $id );
			BasketIdentification::set( $id );

			$basket = WooCommerceBasket::getBasket()->encode();
			$this->cart_session->set_cart_cache_by_id( $id, $basket );
			$this->cart_session->set_wc_cart_snapshot( $id );
			Logger::response( 'Confirmation basket response: ' . var_export( $basket, true ) );

			// plugin-version-header.
			$current_plugin_version = inpost_pay()->get_plugin_version();
			header( 'inpay-plugin-version: ' . $current_plugin_version );
			header( 'content-type: application/json' );

			if ( ! get_option( 'izi_custom_response_enabled', true ) ) {
				die( $basket );
			}

			wp_send_json( json_decode( $basket, true ) );
		};
	}
}
