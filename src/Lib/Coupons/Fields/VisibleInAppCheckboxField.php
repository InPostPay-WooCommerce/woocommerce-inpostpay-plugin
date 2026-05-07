<?php

namespace Ilabs\Inpost_Pay\Lib\Coupons\Fields;

use Ilabs\Inpost_Pay\Lib\Coupons\Coupon;
use WC_Admin_Meta_Boxes;
use WC_Coupon;
use WP_Query;

class VisibleInAppCheckboxField implements FieldInterface {

	public function render( int $coupon_id ): void {
		$visible = get_post_meta( $coupon_id, Coupon::META_VISIBLE_IN_APP, true );

		woocommerce_wp_checkbox(
			array(
				'id'          => Coupon::META_VISIBLE_IN_APP,
				'name'        => Coupon::META_VISIBLE_IN_APP,
				'label'       => __( 'Make available in InPost Pay app', 'inpost-pay' ),
				'description' => __( 'Check this option if the coupon should be available in the InPost Pay app (max 5 active).', 'inpost-pay' ),
				'cbvalue'     => 'yes',
				'checked'     => ( $visible === 'yes' ),
				'desc_tip'    => false,
			)
		);
	}

	public function save( int $post_id ): void {
		$is_visible = isset( $_POST[ Coupon::META_VISIBLE_IN_APP ] ) && $_POST[ Coupon::META_VISIBLE_IN_APP ] === 'yes';

		if ( $is_visible ) {
			$errors = $this->validate( $post_id );
			if ( ! empty( $errors ) ) {
				foreach ( $errors as $error ) {
					WC_Admin_Meta_Boxes::add_error( $error );
				}

				delete_post_meta( $post_id, Coupon::META_VISIBLE_IN_APP );
				return;
			}

			update_post_meta( $post_id, Coupon::META_VISIBLE_IN_APP, 'yes' );
		} else {
			delete_post_meta( $post_id, Coupon::META_VISIBLE_IN_APP );
		}
	}

	private function validate( int $post_id ): array {
		$errors = array();
		$coupon = new WC_Coupon( $post_id );

		if ( trim( strip_tags( $coupon->get_description() ) ) === '' ) {
			$errors[] = __( 'Coupon must have a description to be visible in the InPost Pay app.', 'inpost-pay' );
		}

		if ( $coupon->get_date_expires() && $coupon->get_date_expires()->getTimestamp() < time() ) {
			$errors[] = __( 'Coupon is already expired and cannot be made available in the InPost Pay app.', 'inpost-pay' );
		}

		$query = new WP_Query(
			array(
				'post_type'      => 'shop_coupon',
				'post_status'    => 'publish',
				'posts_per_page' => -1,
				'post__not_in'   => array( $post_id ),
				'meta_query'     => array(
					array(
						'key'     => Coupon::META_VISIBLE_IN_APP,
						'value'   => 'yes',
						'compare' => '=',
					),
				),
			)
		);

		if ( $query->found_posts >= 5 ) {
			$errors[] = __( 'You can mark up to 5 coupons as available in the InPost Pay app.', 'inpost-pay' );
		}

		return $errors;
	}
}
