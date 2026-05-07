<?php

namespace Ilabs\Inpost_Pay\rest\basket;

use Ilabs\Inpost_Pay\Integration\Basket\BundledItem;
use Ilabs\Inpost_Pay\Integration\Basket\BundledItemFactory;
use Ilabs\Inpost_Pay\Integration\Basket\CustomBasketResponseMapper;
use Ilabs\Inpost_Pay\Lib\InPostIzi;
use Ilabs\Inpost_Pay\Logger;
use Ilabs\Inpost_Pay\models\CartSessionService;
use Ilabs\Inpost_Pay\models\Destination;
use Ilabs\Inpost_Pay\objects\CartProductId;
use Ilabs\Inpost_Pay\rest\Base;
use Ilabs\Inpost_Pay\Lib\helpers\WooProductHelper;
use Ilabs\Inpost_Pay\WooCommerce\Mappers\Basket\RelatedProductMapper;
use Ilabs\Inpost_Pay\WooCommerce\WooCommerceBasket;
use Ilabs\Inpost_Pay\WooCommerce\WooCommerceBasketCache;
use WC_Product;
use WP_REST_Response;
use function Ilabs\Inpost_Pay\inpost_pay_container;
use function Ilabs\Inpost_Pay\inpost_pay;

class Update extends Base {

	public const EVENT_TYPE_PROMO_CODES = 'PROMO_CODES';

	public const EVENT_TYPE_PRODUCTS_QUANTITY = 'PRODUCTS_QUANTITY';

	public const EVENT_TYPE_RELATED_PRODUCTS = 'RELATED_PRODUCTS';


	protected bool $hasCoupons  = false;
	protected bool $couponError = false;

	private CartSessionService $cart_session;

	public function __construct() {
		/**
		 * Get from container DI.
		 *
		 * @var CartSessionService $cart_session
		 */
		$this->cart_session = inpost_pay_container()->get( CartSessionService::SERVICE_KEY );
		$this->restricted   = true;
	}

