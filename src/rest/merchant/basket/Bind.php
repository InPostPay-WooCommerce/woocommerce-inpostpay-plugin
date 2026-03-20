<?php
// basketBindingGet

namespace Ilabs\Inpost_Pay\rest\merchant\basket;

use Ilabs\Inpost_Pay\EntityLayer\Repository\BasketBindingRepository;
use Ilabs\Inpost_Pay\Lib\BasketIdentification;
use Ilabs\Inpost_Pay\Lib\BindingProvider;
use Ilabs\Inpost_Pay\Lib\helpers\LSCacheHelper;
use Ilabs\Inpost_Pay\Lib\helpers\Woo_Commerce_Session_Helper;
use Ilabs\Inpost_Pay\Lib\InPostIzi;
use Ilabs\Inpost_Pay\models\CartSessionService;
use Ilabs\Inpost_Pay\rest\Base;
use function Ilabs\Inpost_Pay\inpost_pay_container;

class Bind extends Base {

	private CartSessionService $cart_session;

	private BasketBindingRepository $basket_binding_repository;

	/**
	 * Constructor.
	 *
	 * Retrieves CartSessionService from the container.
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
	 * Binds the current cart session to the given basket ID.
	 *
	 * This API call is used to bind the InPost Pay basket to the current
	 * WooCommerce cart session. It is used to synchronize the InPost Pay
	 * basket with the WooCommerce cart session.
	 *
	 * @return void
	 * @throws \JsonException
	 */
	public function inpost_pay_bind(): void {
		LSCacheHelper::no_cache();

		$cart_id                = BasketIdentification::get();
		$basket_binding_api_key = $this->cart_session->basket_binding_api_key( $cart_id );

		if ( ! $basket_binding_api_key ) {
			wp_send_json_error(
				array(
					'message' => '[Bind] Basket binding api key not found',
					'code'    => 500,
				)
			);
		}

		$basket_binding_entity = $this->basket_binding_repository->find_by_api_key( $basket_binding_api_key );

		if ( null === $basket_binding_entity ) {
			wp_send_json_error(
				array(
					'message' => '[Bind] Basket binding entity not found in repository',
					'code'    => 500,
				)
			);
		}

		$object = $this->cart_session->get_object_by_id( $basket_binding_entity->get_basket_id() );

		if ( $object && null !== $object->get_order_id() ) {
			wp_send_json_error(
				array(
					'message' => '[Bind] Cant create basket',
					'code'    => 500,
				)
			);
		}

		$this->cart_session->initiate_wc_cart();
		$basket_id = $basket_binding_entity->get_basket_id();
		BasketIdentification::set( $basket_id );
		$this->cart_session->store_current();

		InPostIzi::$inpostIziBasketId = $basket_id;

		BindingProvider::setBinding();

		wp_send_json_success(
			array(
				'basket_binding_api_key' => $basket_binding_api_key,
				'session_expiration'     => Woo_Commerce_Session_Helper::get_session_expiration_time(),
			),
			200
		);
	}

	protected function describe(): void {
		add_action( 'wc_ajax_inpost_pay_bind', array( $this, 'inpost_pay_bind' ) );
	}
}
