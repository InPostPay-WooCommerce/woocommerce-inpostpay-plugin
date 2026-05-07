<?php

namespace Ilabs\Inpost_Pay\rest\basket;

use Ilabs\Inpost_Pay\Integration\Basket\Availability\AvailabilityProductFactory;
use Ilabs\Inpost_Pay\Integration\Basket\Availability\ProductIsEmptyException;
use Ilabs\Inpost_Pay\Lib\BasketIdentification;
use Ilabs\Inpost_Pay\Logger;
use Ilabs\Inpost_Pay\models\CartSessionService;
use Ilabs\Inpost_Pay\rest\Base;
use Ilabs\Inpost_Pay\rest\exception\BadRequestException;
use Ilabs\Inpost_Pay\rest\exception\BasketNotFoundException;
use Ilabs\Inpost_Pay\rest\exception\ProductNotAddedException;
use Ilabs\Inpost_Pay\rest\exception\ProductNotFoundException;
use Ilabs\Inpost_Pay\rest\exception\ProductOutOfStockException;
use Ilabs\Inpost_Pay\WooCommerce\WooCommerceBasket;
use Ilabs\Inpost_Pay\WooCommerce\WooCommerceBasketCache;
use WP_REST_Response;
use function Ilabs\Inpost_Pay\inpost_pay_container;
use function Ilabs\Inpost_Pay\inpost_pay;

class Product extends Base {

	private ?int $product_id = null;

	private $product = false;

	protected ?string $basket_id = null;

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
		$this->post['/inpost/v1/izi/basket/product/(?P<id>[0-9-]+)'] = function (
			$request
		) {
			$this->product_id = (int) $request['id'];
			try {
				$data = json_decode( $request->get_body(), false, 512, JSON_THROW_ON_ERROR );
			} catch ( \JsonException $e ) {
				return ( new BadRequestException() )->response();
			}

			if ( ! ( $this->product = wc_get_product( $this->product_id ) ) ) {
				return ( new ProductNotFoundException() )->response();
			}

			try {
				$availability = ( new AvailabilityProductFactory() )->create( $this->product );
			} catch ( ProductIsEmptyException $e ) {
				return ( new ProductNotFoundException() )->response();
			}

			if ( ! $availability->isInStock() ) {
				return ( new ProductOutOfStockException() )->response();
			}

			if ( property_exists( $data, 'basket_id' ) && ! empty( $data->basket_id ) ) {
				if ( ! $this->cart_session->get_object_by_id( $data->basket_id ) ) {
					return ( new BasketNotFoundException() )->response();
				}

				$this->basket_id = $data->basket_id;

				return $this->addToExistingBasket( $data );
			}

			return $this->addToNewBasket( $data );
		};
	}


	private function addToExistingBasket( $data ) {

		Logger::log( 'ADD_TO_EXISTING_BASKET' );
		WooCommerceBasketCache::restore( $data->basket_id, false );
		try {
			WC()->cart->add_to_cart( $this->product_id, 1 );
		} catch ( \Exception $e ) {
			return ( new ProductNotAddedException( $e->getMessage() ) )->response();
		}

		return $this->responseBasket();
	}

	private function addToNewBasket( $data ) {
		BasketIdentification::drop();
		BasketIdentification::generate();
		$this->basket_id = BasketIdentification::get();

		$this->cart_session->initiate_wc_cart();

		WC()->cart->empty_cart();
		try {
			WC()->cart->add_to_cart( $this->product_id, 1 );
		} catch ( \Exception $e ) {
			return ( new ProductNotAddedException( $e->getMessage() ) )->response();
		}

		$this->cart_session->store_current();

		return $this->responseBasket();
	}

	private function responseBasket() {
		$basket = WooCommerceBasket::getBasket( false );
		$this->cart_session->set_cart_cache_by_id( $this->basket_id, $basket->encode() );
		$this->cart_session->set_wc_cart_snapshot( $this->basket_id );
		$this->cart_session->set_cart_coupons_by_id( $this->basket_id, '1' );

		$response = new WP_REST_Response(
			$basket->toArray(),
			200,
			array()
		);

		$current_plugin_version = inpost_pay()->get_plugin_version();
		$response->header( 'inpay-plugin-version', $current_plugin_version );

		return rest_ensure_response( $response );
	}
}
