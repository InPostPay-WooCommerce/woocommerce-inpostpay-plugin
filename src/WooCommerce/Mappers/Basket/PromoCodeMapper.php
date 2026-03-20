<?php

namespace Ilabs\Inpost_Pay\WooCommerce\Mappers\Basket;

use Ilabs\Inpost_Pay\Lib\item\PromoCode;
use Ilabs\Inpost_Pay\Lib\omnibus\Coupon_Helper;
use WC_Cart;
use WC_Coupon;

class PromoCodeMapper {
	/**
	 * Maps promo codes from the WooCommerce cart
	 *
	 * @param WC_Cart $cart The WooCommerce cart
	 *
	 * @return array The mapped promo codes
	 */
	public function mapPromoCodes( WC_Cart $cart ): array {
		$array = [];
		if ( $cart ) {
			foreach ( $cart->get_applied_coupons() as $coupon ) {
				$array[] = $this->mapPromoCode( $coupon );
			}
		}

		return $array;
	}

	/**
	 * Maps a single promo code
	 *
	 * @param string $code The coupon code
	 *
	 * @return PromoCode The mapped promo code
	 */
	public function mapPromoCode( string $code ): PromoCode {
		$promoCode = new PromoCode();
		$coupon    = new WC_Coupon( $code );

		$promoCode
			->set_name( $coupon->get_description() )
			->set_promo_code_value( $coupon->get_code() );

		if ( ! $promoCode->get_name() ) {
			$promoCode->set_name( $promoCode->get_promo_code_value() );
		}

		$is_omnibus_coupon = Coupon_Helper::is_omnibus_coupon( $coupon );
		if ( $is_omnibus_coupon && inpost_pay()->omnibus_enabled() ) {
			inpost_pay()
				->get_omnibus()
				->get_woocommerce_logger( 'Omnibus' )
				->log_debug(
					sprintf( '[PromoCodeMapper] [mapPromoCode] [coupon %s is valid OMNIBUS coupon]',
						print_r( $promoCode->get_name(), true )
					)
				);
			$promoCode->set_regulation_type( 'OMNIBUS' );
		}

		return $promoCode;
	}
}
