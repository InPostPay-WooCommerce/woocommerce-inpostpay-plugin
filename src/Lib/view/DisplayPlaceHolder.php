<?php

namespace Ilabs\Inpost_Pay\Lib\view;

class DisplayPlaceHolder {

	public static function displayProductPlaceholder( $id = null, $styles = '' ) {
		if ( ! $id && is_product() ) {
			global $post;
			$id = $post->ID;
		} elseif ( ! $id ) {
			return;
		}

		$alert_message = __( 'Please complete the required fields to use InpostPay.', 'inpost-pay' );

		echo "<div class='izi-widget-placeholder izi-widget-product' data-product-id='$id' data-alert-message='$alert_message'></div>";
	}

	public static function displayCartPlaceholder( $styles = '' ) {
		echo "<div class='izi-widget-placeholder izi-widget-cart'></div>";
	}

	public static function displayCheckoutPlaceholder( $styles = '' ) {
		echo "<div class='izi-widget-placeholder izi-widget-checkout'></div>";
	}

	public static function displayLoginPagePlaceholder( $styles = '' ) {
		echo "<div class='izi-widget-placeholder izi-widget-login-page'></div>";
	}

	public static function displayMinicartPlaceholder( $styles = '' ) {
		echo "<div class='izi-widget-placeholder izi-widget-minicart'></div>";
	}

	public static function displayOrderPlaceholder( $styles = '' ) {
		echo "<div class='izi-widget-placeholder izi-widget-order'></div>";
	}
}
