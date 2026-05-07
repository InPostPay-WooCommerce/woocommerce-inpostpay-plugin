<?php
namespace Ilabs\Inpost_Pay\Lib\Product\CustomMeta;

abstract class AbstractProductMeta {
	public static function get_meta( $post_ID, $key, $default, $single = true ) {
		$meta_value = get_post_meta( $post_ID, $key, $single );
		if ( empty( $meta_value ) ) {
			return $default;
		}

		return $meta_value;
	}
}