	protected function describe() {
		$this->post['/inpost/v1/izi/basket/(?P<id>[a-zA-Z0-9-]+)/event'] = function (
			$request
		) {
			define( 'DOING_AJAX', true );
			$this->check_signature( $request );
			add_filter( 'woocommerce_persistent_cart_enabled', '__return_false', 99 );

			$id = $request->get_param( 'id' );
			Logger::log( 'Update basket for id: ' . $id . '' );
			$data = $request->get_body();
			InPostIzi::blockPut();
			$date = date( 'Y-m-d H:i:s' );
			Logger::basketEvent(
				$data,
				"Event dla koszyka {$id} z {$date} {$_SERVER['REQUEST_URI']}"
			);
			$data = json_decode( $data );

			WooCommerceBasketCache::restore( $id, false );
			$dest = Destination::get();

			switch ( $data->event_type ) {
				case self::EVENT_TYPE_PRODUCTS_QUANTITY:
					foreach ( $data->quantity_event_data as $eventData ) {
						$quantity = $eventData->quantity->quantity;
						$this->updateQuantity(
							$eventData->product_id,
							$quantity
						);
					}
					break;
				case self::EVENT_TYPE_PROMO_CODES:
					$couponList       = array();
					$this->hasCoupons = true;
					foreach ( $data->promo_codes_event_data as $eventData ) {
						$couponList[] = $eventData->promo_code_value;
					}
					foreach ( \WC()->cart->get_applied_coupons() as $code ) {
						if ( ! in_array( $code, $couponList ) ) {
							\WC()->cart->remove_coupon( $code );
						}
					}

					foreach ( $data->promo_codes_event_data as $eventData ) {
						$this->applyCode( $eventData->promo_code_value );
					}

					WooCommerceBasket::$couponError = $this->couponError;
					break;
				case self::EVENT_TYPE_RELATED_PRODUCTS:
					if ( empty( $data->related_products_event_data ) ) {
						break;
					}

					$product_ids = array_map(
						static fn( $item ) => (int) ( $item->product_id ?? 0 ),
						$data->related_products_event_data
					);
					$product_ids = array_filter( $product_ids );

					if ( empty( $product_ids ) ) {
						break;
					}

					/**
					 * Get from container DI.
					 *
					 * @var WooProductHelper $product_helper
					 */
					$product_helper = inpost_pay_container()->get( WooProductHelper::SERVICE_KEY );
					$products       = $product_helper->load_products_safe( $product_ids );

					foreach ( $products as $product_id => $product ) {
						if ( ! $product ) {
							continue;
						}

						if ( ! $this->checkAvailability( $product ) ) {
							continue;
						}

						\WC()->cart->add_to_cart( $product_id );
					}
					break;
			}

			add_filter(
				'option_woocommerce_flexible_shipping_single_2_settings',
				static function ( $value ) {
					static $cached = null;
					if ( $cached !== null ) {
						return $cached;
					}
					$cached = $value;

					return $value;
				}
			);

			$cart                = WC()->cart;
			$cart->cart_contents = apply_filters( 'woocommerce_cart_contents_changed', $cart->cart_contents );
			$responseData        = null;

			$should_recalculate_full = in_array(
				$data->event_type,
				array(
					self::EVENT_TYPE_PROMO_CODES,
					self::EVENT_TYPE_RELATED_PRODUCTS,
				),
				true
			);

			if ( $should_recalculate_full ) {
				$cart->calculate_shipping();
				$cart->calculate_fees();
			}
			$cart->calculate_totals();

			// When InPost empties the cart, reset the hash so the next shop-side
			// add-to-cart always triggers a PUT (handles qty=1 re-add edge case).
			if ( \WC()->session && \WC()->cart->is_empty() ) {
				\WC()->session->set( 'inpost_cart_hash', null );
			}

			$early_response_enabled = get_option( 'izi_early_update_response_enabled', false );

			if ( ! $early_response_enabled ) {
				if ( get_option( 'izi_custom_basket_response_enabled', false ) ) {
					$basketObject    = WooCommerceBasket::getBasket( true, $id );
					$relatedProducts = RelatedProductMapper::mapCustomRelatedProducts( $cart->get_cart() );
					$basketObject->set_related_products( $relatedProducts );

					$mapper       = new CustomBasketResponseMapper();
					$responseData = $mapper->map( $basketObject );

					if ( function_exists( 'wc_get_logger' ) ) {
						\wc_get_logger()->debug( print_r( '[izi_custom_basket_response_enabled = true]', true ), array( 'source' => 'inpost-pay-basket-update' ) );
						\wc_get_logger()->debug( print_r( 'NEW UPDATE BASKET response', true ), array( 'source' => 'inpost-pay-basket-update' ) );
						\wc_get_logger()->debug( print_r( $responseData, true ), array( 'source' => 'inpost-pay-basket-update' ) );
					}

					add_action(
						'shutdown',
						function () use ( $id, $basketObject, $mapper ) {
							$basketArray = $mapper->map( $basketObject );
							$basket      = json_encode( $basketArray );

							$this->cart_session->set_cart_cache_by_id( $id, $basket );
							$this->cart_session->set_wc_cart_snapshot( $id );
							$this->cart_session->set_cart_coupons_by_id( $id, '1' );
							Logger::rawData( $basket, 'BASKET FROM UPDATE' );
							InPostIzi::getStorage()->sessionClose();

							if ( ! get_option( 'izi_custom_response_enabled', true ) ) {
								die( mb_convert_encoding( $basket, 'UTF-8' ) );
							}

							wp_send_json( json_decode( $basket, true ) );
						},
						PHP_INT_MAX - 1
					);

				} else {
					if ( function_exists( 'wc_get_logger' ) ) {
						\wc_get_logger()->debug( print_r( '[izi_custom_basket_response_enabled = false]', true ), array( 'source' => 'inpost-pay-basket-update' ) );
						\wc_get_logger()->debug( print_r( 'UPDATE BASKET response', true ), array( 'source' => 'inpost-pay-basket-update' ) );
						\wc_get_logger()->debug( print_r( $responseData, true ), array( 'source' => 'inpost-pay-basket-update' ) );
					}

					add_action(
						'shutdown',
						function () use ( $id ) {
							$basket = WooCommerceBasket::getBasket( false, $id )->encode();
							$this->cart_session->set_cart_cache_by_id( $id, $basket );
							$this->cart_session->set_wc_cart_snapshot( $id );
							$this->cart_session->set_cart_coupons_by_id( $id, '1' );
							Logger::rawData( $basket, 'BASKET FROM UPDATE' );
							InPostIzi::getStorage()->sessionClose();

							if ( ! get_option( 'izi_custom_response_enabled', true ) ) {
								die( mb_convert_encoding( $basket, 'UTF-8' ) );
							}

							wp_send_json( json_decode( $basket, true ) );
						},
						PHP_INT_MAX - 1
					);
				}
			}

			if ( $early_response_enabled ) {
				$basket = WooCommerceBasket::getBasket( false, $id )->encode();
				$this->cart_session->set_cart_cache_by_id( $id, $basket );
				$this->cart_session->set_wc_cart_snapshot( $id );
				$this->cart_session->set_cart_coupons_by_id( $id, '1' );
				Logger::rawData( $basket, 'BASKET FROM UPDATE' );
				InPostIzi::getStorage()->sessionClose();

				if ( ! get_option( 'izi_custom_response_enabled', true ) ) {
					die( mb_convert_encoding( $basket, 'UTF-8' ) );
				}

				$responseData = json_decode( $basket, true );
			}

			$response = new WP_REST_Response(
				$responseData,
				200,
				array()
			);

			$current_plugin_version = inpost_pay()->get_plugin_version();
			$response->header( 'inpay-plugin-version', $current_plugin_version );

			remove_filter( 'woocommerce_persistent_cart_enabled', '__return_false', 99 );

			// When InPost empties the cart, WooCommerce stores cart=null in the session.
			// On the next page load, get_cart_from_session() sees null and merges from
			// persistent cart (user_meta), which still holds the old items because our
			// woocommerce_persistent_cart_enabled=false filter blocked persistent_cart_update()
			// during this request. Clear user_meta now so the page reload shows an empty cart.
			if ( get_current_user_id() && \WC()->cart && \WC()->cart->is_empty() ) {
				delete_user_meta( get_current_user_id(), '_woocommerce_persistent_cart_' . get_current_blog_id() );
			}

			return rest_ensure_response( $response );
		};
	}

