<?php
/**
 * Admin Notices Hook.
 *
 * @package InpostPay
 * @subpackage Hooks/Admin
 */

namespace Ilabs\Inpost_Pay\hooks\admin;

use Ilabs\Inpost_Pay\hooks\Base;
use Ilabs\Inpost_Pay\Lib\config\product\ExpiredHotProductsConfig;
use Ilabs\Inpost_Pay\Lib\config\product\HotProductsConfig;
use Ilabs\Inpost_Pay\Lib\config\product\InactiveHotProductsConfig;
use Ilabs\Inpost_Pay\Lib\Product\HotProduct;
use Ilabs\Inpost_Pay\Logger;

/**
 * Class AdminNotices
 *
 * Handles admin notices for hot products.
 */
class AdminNotices extends Base {

	/**
	 * Attach hooks.
	 *
	 * @return void
	 */
	public function attach_hook() {
		add_action( 'admin_notices', array( $this, 'inactive_product' ) );
		add_action( 'admin_notices', array( $this, 'inactive_products' ) );
		add_action( 'admin_notices', array( $this, 'expired_products' ) );
		add_action( 'admin_notices', array( $this, 'remove_product_based_on_ean' ) );
	}

	/**
	 * Show inactive product notice.
	 *
	 * @return void
	 */
	public function inactive_product(): void {
		global $post;

		if ( ! $post || get_post_type( $post->ID ) !== 'product' ) {
			return;
		}

		$product_id = get_transient( 'inpost_pay_product_update_hot_inactive_' . get_current_user_id() );

		if ( $product_id && (int) $product_id === (int) $post->ID ) {
			echo '<div class="notice notice-warning is-dismissible">
                <h3>' . esc_html( __( 'Hot product is inactive', 'inpost-pay' ) ) . '</h3>
              </div>';
			delete_transient( 'inpost_pay_product_update_hot_inactive_' . get_current_user_id() );
		}
	}

	/**
	 * Show inactive products notice.
	 *
	 * @return void
	 */
	public function inactive_products(): void {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( ! isset( $_GET['page'] ) || sanitize_text_field( wp_unslash( $_GET['page'] ) ) !== 'inpost-pay-hot-products' ) {
			return;
		}

		$product_ids = ( new InactiveHotProductsConfig() )->get();
		if ( ! $product_ids ) {
			return;
		}

		echo '<div class="notice notice-info">';
		echo '<h3>' . esc_html( __( 'Inactive hot products in InPost Pay', 'inpost-pay' ) ) . '</h3>';
		echo '<ul>';

		foreach ( $product_ids as $product_id ) {
			$product = wc_get_product( $product_id );
			if ( $product ) {
				$edit_url = get_edit_post_link( $product_id );
				echo '<li><a href="' . esc_url( $edit_url ) . '" target="_blank">' . esc_html( $product->get_name() ) . '</a></li>';
			}
		}

		echo '</ul>';
		echo '</div>';
	}

	/**
	 * Show expired products notice.
	 *
	 * @return void
	 */
	public function expired_products(): void {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( ! isset( $_GET['page'] ) || sanitize_text_field( wp_unslash( $_GET['page'] ) ) !== 'inpost-pay-hot-products' ) {
			return;
		}

		$is_expired_updated = get_transient( 'inpost_pay_product_expired_check' );
		if ( ! $is_expired_updated ) {
			$expired_ids = ( new HotProduct() )->getExpiredProductIds();
			( new ExpiredHotProductsConfig() )->update( $expired_ids );
		}

		$expired_product_ids = ( new ExpiredHotProductsConfig() )->get();

		if ( ! $expired_product_ids ) {
			return;
		}

		echo '<div class="notice notice-warning">';
		echo '<h3>' . esc_html( __( 'Expired hot products in InPost Pay', 'inpost-pay' ) ) . '</h3>';
		echo '<ul>';

		foreach ( $expired_product_ids as $product_id ) {
			$product = wc_get_product( $product_id );
			if ( $product ) {
				echo '<li>' . esc_html( $product->get_name() ) . '</li>';
			}
		}

		echo '</ul>';
		echo '</div>';
	}

	/**
	 * Show product removed based on EAN notice.
	 *
	 * @return void
	 */
	public function remove_product_based_on_ean(): void {
		$notice = get_transient( 'inpost_pay_hot_product_removed_' . get_current_user_id() );

		if ( ! $notice || ! is_array( $notice ) ) {
			return;
		}

		$product = wc_get_product( $notice['product_id'] ?? 0 );
		if ( ! $product ) {
			return;
		}

		$reason = $notice['reason'] ?? __( 'Product removed from Hot Products.', 'inpost-pay' );

		echo '<div class="notice notice-warning is-dismissible">';
		echo '<h3>' . esc_html( $reason ) . '</h3>';
		echo '<ul>';
		echo '<li>' . esc_html( $product->get_name() ) . '</li>';
		echo '</ul>';
		echo '</div>';

		delete_transient( 'inpost_pay_hot_product_removed_' . get_current_user_id() );
	}
}
