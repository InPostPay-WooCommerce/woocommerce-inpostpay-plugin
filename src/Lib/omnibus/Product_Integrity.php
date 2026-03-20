<?php

namespace Ilabs\Inpost_Pay\Lib\omnibus;

use Isolated\Inpost_Pay\Ilabs\Ilabs_Plugin\Alerts;

class Product_Integrity {

	public function get_post_meta_key(): string {
		return inpost_pay()->get_omnibus()->add_slug_prefix( 'origin_product' );
	}

	public function handle_check_integrity( int $product_id ) {
		$meta_value = get_post_meta( $product_id, $this->get_post_meta_key(),
			true );
		if ( empty( $meta_value ) ) {
			$this->init_integrity_control( $product_id );

			return;
		}

		if ( (int) $meta_value !== $product_id ) {
			{

				$this->remove_omnibus_metadata( $product_id );
			}
		}
	}

	public function remove_omnibus_metadata( int $product_id ) {
		delete_post_meta( $product_id, $this->get_post_meta_key() );
		delete_post_meta( $product_id,
			( new Price_Post_Meta_Repository() )->get_post_meta_key() );
		delete_post_meta( $product_id,
			( new Lowest_Price_Cache_Post_Meta_Repository() )->get_post_meta_key() );
	}

	public function init_integrity_control( int $product_id ) {
		update_post_meta( $product_id, $this->get_post_meta_key(),
			$product_id );
	}

}
