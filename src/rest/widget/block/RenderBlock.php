<?php

namespace Ilabs\Inpost_Pay\rest\widget\block;

use Ilabs\Inpost_Pay\hooks\front\FrontDisplayWidget;
use Ilabs\Inpost_Pay\Integration\Blocks\BlockRender;
use Ilabs\Inpost_Pay\Lib\BasketIdentification;
use Ilabs\Inpost_Pay\rest\Base;

class RenderBlock extends Base {

	protected function describe() {

		$this->get['/inpost/v1/izi/widget/render/block'] = function (
			$request
		) {
			if ( esc_attr( get_option( 'izi_show_checkout' ) ) ) {
				$attributes = $request->get_param('attributes');
				ob_start();
				BlockRender::render_inpost_button( $attributes );
				header('Content-Type:text/html; charset=UTF-8');
				die( ob_get_clean() );
			}
			die;
		};
	}

}
