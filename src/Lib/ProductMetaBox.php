<?php

namespace Ilabs\Inpost_Pay\Lib;


use Ilabs\Inpost_Pay\Lib\Product\CustomMeta\AvailableForProduct;
use Ilabs\Inpost_Pay\Lib\form\exception\NotAllowedConfigOptionException;
use Ilabs\Inpost_Pay\Lib\form\exception\RequiredConfigOptionException;
use Ilabs\Inpost_Pay\Logger;

class ProductMetaBox {
	public const NAME = 'inpost_pay_product_meta_box';

	public const AVAILABLE_META = [
		AvailableForProduct::class,

	];


	public function register(): void {
		add_filter( 'woocommerce_product_data_tabs', [ $this, 'filter_woocommerce_product_data_tabs' ], 10, 1 );
		add_action( 'woocommerce_product_data_panels', [ $this, 'action_woocommerce_product_data_panels' ], 10, 0 );
		add_action( 'woocommerce_admin_process_product_object', [ $this, 'save' ], 10, 1 );
	}

	/**
	 * Add custom product setting tab.
	 */
	public function filter_woocommerce_product_data_tabs( $default_tabs ) {
		$default_tabs[self::NAME] = [
			'label'    => __( 'InPost Pay', 'inpost-pay' ),
			'target'   => self::NAME,
			'priority' => 50,
			'class'    => [],
		];

		return $default_tabs;
	}


	/**
	 * Contents custom product setting tab.
	 */
	public function action_woocommerce_product_data_panels(): void {
		global $post;
		echo '<div id="'.self::NAME.'" class="panel woocommerce_options_panel">';


		foreach ( self::AVAILABLE_META as $meta ) {
			try {
				$field = $meta::get_form_field( $post );
				echo "<p class='form-field'>";
				$field->print_label();
				$field->print_field();
				echo '</p>';
			} catch ( RequiredConfigOptionException|NotAllowedConfigOptionException $e ) {
				Logger::log( $e->getMessage() );
			}
		}

		echo '</div>';
	}


	public function save( $post_id ) {
		// Check if this is an autosave
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return $post_id;
		}

		// Check user permissions
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return $post_id;
		}

		foreach ( self::AVAILABLE_META as $meta ) {
			// Sanitize user input
			if ( $meta::validate( $post_id ) ) {
				$new_value = sanitize_text_field( $_POST[$meta::get_slug()] );

				// Update the meta field in the database
				update_post_meta( $post_id, $meta::get_slug(), $new_value );
			} else {
				wc_add_notice( __( $meta::get_validation_error(), 'inpost-pay' ), 'error' );
				return;
			}

		}


	}
}
