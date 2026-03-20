<?php

namespace Ilabs\Inpost_Pay\rest\basket;

use Ilabs\Inpost_Pay\rest\Base;
use Ilabs\Inpost_Pay\Logger;
use Ilabs\Inpost_Pay\models\CartSessionService;
use function Ilabs\Inpost_Pay\inpost_pay_container;

class Get extends Base
{
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

	protected function describe()
	{
		$this->get['/inpost/v1/izi/basket/(?P<id>[a-zA-Z0-9-]+)'] = function ($request) {

			$this->check_signature($request);

			$id = $request->get_param('id');
			$this->cart_session->set_session_by_cart_id($id);
			$basket = $this->cart_session->get_cart_cache_by_id($id);
			$basket = str_replace('\/', '/', mb_convert_encoding($basket, 'UTF-8'));

			Logger::log('###GET BASKET###');
			Logger::response($basket);

			$current_plugin_version = inpost_pay()->get_plugin_version();
			header('inpay-plugin-version: ' . $current_plugin_version);

			if ( ! get_option( 'izi_custom_response_enabled', true ) ) {
				header('content-type: application/json');
				die($basket);
			}

			wp_send_json(
				json_decode($basket, true),
				200
			);
		};
	}
}

