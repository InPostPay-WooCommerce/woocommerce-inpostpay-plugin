<?php

namespace Ilabs\Inpost_Pay\rest\widget\get;

use Ilabs\Inpost_Pay\hooks\front\FrontDisplayWidget;
use Ilabs\Inpost_Pay\rest\Base;

class WidgetLoginPage extends Base {

	protected function describe() {

		$this->get['/inpost/v1/izi/widget/place_login_page'] = function (
			$request
		) {
			$isBlock = $request->get_param( 'isBlock' );

			if (  $isBlock === 'true' || esc_attr( get_option( 'izi_show_login_page' ) ) ) {

				ob_start();
				( new FrontDisplayWidget() )->displayLoginPage();
				header('Content-Type:text/html; charset=UTF-8');
				die( ob_get_clean() );
			}
			die;
		};
	}
}
