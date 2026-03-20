<?php

namespace Ilabs\Inpost_Pay\Integration\Blocks;

use Ilabs\Inpost_Pay\Lib\InPostIzi;

class BlockRender {
	public static function render_inpost_button( $attributes ): string {
		global $post;

		if ( ! is_array( $attributes ) ) {
			$attributes = json_decode( $attributes, true );
		}

		$defaults = [
			'variant'      => 'primary',
			'bindingPlace' => 'PRODUCT_CARD',
			'background'   => 'bright',
			'frameStyle'   => 'none',
			'language'     => substr( get_locale(), 0, 2 ),
		];

		$attributes = array_merge( $defaults, $attributes );

		if ( $attributes['bindingPlace'] === 'BASKET_SUMMARY' ) {
			if ( ! function_exists( 'WC' ) || ! WC()->cart || WC()->cart->is_empty() ) {
				return '';
			}
		}

		$product_id = $attributes['bindingPlace'] === 'PRODUCT_CARD' ? $post->ID : null;

		return InPostIzi::render(
			$product_id,
			true, // don't echo, return instead
			$attributes['variant'] === 'dark',
			$attributes['variant'] === 'primary',
			$attributes['bindingPlace'],
			$attributes['frameStyle'],
			false,
			false // not from elementor
		);
	}
}
