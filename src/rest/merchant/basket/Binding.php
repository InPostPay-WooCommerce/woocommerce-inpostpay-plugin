<?php

declare( strict_types=1 );

namespace Ilabs\Inpost_Pay\rest\merchant\basket;

use Ilabs\Inpost_Pay\Lib\Analytics\Analytics;
use Ilabs\Inpost_Pay\Lib\BasketIdentification;
use Ilabs\Inpost_Pay\Lib\helpers\CookieHelper;
use Ilabs\Inpost_Pay\Lib\helpers\LSCacheHelper;
use Ilabs\Inpost_Pay\Lib\Remote;
use Ilabs\Inpost_Pay\Logger;
use Ilabs\Inpost_Pay\models\CartSessionService;
use Ilabs\Inpost_Pay\rest\Base;
use JsonException;
use function Ilabs\Inpost_Pay\inpost_pay_container;

class Binding extends Base {

	private CartSessionService $cart_session;

	public function __construct() {
		$this->cart_session = inpost_pay_container()->get( CartSessionService::SERVICE_KEY );
	}

	protected function describe(): void {
		add_action( 'wc_ajax_inpost_pay_binding', array( $this, 'inpost_pay_binding' ) );
	}

	public function inpost_pay_binding(): void {
		LSCacheHelper::no_cache();

		$this->cart_session->initiate_wc_cart();
		$this->cart_session->store_current();

		$cart_id             = BasketIdentification::get();
		$basketBindingApiKey = $this->cart_session->basket_binding_api_key( $cart_id );

		if ( $basketBindingApiKey ) {
			$this->send_response( $basketBindingApiKey );
		}

		try {
			$remote                 = new Remote();
			$response               = $remote->basket_binding_put( json_encode( array(), JSON_THROW_ON_ERROR ) );
			$basket_binding_api_key = $response->basket_binding_api_key ?? null;

			Logger::log( 'BASKET_BINDING_API_KEY: ' . var_export( $basket_binding_api_key, true ) );

			if ( $basket_binding_api_key ) {
				$this->cart_session->set_basket_binding_api_key( $cart_id, $basket_binding_api_key );
				CookieHelper::set_basket_binding_api_key( $basket_binding_api_key );
			}

			( new Analytics() )->store_from_post();

			$this->send_response( $response );

		} catch ( JsonException $e ) {
			Logger::log( '[Binding] JSON error: ' . $e->getMessage() );
			wp_send_json_error( array( 'message' => 'Invalid JSON response' ), 500 );
		}
	}

	private function send_response( $data ): void {
		$current_plugin_version = inpost_pay()->get_plugin_version();
		header( 'inpay-plugin-version: ' . $current_plugin_version );

		if ( ! get_option( 'izi_custom_response_enabled', true ) ) {
			header( 'Content-Type: application/json; charset=utf-8' );
			die( is_string( $data ) ? $data : wp_json_encode( $data ) );
		}

		wp_send_json( $data );
	}
}
