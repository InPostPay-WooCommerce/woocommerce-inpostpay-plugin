<?php
/**
 * Admin Product Gallery Setting Hook.
 *
 * @package InpostPay
 * @subpackage Hooks/Admin
 */

namespace Ilabs\Inpost_Pay\hooks\admin;

use Ilabs\Inpost_Pay\hooks\Base;
use Ilabs\Inpost_Pay\Lib\Utils\HotProductUtils;

/**
 * Class AdminProductGallerySetting
 *
 * Handles product gallery setting for hot products.
 */
class AdminProductGallerySetting extends Base {
	/**
	 * Attach hooks.
	 *
	 * @return void
	 */
	public function attach_hook(): void {
		add_action(
			'woocommerce_product_options_advanced',
			array( $this, 'add_checkbox' )
		);

		add_action(
			'woocommerce_admin_process_product_object',
			array( $this, 'save_checkbox' )
		);
	}

	/**
	 * Add gallery override checkbox to product edit screen.
	 *
	 * Only renders if Hot Products list is empty.
	 *
	 * @return void
	 */
	public function add_checkbox(): void {
		$has_hot_products = HotProductUtils::has_hot_products();

		// Don't render checkbox at all if any HP are configured.
		if ( $has_hot_products ) {
			$this->render_locked_message();

			return;
		}

		$description = $this->get_description_text();

		woocommerce_wp_checkbox(
			array(
				'id'          => '_izi_gallery_inverse',
				'label'       => __( 'Override gallery setting for this Hot Product', 'inpost-pay' ),
				'description' => $description,
				'desc_tip'    => false,
			)
		);
	}

	/**
	 * Save gallery override setting.
	 *
	 * Validates that the setting can only be changed when Hot Products list is empty.
	 *
	 * @param \WC_Product $product Product object.
	 *
	 * @return void
	 */
	public function save_checkbox( \WC_Product $product ): void {
		// Prevent saving if any HP are configured.
		if ( HotProductUtils::has_hot_products() ) {
			return;
		}

		if (
			! isset( $_POST['woocommerce_meta_nonce'] ) ||
			! wp_verify_nonce(
				sanitize_text_field( wp_unslash( $_POST['woocommerce_meta_nonce'] ) ),
				'woocommerce_save_data'
			)
		) {
			return;
		}

		$raw_value  = isset( $_POST['_izi_gallery_inverse'] ) ? sanitize_text_field( wp_unslash( $_POST['_izi_gallery_inverse'] ) ) : null;
		$is_checked = filter_var( $raw_value, FILTER_VALIDATE_BOOLEAN );
		$product->update_meta_data( '_izi_gallery_inverse', $is_checked ? 'yes' : 'no' );
	}

	/**
	 * Render locked state message when HP are configured.
	 *
	 * @return void
	 */
	private function render_locked_message(): void {
		$hp_count = HotProductUtils::count_hot_products();

		$label = __( 'Override gallery setting for this Hot Product', 'inpost-pay' );

		$message = sprintf(
		/* translators: %d - number of configured Hot Products */
			__(
				'Number of configured Hot Products: %d. Remove all Hot Products to change gallery settings.',
				'inpost-pay'
			),
			$hp_count
		);

		echo '<p class="form-field _izi_gallery_inverse_field">';
		echo '<label>' . esc_html( $label ) . '</label>';
		echo '<span class="description" style="color: #d63638;">' . esc_html( $message ) . '</span>';
		echo '</p>';
	}

	/**
	 * Get description text for the checkbox field.
	 *
	 * @return string HTML description text.
	 */
	private function get_description_text(): string {
		$global_settings_url = admin_url( 'admin.php?page=inpost-pay#izi_main_image_only' );

		$global_main_image_only_raw = get_option( 'izi_main_image_only', false );
		$global_main_image_only     = filter_var( $global_main_image_only_raw, FILTER_VALIDATE_BOOLEAN );

		$current_global_label = $global_main_image_only
			? __( 'Global setting: Only main image is shown', 'inpost-pay' )
			: __( 'Global setting: Full gallery is shown', 'inpost-pay' );

		return sprintf(
			/* translators: %1$s: Current global setting label, %2$s: URL to global settings */
			__(
				'Enable this option to make this Hot Product behave opposite to the global setting in the InPost Pay app. For example: if globally only the main image is shown, this Hot Product will show the full gallery. If globally the full gallery is shown, this Hot Product will show only the main image. %1$s. To change the global configuration <a href="%2$s" target="_blank">click here</a>.',
				'inpost-pay'
			),
			$current_global_label,
			esc_url( $global_settings_url )
		);
	}
}
