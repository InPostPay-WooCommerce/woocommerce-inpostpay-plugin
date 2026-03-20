<?php

namespace Ilabs\Inpost_Pay\Lib\Coupons\Fields;

interface FieldInterface {
	public function render( int $coupon_id ): void;
	public function save( int $post_id ): void;
}
