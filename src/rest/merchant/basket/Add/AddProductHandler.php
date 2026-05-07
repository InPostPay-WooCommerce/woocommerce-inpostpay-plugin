<?php
/**
 * Handler for the wc_ajax_inpost_add_product endpoint.
 *
 * @package Ilabs\Inpost_Pay\rest\merchant\basket\Add
 */

declare( strict_types=1 );

namespace Ilabs\Inpost_Pay\rest\merchant\basket\Add;

use Ilabs\Inpost_Pay\EntityLayer\Repository\BasketBindingRepository;
use Ilabs\Inpost_Pay\Lib\Analytics\Analytics;
use Ilabs\Inpost_Pay\Lib\BasketIdentification;
use Ilabs\Inpost_Pay\Lib\helpers\CookieHelper;
use Ilabs\Inpost_Pay\Lib\helpers\LSCacheHelper;
use Ilabs\Inpost_Pay\Lib\helpers\Woo_Commerce_Session_Helper;
use Ilabs\Inpost_Pay\Lib\InPostIzi;
use Ilabs\Inpost_Pay\Lib\Remote;
use Ilabs\Inpost_Pay\Logger;
use Ilabs\Inpost_Pay\models\CartSessionService;
use Ilabs\Inpost_Pay\rest\exception\CartValidationException;
use Ilabs\Inpost_Pay\WooCommerce\WooCommerceInPostIzi;

/**
 * Orchestrates the full add-product-to-cart flow for InPost Pay.
 *
 * @since 2.0.8
 */
class AddProductHandler {

	/**
	 * Cart session service.
	 *
	 * @var CartSessionService
	 */
	private CartSessionService $cart_session;

	/**
	 * Basket binding repository.
	 *
	 * @var BasketBindingRepository
	 */
	private BasketBindingRepository $basket_binding_repository;

	/**
	 * Constructor.
	 *
	 * @param CartSessionService      $cart_session              Cart session service.
	 * @param BasketBindingRepository $basket_binding_repository Basket binding repository.
	 */
	public function __construct(
		CartSessionService $cart_session,
		BasketBindingRepository $basket_binding_repository
	) {
		$this->cart_session              = $cart_session;
		$this->basket_binding_repository = $basket_binding_repository;
	}

	/**
	 * Handle the WC AJAX add-to-cart request.
	 *
	 * @return void
	 */
	public function handle(): void {
		LSCacheHelper::no_cache();
		$this->cart_session->initiate_wc_cart();

		try {
			$basket_binding_api_key = $this->resolve_basket_binding_api_key();

			$this->setup_basket( $basket_binding_api_key );
			$this->add_product_to_cart();
			$this->persist_session_and_cache();

			wp_send_json(
				array(
					'basket_binding_api_key' => $basket_binding_api_key,
					'session_expiration'     => Woo_Commerce_Session_Helper::get_session_expiration_time(),
				)
			);
		} catch ( CartValidationException $e ) {
			$code = $e->getCode() > 0 ? $e->getCode() : 400;

			Logger::log( '[Add] Cart validation error: ' . $e->getMessage() );

			wp_send_json_error(
				array(
					'message' => $e->getMessage(),
					'code'    => $code,
				),
				$code
			);
		} catch ( \Throwable $e ) {
			Logger::log( '[Add] Unexpected error: ' . $e->getMessage() );

			wp_send_json_error(
				array(
					'message' => __( 'An unexpected error occurred.', 'inpost-pay' ),
					'code'    => 500,
				),
				500
			);
		}
	}

