<?php

namespace Ilabs\Inpost_Pay\rest\widget\get;

use Ilabs\Inpost_Pay\hooks\front\FrontDisplayWidget;
use Ilabs\Inpost_Pay\Lib\helpers\Woo_Commerce_Session_Helper;
use Ilabs\Inpost_Pay\rest\Base;

class WidgetCheckoutPage extends Base {

	protected function describe() {

		$this->get['/inpost/v1/izi/widget/place_checkout_page'] = function (
			$request
		) {
			$isBlock = $request->get_param( 'isBlock' );

			if ( $isBlock === 'true' || esc_attr( get_option( 'izi_show_checkout' ) ) ) {

				if ( ! Woo_Commerce_Session_Helper::has_session_cookie() ) {
					die;
				}

				if ( null === WC()->session ) {
					WC()->session = new \WC_Session_Handler();
					WC()->session->init();
				}

				ob_start();
				( new FrontDisplayWidget() )->displayCheckout();
				header('Content-Type:text/html; charset=UTF-8');
				die( ob_get_clean() );
			}
			die;
		};
	}
}
