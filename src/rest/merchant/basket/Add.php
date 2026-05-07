<?php
/**
 * WC-AJAX endpoint for adding products to cart via InPost Pay.
 *
 * @package Ilabs\Inpost_Pay\rest\merchant\basket
 * @since   1.0.0
 */

declare( strict_types=1 );

namespace Ilabs\Inpost_Pay\rest\merchant\basket;

use Ilabs\Inpost_Pay\EntityLayer\Repository\BasketBindingRepository;
use Ilabs\Inpost_Pay\models\CartSessionService;
use Ilabs\Inpost_Pay\rest\Base;
use Ilabs\Inpost_Pay\rest\merchant\basket\Add\AddProductHandler;
use function Ilabs\Inpost_Pay\inpost_pay_container;

/**
 * Registers and dispatches the wc_ajax_inpost_add_product endpoint.
 *
 * Acts as a thin shell: hook registration and redirect-blocking filters
 * live here; all business logic is delegated to AddProductHandler.
 *
 * @since 1.0.0
 */
class Add extends Base {

	/**
	 * Full handler instance.
	 *
	 * @var AddProductHandler
	 */
	private AddProductHandler $handler;

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		$this->handler = new AddProductHandler(
			inpost_pay_container()->get( CartSessionService::SERVICE_KEY ),
			inpost_pay_container()->get( BasketBindingRepository::SERVICE_KEY )
		);
	}

	/**
	 * WC AJAX callback — delegates to AddProductHandler.
	 *
	 * @return void
	 *
	 * @throws \JsonException When JSON encoding fails.
	 * @since 1.0.0
	 */
	public function wc_ajax_inpost_add_product(): void {
		$this->handler->handle();
	}

	/**
	 * Register redirect-blocking filters for the InPost add-to-cart flow.
	 *
	 * Prevents WooCommerce from redirecting to the cart page after add,
	 * which would abort the AJAX response before it is sent.
	 *
	 * @return void
	 */
	public function maybe_block_add_to_cart_redirects(): void {
		$wc_ajax = sanitize_text_field( wp_unslash( $_GET['wc-ajax'] ?? '' ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended

		if ( 'wc_ajax_inpost_add_product' !== $wc_ajax ) {
			return;
		}

		add_filter(
			'woocommerce_add_to_cart_redirect',
			static function () {
				return false;
			},
			999
		);

		add_filter(
			'pre_option_woocommerce_cart_redirect_after_add',
			static function () {
				return 'no';
			},
			999
		);

		add_filter(
			'wp_redirect',
			static function ( $location ) {
				return $location;
			},
			1
		);

		add_filter(
			'wp_redirect',
			static function ( $location ) {
				$location_string = is_string( $location ) ? $location : '';

				if ( false !== strpos( $location_string, 'cart' ) ) {
					return false;
				}

				return $location;
			},
			999
		);
	}

	/**
	 * Register hooks.
	 *
	 * @return void
	 */
	protected function describe(): void {
		add_action( 'wp_loaded', array( $this, 'maybe_block_add_to_cart_redirects' ), 1 );
		add_action( 'wc_ajax_wc_ajax_inpost_add_product', array( $this, 'wc_ajax_inpost_add_product' ) );
	}
}
