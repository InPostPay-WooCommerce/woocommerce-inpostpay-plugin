<?php
/**
 * Front Cart Count Hook.
 *
 * @package InpostPay
 * @subpackage Hooks/Front
 */

namespace Ilabs\Inpost_Pay\hooks\front;

/**
 * Class FrontCartCount
 *
 * Handles cart count functionality on the frontend.
 */
class FrontCartCount extends FrontBase {
	/**
	 * Attach hooks.
	 *
	 * @return void
	 */
	public function attach_hook() {
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_cart_count_script' ) );
		add_action( 'wp_ajax_update_cart_count', array( $this, 'update_cart_count_function' ) );
		add_action( 'wp_ajax_nopriv_update_cart_count', array( $this, 'update_cart_count_function' ) );
	}

	/**
	 * Update cart count function.
	 *
	 * @return void
	 */
	public function update_cart_count_function() {
		echo esc_html( $this->get_cart_count() );
		wp_die();
	}

	/**
	 * Enqueue cart count script.
	 *
	 * @return void
	 */
	public function enqueue_cart_count_script() {
		wp_localize_script(
			'izi-cart-count',
			'iziCartCountData',
			array(
				'ajax_url'   => admin_url( 'admin-ajax.php' ),
				'cart_count' => $this->get_cart_count(),
			)
		);
	}

	/**
	 * Get cart count.
	 *
	 * @return int Cart count.
	 */
	public function get_cart_count(): int {
		if ( ! function_exists( 'WC' ) ) {
			return 0;
		}

		// Guard: Check if cart exists without triggering initialization.
		$wc = WC();
		if ( ! $wc || ! $wc->cart || ! $wc->cart instanceof \WC_Cart ) {
			return 0;
		}

		return (int) $wc->cart->get_cart_contents_count();
	}
}