	/**
	 * Add a product to cart using the legacy flow semantics.
	 *
	 * @return void
	 *
	 * @throws CartValidationException When product is invalid or WooCommerce rejects add to cart.
	 */
	private function add_product_to_cart(): void {
		$product_id   = isset( $_POST['product_id'] ) ? absint( wp_unslash( $_POST['product_id'] ) ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Missing
		$variation_id = isset( $_POST['variation_id'] ) ? absint( wp_unslash( $_POST['variation_id'] ) ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Missing
		$product      = wc_get_product( $product_id );

		if ( ! $product ) {
			throw new CartValidationException( __( 'Product not found.', 'inpost-pay' ), 404 );
		}

		if ( 'grouped' === $product->get_type() ) {
			$this->add_grouped_product_to_cart();

			return;
		}

		$this->add_single_or_variable_product_to_cart( $product_id, $variation_id );
	}

	/**
	 * Add grouped product children to cart.
	 *
	 * @return void
	 *
	 * @throws CartValidationException When grouped quantity payload is invalid.
	 */
	private function add_grouped_product_to_cart(): void {
		$quantities = isset( $_POST['quantity'] ) && is_array( $_POST['quantity'] )
			? wp_unslash( $_POST['quantity'] )
			: array(); // phpcs:ignore WordPress.Security.NonceVerification.Missing

		if ( empty( $quantities ) ) {
			return;
		}

		foreach ( $quantities as $child_id => $qty ) {
			$child_id = absint( $child_id );
			$qty      = absint( $qty );

			if ( $qty <= 0 ) {
				continue;
			}

			if ( $this->is_product_already_in_cart( $child_id, 0 ) ) {
				continue;
			}

			$_POST['product_id'] = (string) $child_id;
			$_POST['quantity']   = (string) $qty;
			unset( $_POST['variation_id'] );

			$this->execute_wc_add_to_cart();
		}
	}

	/**
	 * Add a simple or variable product to cart.
	 *
	 * @param int $product_id   Product ID.
	 * @param int $variation_id Variation ID.
	 *
	 * @return void
	 *
	 * @throws CartValidationException When WooCommerce rejects add to cart.
	 */
	private function add_single_or_variable_product_to_cart( int $product_id, int $variation_id ): void {
		$found = $this->is_product_already_in_cart( $product_id, $variation_id );

		if ( $variation_id > 0 ) {
			$_POST['product_id'] = (string) $variation_id;
		}

		if ( $found ) {
			return;
		}

		$this->execute_wc_add_to_cart();
	}

	/**
	 * Check whether the requested product is already in cart.
	 *
	 * @param int $product_id   Product ID.
	 * @param int $variation_id Variation ID.
	 *
	 * @return bool
	 */
	private function is_product_already_in_cart( int $product_id, int $variation_id ): bool {
		foreach ( WC()->cart->get_cart() as $item ) {
			$item_product_id   = isset( $item['product_id'] ) ? (int) $item['product_id'] : 0;
			$item_variation_id = isset( $item['variation_id'] ) ? (int) $item['variation_id'] : 0;

			if ( $item_product_id !== $product_id ) {
				continue;
			}

			if ( $item_variation_id > 0 && $item_variation_id !== $variation_id ) {
				continue;
			}

			return true;
		}

		return false;
	}

	/**
	 * Run the native WooCommerce add-to-cart flow via do_action.
	 *
	 * @return void
	 *
	 * @throws CartValidationException When WooCommerce rejects the add.
	 */
	private function execute_wc_add_to_cart(): void {
		$error_notices_before = wc_get_notices( 'error' );
		$wc_output            = '';
		$initial_ob_level     = ob_get_level();

		ob_start();

		add_filter( 'wp_die_ajax_handler', array( $this, 'get_noop_wp_die_handler' ), 9999 );

		try {
			do_action( 'wc_ajax_add_to_cart' ); // phpcs:ignore WordPress.NamingConventions.ValidHookName.UseUnderscores
		} finally {
			remove_filter( 'wp_die_ajax_handler', array( $this, 'get_noop_wp_die_handler' ), 9999 );

			while ( ob_get_level() > $initial_ob_level ) {
				$chunk = ob_get_clean();

				if ( false === $chunk ) {
					break;
				}

				$wc_output = $chunk . $wc_output;
			}
		}

		$error_notices_after = wc_get_notices( 'error' );
		$new_error_notices   = array_slice( $error_notices_after, count( $error_notices_before ) );
		wc_clear_notices();

		$wc_error_message = $this->extract_wc_error_message_from_output( $wc_output );

		if ( null !== $wc_error_message ) {
			throw new CartValidationException( $wc_error_message, 400 );
		}

		if ( ! empty( $new_error_notices ) ) {
			$first  = $new_error_notices[0];
			$notice = is_array( $first ) ? ( $first['notice'] ?? '' ) : (string) $first;

			throw new CartValidationException( wp_strip_all_tags( $notice ), 400 );
		}
	}

	/**
	 * Extract WooCommerce error message from buffered AJAX output.
	 *
	 * @param string $wc_output Buffered WooCommerce output.
	 *
	 * @return string|null
	 */
	private function extract_wc_error_message_from_output( string $wc_output ): ?string {
		if ( '' === trim( $wc_output ) ) {
			return null;
		}

		$decoded = json_decode( $wc_output, true );

		if ( ! is_array( $decoded ) || empty( $decoded['error'] ) ) {
			return null;
		}

		if ( ! empty( $decoded['message'] ) && is_string( $decoded['message'] ) ) {
			return wp_strip_all_tags( $decoded['message'] );
		}

		if ( ! empty( $decoded['product_url'] ) ) {
			return sprintf(
			/* translators: %s: Product URL. */
				__( 'WooCommerce rejected add to cart. Product page: %s', 'inpost-pay' ),
				esc_url_raw( (string) $decoded['product_url'] )
			);
		}

		return __( 'Product could not be added to cart.', 'inpost-pay' );
	}

	/**
	 * Return a no-op callable for wp_die_ajax_handler.
	 *
	 * @param mixed $handler Existing handler.
	 *
	 * @return callable
	 */
	public function get_noop_wp_die_handler( $handler ): callable { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.Found
		return static function (): void {
		};
	}

	/**
	 * Resolve or generate the basket binding API key.
	 *
	 * @return string
	 *
	 * @throws CartValidationException When key cannot be resolved or generated.
	 */
	private function resolve_basket_binding_api_key(): string {
		$cart_id                = BasketIdentification::get();
		$basket_binding_api_key = $this->cart_session->basket_binding_api_key( $cart_id );

		if ( $basket_binding_api_key ) {
			return $basket_binding_api_key;
		}

		try {
			$remote   = new Remote();
			$response = $remote->basket_binding_put( wp_json_encode( array(), JSON_THROW_ON_ERROR ) );
			$api_key  = $response->basket_binding_api_key ?? null;
		} catch ( \Throwable $e ) {
			Logger::log( '[Add] Exception during basket_binding_api_key generation: ' . $e->getMessage() );

			throw new CartValidationException( __( 'Failed to generate basket binding API key.', 'inpost-pay' ), 500 );
		}

		if ( ! $api_key ) {
			throw new CartValidationException( __( 'Failed to generate basket binding API key.', 'inpost-pay' ), 500 );
		}

		$this->cart_session->set_basket_binding_api_key( $cart_id, $api_key );
		CookieHelper::set_basket_binding_api_key( $api_key );

		Logger::log( "[Add] Generated new basket_binding_api_key for cart_id={$cart_id}" );

		return $api_key;
	}

	/**
	 * Validate basket state and set the basket ID in session.
	 *
	 * @param string $basket_binding_api_key Basket binding API key.
	 *
	 * @return void
	 *
	 * @throws CartValidationException When basket entity not found or basket has an existing order.
	 */
	private function setup_basket( string $basket_binding_api_key ): void {
		$entity = $this->basket_binding_repository->find_by_api_key( $basket_binding_api_key );

		if ( null === $entity ) {
			throw new CartValidationException( __( 'Basket binding entity not found in repository.', 'inpost-pay' ), 500 );
		}

		$session_object = $this->cart_session->get_object_by_id( $entity->get_basket_id() );

		if ( $session_object && null !== $session_object->get_order_id() ) {
			throw new CartValidationException( __( 'Basket already has an order.', 'inpost-pay' ), 409 );
		}

		BasketIdentification::set( $entity->get_basket_id() );
	}

	/**
	 * Persist cart session, analytics, and basket cache after successful add.
	 *
	 * @return void
	 */
	private function persist_session_and_cache(): void {
		Logger::log( 'STORE BASKET CACHE ON ADD TO CART PRODUCT' );

		$izi = WooCommerceInPostIzi::get_instance();

		InPostIzi::blockPut();
		$this->cart_session->store_current();
		( new Analytics() )->store_from_post();

		$data = $izi->getBasket()->encode();

		$this->cart_session->set_cart_cache_by_id( BasketIdentification::get(), $data );

		WC()->session->save_data();
	}
}
