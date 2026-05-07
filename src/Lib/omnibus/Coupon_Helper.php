<?php

namespace Ilabs\Inpost_Pay\Lib\omnibus;

class Coupon_Helper {


	public static function is_omnibus_coupon( \WC_Coupon $coupon ): bool {
		$restrictions = $coupon->get_email_restrictions();

		if ( is_array( $restrictions ) && 0 < count( $restrictions ) ) {
			return false;
		}

		return true;
	}

	public static function validate_cart_having_omnibus_coupons( \WC_Cart $cart ): bool {
		$return = false;
		$codes  = $cart->get_applied_coupons();

		foreach ( $codes as $code ) {
			$coupon = new \WC_Coupon( $code );

			if ( ! self::is_omnibus_coupon( $coupon ) ) {

				return false;
			}

			$return = true;
		}

		return $return;
	}
}
