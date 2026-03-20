<?php
/**
 * REST API endpoint for widget basket summary.
 *
 * @package Ilabs\Inpost_Pay\Package
 */

namespace Ilabs\Inpost_Pay\rest\widget\get;

use Ilabs\Inpost_Pay\hooks\front\FrontDisplayWidget;
use Ilabs\Inpost_Pay\Lib\helpers\Woo_Commerce_Session_Helper;
use Ilabs\Inpost_Pay\rest\Base;

/**
 * REST API endpoint class for widget basket summary.
 *
 * @package Ilabs\Inpost_Pay\Package
 */
class WidgetPlaceBasketSummary extends Base {

	/**
	 * Describe the REST API endpoint for widget basket summary.
	 *
	 * @return void
	 */
	protected function describe(): void {
		$this->get['/inpost/v1/izi/widget/place_basket_summary'] = static function (
			$request
		) {
			$is_block = $request->get_param( 'isBlock' );

			if ( 'true' === $is_block || esc_attr( get_option( 'izi_show_basket' ) ) ) {

				if ( ! Woo_Commerce_Session_Helper::has_session_cookie() ) {
					die;
				}

				if ( null === WC()->session ) {
					WC()->session = new \WC_Session_Handler();
					WC()->session->init();
				}

				WC()->initialize_cart();

				ob_start();
				( new FrontDisplayWidget() )->displayCart();
				header( 'Content-Type:text/html; charset=UTF-8' );
				die( ob_get_clean() );
			}
			die;
		};
	}
}
