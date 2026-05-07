<?php

namespace Ilabs\Inpost_Pay\Lib\Coupons\Fields;

use Ilabs\Inpost_Pay\Lib\Coupons\Coupon;
use WC_Admin_Meta_Boxes;

class PromotionUrlField implements FieldInterface {

	public function render( int $coupon_id ): void {
		$meta = get_post_meta( $coupon_id, Coupon::META_PROMOTION_URL, true );
		woocommerce_wp_text_input(
			array(
				'id'        => Coupon::META_PROMOTION_URL,
				'label'     => __( 'Promotion URL', 'inpost-pay' ),
				'value'     => $meta,
				'style'     => ( ! $this->validate( $meta ) ) ? 'border: 1px solid red' : '',
				'data_type' => 'url',
			)
		);
	}

	public function save( int $post_id ): void {
		if ( isset( $_POST[ Coupon::META_PROMOTION_URL ] ) ) {
			$value = sanitize_text_field( $_POST[ Coupon::META_PROMOTION_URL ] );
			if ( ! $this->validate( $value ) ) {
				WC_Admin_Meta_Boxes::add_error( __( 'Enter valid url address', 'inpost-pay' ) );
				return;
			}
			update_post_meta( $post_id, Coupon::META_PROMOTION_URL, $value );
		}
	}

	private function validate( string $value ): bool {
		return empty( $value ) || preg_match( '/^(https?:\/\/[a-zA-Z0-9.-]+\.[a-zA-Z]{2,})([\/?].*)?$/', $value );
	}
}
