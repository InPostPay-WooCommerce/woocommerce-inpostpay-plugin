<?php

namespace Ilabs\Inpost_Pay\WooCommerce;

use Ilabs\Inpost_Pay\hooks\front\FrontBasketChange;
use Ilabs\Inpost_Pay\Lib\BasketIdentification;
use Ilabs\Inpost_Pay\models\CartSessionService;
use Ilabs\Inpost_Pay\rest\RestRequest;
use function Ilabs\Inpost_Pay\inpost_pay_container;

class WooCommerceBasketCache {

	private static ?CartSessionService $cart_session_service = null;

	private static function get_cart_service(): CartSessionService {
		if ( null === self::$cart_session_service ) {
			self::$cart_session_service = inpost_pay_container()->get( CartSessionService::SERVICE_KEY );
		}

		return self::$cart_session_service;
	}

	public static function store( $cart_id = null ): string {
		$cart = WC()->cart;

		$cleanCart = array_filter(
			$cart->cart_contents,
			static function ( $item ) {
				return is_array( $item ) && isset( $item['product_id'] );
			}
		);

		$storeCart['cart_contents']   = $cleanCart;
		$storeCart['applied_coupons'] = $cart->applied_coupons;
		$storeCart['fees_api']        = $cart->fees_api;

		return serialize( $storeCart );
	}


	public static function restore( $cartId, $calculate_totals = true ) {
		if ( RestRequest::isRequested() === false ) {
			return;
		}

		FrontBasketChange::$block_action_set = true;
		FrontBasketChange::$hook_is_start    = true;

		self::get_cart_service()->set_session_by_cart_id( $cartId );
		self::get_cart_service()->initiate_wc_cart();
		BasketIdentification::set( $cartId );

		if ( ! \WC()->cart->is_empty() ) {
			return;
		}

		$contents = unserialize( self::get_cart_service()->get_wc_cart_snapshot( $cartId ) );

		if ( ! is_array( $contents['cart_contents'] ) ) {
			return;
		}

		$cleanCart = array();
		foreach ( $contents['cart_contents'] as $key => $item ) {
			if ( is_array( $item ) && isset( $item['product_id'] ) ) {
				if ( ! empty( $item['tmcartepo'] ) ) {
					$item['tc_recalculate'] = true;
				}
				$cleanCart[ $key ] = $item;
			}
		}
		\WC()->cart->cart_contents = $cleanCart;

		if ( isset( $contents['applied_coupons'] ) ) {
			foreach ( $contents['applied_coupons'] as $coupon ) {
				\WC()->cart->apply_coupon( $coupon );
			}
		}

		if ( $calculate_totals ) {
			\WC()->cart->calculate_shipping();
			\WC()->cart->calculate_fees();
			\WC()->cart->calculate_totals();
		}
	}
}
