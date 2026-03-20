<?php
/**
 * REST API handler for adding products to cart via InPost.
 *
 * This file contains the Add class which handles WC AJAX requests for adding
 * products to the WooCommerce cart through the InPost integration, including
 * basket binding and session management.
 *
 * @package Ilabs\Inpost_Pay\rest\merchant\basket
 * @since   1.0.0
 */

namespace Ilabs\Inpost_Pay\rest\merchant\basket;

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
use Ilabs\Inpost_Pay\rest\Base;
use Ilabs\Inpost_Pay\WooCommerce\WooCommerceInPostIzi;
use function Ilabs\Inpost_Pay\inpost_pay_container;

/**
 * REST API handler for adding products to cart via InPost.
 *
 * This class handles WC AJAX requests for adding products to the WooCommerce cart
 * through the InPost integration, including basket binding and session management.
 *
 * @since 1.0.0
 */
class Add extends Base {

	private CartSessionService $cart_session;
	private BasketBindingRepository $basket_binding_repository;

	/**
	 * Constructor.
	 *
	 * Retrieves CartSessionService and BasketBindingRepository from the container.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		/**
		 * Get from container DI.
		 *
		 * @var CartSessionService $cart_session
		 */
		$this->cart_session = inpost_pay_container()->get( CartSessionService::SERVICE_KEY );
		/**
		 * Get from container DI.
		 *
		 * @var BasketBindingRepository $basket_binding_repository
		 */
		$this->basket_binding_repository = inpost_pay_container()->get( BasketBindingRepository::SERVICE_KEY );
	}

	/**
	 * WC AJAX handler for adding a product to the cart via InPost.
	 *
	 * Handles adding a product to the cart and storing the basket in the session.
	 *
	 * @return void
	 * @throws \JsonException
	 * @since 1.0.0
	 */
	public function wc_ajax_inpost_add_product(): void {
		LSCacheHelper::no_cache();
		$this->cart_session->initiate_wc_cart();

		$cart_id                = BasketIdentification::get();
		$basket_binding_api_key = $this->cart_session->basket_binding_api_key( $cart_id );

		if ( ! $basket_binding_api_key ) {
			try {
				$remote                 = new Remote();
				$response               = $remote->basket_binding_put( wp_json_encode( array(), JSON_THROW_ON_ERROR ) );
				$basket_binding_api_key = $response->basket_binding_api_key ?? null;

				if ( ! $basket_binding_api_key ) {
					wp_send_json_error(
						array(
							'message' => 'Failed to generate basket_binding_api_key',
							'code'    => 500,
						)
					);
				}

				$this->cart_session->set_basket_binding_api_key( $cart_id, $basket_binding_api_key );
				CookieHelper::set_basket_binding_api_key( $basket_binding_api_key );

				Logger::log( "[Add] Generated new basket_binding_api_key for cart_id={$cart_id}" );
			} catch ( \Throwable $e ) {
				Logger::log( '[Add] Exception during basket_binding_api_key generation: ' . $e->getMessage() );
				wp_send_json_error(
					array(
						'message' => '[Add] Failed to generate basket_binding_api_key',
						'code'    => 500,
					)
				);
			}
		}

		$basket_binding_entity = $this->basket_binding_repository->find_by_api_key( $basket_binding_api_key );

		if ( null === $basket_binding_entity ) {
			wp_send_json_error(
				array(
					'message' => 'Basket binding entity not found in repository',
					'code'    => 500,
				)
			);
		}

		$object = $this->cart_session->get_object_by_id( $basket_binding_entity->get_basket_id() );

		if ( $object && null !== $object->get_order_id() ) {
			wp_send_json_error(
				array(
					'message' => 'Cant create basket',
					'code'    => 500,
				)
			);
		}

		BasketIdentification::set( $basket_binding_entity->get_basket_id() );

		$productId   = absint( $_POST['product_id'] ?? 0 );
		$variationId = absint( $_POST['variation_id'] ?? 0 );
		$product     = wc_get_product( $productId );

		if ( ! $product ) {
			wp_send_json_error(
				array(
					'error'   => true,
					'message' => 'Product not found',
				)
			);
		}

		if ( $product->get_type() === 'grouped' ) {
			$quantities = $_POST['quantity'] ?? array();

			foreach ( $quantities as $childId => $qty ) {
				$childId = absint( $childId );
				$qty     = absint( $qty );

				if ( $qty <= 0 ) {
					continue;
				}

				$alreadyInCart = false;
				foreach ( \WC()->cart->get_cart() as $item ) {
					if ( (int) $item['product_id'] === $childId ) {
						$alreadyInCart = true;
						break;
					}
				}

				if ( ! $alreadyInCart ) {
					$_POST['product_id'] = $childId;
					$_POST['quantity']   = $qty;
					do_action( 'wc_ajax_add_to_cart' );
				}
			}
		} else {
			$items = \WC()->cart->get_cart();
			$found = false;

			foreach ( $items as $item ) {
				if ( isset( $item['product_id'] ) && (int) $item['product_id'] === $productId ) {
					$found = true;
					if ( $item['variation_id'] > 0 && $item['variation_id'] != $variationId ) {
						$found = false;
					}
				}
			}

			if ( $variationId > 0 ) {
				$_POST['product_id'] = $variationId;
			}


			if ( ! $found ) {
				do_action( 'wc_ajax_add_to_cart' );
			}
		}

		Logger::log( 'STORE BASKET CACHE ON ADD TO CART PRODUCT' );

		$izi = WooCommerceInPostIzi::get_instance();
		InPostIzi::blockPut();
		$this->cart_session->store_current();
		// Store analytics identification on basket create
		( new Analytics() )->store_from_post();

		$data = $izi->getBasket()->encode();
		$this->cart_session->set_cart_cache_by_id( BasketIdentification::get(), $data );

		WC()->session->save_data();

		header( 'content-type: application/json' );

		wp_send_json(
			array(
				'basket_binding_api_key' => $basket_binding_api_key,
				'session_expiration'     => Woo_Commerce_Session_Helper::get_session_expiration_time(),
			)
		);
	}

	/**
	 * Register filters blocking add-to-cart redirects for InPost AJAX flow.
	 *
	 * @return void
	 */
	public function maybe_block_add_to_cart_redirects(): void {
		$wc_ajax = sanitize_text_field( wp_unslash( $_GET['wc-ajax'] ?? '' ) );

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

	protected function describe() {
		add_action( 'wp_loaded', array( $this, 'maybe_block_add_to_cart_redirects' ), 1 );
		add_action( 'wc_ajax_wc_ajax_inpost_add_product', array( $this, 'wc_ajax_inpost_add_product' ) );
	}
}
