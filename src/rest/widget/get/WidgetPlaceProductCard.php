<?php

namespace Ilabs\Inpost_Pay\rest\widget\get;

use Ilabs\Inpost_Pay\hooks\front\FrontDisplayWidget;
use Ilabs\Inpost_Pay\rest\Base;

class WidgetPlaceProductCard extends Base {


	protected function describe() {
		$this->get['/inpost/v1/izi/widget/place_product_card/(?P<product_id>[0-9-]+)'] = function (
			$request
		) {
			$isBlock = $request->get_param( 'isBlock' );

			if (  $isBlock === 'true' || esc_attr( get_option( 'izi_show_details' ) ) ) {
				$product_id = $request->get_param( 'product_id' );
				ob_start();
				( new FrontDisplayWidget() )->displayProduct( $product_id );
				header( 'Content-Type:text/html; charset=UTF-8' );
				die( ob_get_clean() );
			}
			die;
		};
	}
}