	protected function updateQuantity( $productId, $quantity ) {
		$items           = \WC()->cart->get_cart();
		$cart_product_id = new CartProductId( $productId );

		if ( $cart_product_id->hasKey() ) {
			$cart_item = $items[ $cart_product_id->getKey() ];
			if ( ! is_array( $cart_item ) ) {
				return;
			}

			$bundleItem = BundledItemFactory::create( $cart_item, \WC()->cart );

			if ( $quantity === 0 ) {

				if ( $bundleItem instanceof BundledItem ) {
					$bundleItem->removeParentWithBundledItems();

					return;
				}

				/*
				if ( ! $cartItemFilter->canAddCartItem( $cart_item ) ) {
					$wooco_parent_id              = $cart_item['wooco_parent_id'];
					foreach ( $items as $cart_item_key_2 => $item_2 ) {
						if ( ( $item_2['product_id'] ) == $wooco_parent_id ) {
							\WC()->cart->remove_cart_item( $cart_item_key_2 );

							return;
						}
					}
				}*/

				\WC()->cart->remove_cart_item( $cart_product_id->getKey() );
			} else {

				if ( $bundleItem instanceof BundledItem ) {
					return;
				}

				\WC()->cart->set_quantity( $cart_product_id->getKey(), $quantity );
			}

			return;
		}

		foreach ( $items as $cart_item_key => $item ) {
			if ( ! is_array( $item ) ) {
				continue;
			}

			$bundleItem = BundledItemFactory::create( $item, \WC()->cart );
			if ( $bundleItem instanceof BundledItem ) {
				$bundleItem->removeParentWithBundledItems();

				return;
			}
			/*
			if ( ! $cartItemFilter->canAddCartItem( $item ) ) {
				$wooco_parent_id              = $item['wooco_parent_id'];
				foreach ( $items as $cart_item_key_2 => $item_2 ) {
					if ( ( $item_2['product_id'] ) == $wooco_parent_id ) {
						\WC()->cart->remove_cart_item( $cart_item_key_2 );

						return;
					}
				}
			}*/

			if ( isset( $item['product_id'] ) ) {
				if ( ( $item['product_id'] ) == $cart_product_id->getId() || $item['variation_id'] == $cart_product_id->getId() ) {
					if ( $quantity === 0 ) {
						\WC()->cart->remove_cart_item( $cart_item_key );
					} else {
						\WC()->cart->set_quantity( $cart_item_key, $quantity );
					}
				}
			}
		}
	}

	protected function applyCode( $code ) {
		if ( in_array( $code, \WC()->cart->get_applied_coupons() ) ) {
			return;
		}

		$couponObject = new \WC_Coupon( $code );

		if ( ! $couponObject ) {
			$this->couponError = true;

			return;
		}
		$code = $couponObject->get_code();

		WooCommerceBasket::$hasCoupons = $this->hasCoupons;
		if ( ! \WC()->cart->has_discount( $code ) ) {
			if ( ! \WC()->cart->apply_coupon( $code ) ) {
				$this->couponError = true;
			}
			if ( ! \WC()->cart->has_discount( $code ) ) {
				$this->couponError = true;
			} else {
				wc_clear_notices();
				wc_add_notice(
					__(
						'Coupon added using InPost Pay',
						'woocommerce'
					),
					'success'
				);
			}
		} else {
			$this->couponError = true;
		}

		return $code;
	}

	protected function checkAvailability( WC_Product $product ): bool {
		return $product->is_purchasable() && $product->is_in_stock() && $product->is_visible();
	}
}
