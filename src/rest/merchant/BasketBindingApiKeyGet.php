<?php

namespace Ilabs\Inpost_Pay\rest\merchant;


use Ilabs\Inpost_Pay\Lib\helpers\LSCacheHelper;


use Ilabs\Inpost_Pay\objects\BasketBindingApiKey;
use Ilabs\Inpost_Pay\rest\Base;

class BasketBindingApiKeyGet extends Base {

	protected function describe() {
		add_action( 'wc_ajax_inpost_basket_binding_api_key_get', [ $this, 'basket_binding_api_key_get' ] );
	}

	function basket_binding_api_key_get() {
		LSCacheHelper::no_cache();

		$basket_binding_api_key = ( new BasketBindingApiKey() )->get();

		header( 'content-type: application/json' );

		wp_send_json(
			[
				'basket_binding_api_key' => $basket_binding_api_key,
			]
		 );


	}
}
