<?php

namespace Ilabs\Inpost_Pay\Lib\omnibus;

use WC_Product;

class Price_Note_Template_Service {

	public function get_product_note_html( float $lowest_price, WC_Product $product ): string {
		$note = __( 'Lowest price 30 days before the discount:', 'inpost-pay' );

		$formatted_price = inpost_pay()->get_omnibus()->format_omnibus_price( $lowest_price, $product );

		return apply_filters(
			'ilabs_omnibus_price_note',
			"<p class='ilabs-omnibus-price-note'>$note $formatted_price</p>",
			$note,
			$formatted_price
		);
	}

	public function output_product_note_html( float $lowest_price, WC_Product $product ) {
		echo wp_kses( $this->get_product_note_html( $lowest_price, $product ), [ 'p' => [ 'class' => [] ] ] );
	}
}
