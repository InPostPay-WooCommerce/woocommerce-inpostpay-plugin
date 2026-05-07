<?php
/**
 * CartSessionService class.
 *
 * Handles WooCommerce cart session persistence and restoration.
 *
 * @package Ilabs\Inpost_Pay\Models
 */

declare( strict_types=1 );

namespace Ilabs\Inpost_Pay\Models;

use Couchbase\LookupGetFullSpec;
use Ilabs\Inpost_Pay\Container\ServiceContainer;
use Ilabs\Inpost_Pay\EntityLayer\Entity\CartSessionEntity;
use Ilabs\Inpost_Pay\EntityLayer\Repository\BasketBindingRepository;
use Ilabs\Inpost_Pay\EntityLayer\Repository\CartSessionRepository;
use Ilabs\Inpost_Pay\Lib\BasketIdentification;
use Ilabs\Inpost_Pay\Lib\helpers\CookieHelper;
use Ilabs\Inpost_Pay\Lib\helpers\Woo_Commerce_Session_Helper;
use Ilabs\Inpost_Pay\Lib\InPostIzi;
use Ilabs\Inpost_Pay\Lib\interfaces\CartSessionInterface;
use Ilabs\Inpost_Pay\Logger;
use Ilabs\Inpost_Pay\LoggerTrace;
use Ilabs\Inpost_Pay\WooCommerce\WooCommerceBasketCache;
use RuntimeException;
use WC_Session_Handler;
use function WC;

/**
 * Service handling WooCommerce cart session logic and persistence.
 */
class CartSessionService implements CartSessionInterface {

	/**
	 * Service key for DI container.
	 *
	 * @var string
	 */
	public const SERVICE_KEY = 'service.cart_session_service';

	/**
	 * Cart session repository instance.
	 *
	 * @var CartSessionRepository
	 */
	private CartSessionRepository $repository;

	/**
	 * Basket binding repository instance.
	 *
	 * @var BasketBindingRepository
	 */
	private BasketBindingRepository $binding_repository;

	/**
	 * Local entity cache for faster access within request.
	 *
	 * @var array<string, CartSessionEntity>
	 */
	private array $entity_cache = array();

	/**
	 * Constructor.
	 *
	 * @param ServiceContainer $container Dependency injection container.
	 */
	public function __construct( ServiceContainer $container ) {
		$this->repository         = $container->get( CartSessionRepository::SERVICE_KEY );
		$this->binding_repository = $container->get( BasketBindingRepository::SERVICE_KEY );
	}

	/**
	 * Clear redirect URL for given cart.
	 *
	 * @param string $cart_id WooCommerce cart identifier.
	 *
	 * @return void
	 */
	public function clear_redirect_url_by_cart_id( string $cart_id ): void {
		$entity = $this->get_entity_cached( $cart_id );

		if ( ! $entity || ! $entity->get_id() ) {
			return;
		}

		$entity->set_redirect_url( null );
		$this->repository->save( $entity );
		$this->entity_cache[ $cart_id ] = $entity;
	}   /**
		 * Retrieve entity from local cache or repository.
		 *
		 * @param string $cart_id WooCommerce cart identifier.
		 *
		 * @return CartSessionEntity|null
		 */
	private function get_entity_cached( string $cart_id ): ?CartSessionEntity {
		if ( isset( $this->entity_cache[ $cart_id ] ) ) {
			return $this->entity_cache[ $cart_id ];
		}

		$entity = $this->repository->find_one_by( array( 'cart_id' => $cart_id ) );

		// Logger::log('GET ENTITY FROM DB: ' . $cart_id . '');

		if ( ! $entity instanceof CartSessionEntity ) {
			return null;
		}

		$this->entity_cache[ $cart_id ] = $entity;

		return $entity;
	}

	/**
	 * Get cart session entity by basket binding API key.
	 *
	 * @param string $basket_binding_api_key Basket binding API key.
	 *
	 * @return CartSessionEntity|null
	 */
	public function get_session_by_basket_binding_api_key( string $basket_binding_api_key ): ?CartSessionEntity {
		$binding_entity = $this->binding_repository->find_by_api_key( $basket_binding_api_key );

		if ( ! $binding_entity || ! $binding_entity->get_basket_id() ) {
			return null;
		}

		return $this->get_entity_cached( $binding_entity->get_basket_id() );
	}

	/**
	 * Store the current WooCommerce session in database.
	 *
	 * @return void
	 */
	public function store_current(): void {
		if ( defined( 'DOING_CRON' ) && DOING_CRON ) {
			return;
		}

		$cart_id = BasketIdentification::get();
		$entity  = $this->get_entity_cached( $cart_id );

		if ( ! $entity ) {
			Logger::log( 'ENTITY CREATED' );
			$entity = new CartSessionEntity();
		}

		$entity->set_session_id( json_encode( CookieHelper::getCookies(), JSON_THROW_ON_ERROR ) );
		$entity->set_wc_cart_session( WC()->session->get_customer_id() );
		$entity->set_session_expiry( Woo_Commerce_Session_Helper::get_session_expiration_time() );
		$entity->set_cart_id( $cart_id );

		if ( strlen( (string) $entity->get_session_id() ) < 3 ) {
			return;
		}

		$this->repository->save( $entity );
		$this->entity_cache[ $cart_id ] = $entity;

		Logger::log(
			sprintf(
				'[DIAG][P3] store_current basket_id=%s expiry=%s (%s)',
				$cart_id,
				$entity->get_session_expiry(),
				gmdate( 'Y-m-d H:i:s', $entity->get_session_expiry() )
			)
		);
	}

	/**
	 * Restore WooCommerce session from stored cart ID.
	 *
	 * @param string $cart_id WooCommerce cart identifier.
	 *
	 * @return void
	 */
	public function set_session_by_cart_id( string $cart_id ): void {
		$entity = $this->get_entity_cached( $cart_id );

		if ( ! $entity || ! $entity->get_id() ) {
			Logger::log( "[CartSessionService] Entity not found for cart_id: {$cart_id}" );
			$this->initiate_wc_cart();
			$this->store_current();

			$entity = $this->get_entity_cached( $cart_id );

			if ( ! $entity || ! $entity->get_id() ) {
				Logger::log( "[CartSessionService] Failed to create entity for cart_id: {$cart_id}" );
				Logger::log( LoggerTrace::compact_backtrace() );

				die( wp_json_encode( array() ) );
			}

			Logger::log( "[CartSessionService] New entity created id={$entity->get_id()} for cart_id={$cart_id}" );
		}

		if ( null === WC()->session ) {
			$this->initiate_wc_cart();
		}

		$stored_wc_session = $this->get_wc_cart_session( $cart_id );

		if ( is_user_logged_in() && $stored_wc_session === (string) get_current_user_id() ) {
			Logger::log( "[CartSessionService] Skipping destructive restore: cart {$cart_id} belongs to current user " . get_current_user_id() );
			return;
		}

		if ( $stored_wc_session !== WC()->session->get_customer_id() ) {
			Logger::log( 'Restore session for ' . $cart_id );
			InPostIzi::getStorage()->destroySession();

			if ( WC()->session instanceof \WC_Session ) {
				WC()->session = null;
			}

			if ( WC()->cart instanceof \WC_Cart ) {
				WC()->cart = null;
			}

			unset( $_COOKIE );
			$_COOKIE = $this->get_session_id( $cart_id );

			if ( isset( $_COOKIE['customer_id'] ) ) {
				wp_set_current_user( (int) $_COOKIE['customer_id'] );
			}

			$this->initiate_wc_cart();

			if ( method_exists( WC()->session, 'get_session_data' ) ) {
				WC()->session->get_session_data();
			}

			if ( method_exists( WC()->cart, 'get_cart_for_session' ) ) {
				WC()->cart->get_cart_for_session();
			}

			if ( method_exists( WC()->cart, 'get_cart_from_session' ) ) {
				WC()->cart->get_cart_from_session();
			}

			InPostIzi::getStorage()->insertSession( BasketIdentification::INPOSTIZI_BASKET_ID, $cart_id );
		}
	}

	/**
	 * Delete cart session entity by cart ID.
	 *
	 * @param string $cart_id WooCommerce cart identifier.
	 *
	 * @return void
	 */
	public function delete_by_cart_id( string $cart_id ): void {
		$entity = $this->get_entity_cached( $cart_id );

		if ( ! $entity || ! $entity->get_id() ) {
			return;
		}

		$entity->set_redirect_url( 'deleted' );
		$this->repository->save( $entity );
		$this->entity_cache[ $cart_id ] = $entity;
	}

	/**
	 * Get redirection flag for given cart ID.
	 *
	 * @param string $cart_id WooCommerce cart identifier.
	 *
	 * @return int|null
	 */
	public function get_redirected_by_id( string $cart_id ): ?int {
		$entity = $this->get_entity_cached( $cart_id );

		if ( ! $entity || ! $entity->get_id() ) {
			return null;
		}

		return $entity->get_redirected();
	}

	/**
	 * Set redirected flag for given cart.
	 *
	 * @param string $cart_id WooCommerce cart identifier.
	 * @param int    $value   Redirected flag value.
	 *
	 * @return void
	 */
	public function set_redirected_by_id( string $cart_id, int $value ): void {
		$entity = $this->get_entity_cached( $cart_id );

		if ( ! $entity || ! $entity->get_id() ) {
			return;
		}

		$entity->set_redirected( $value );
		$this->repository->save( $entity );
		$this->entity_cache[ $cart_id ] = $entity;
	}



	/**
	 * Link WooCommerce order to cart and set redirect URL.
	 *
	 * @param string     $cart_id      WooCommerce cart identifier.
	 * @param int|string $order_id     WooCommerce order ID or alias.
	 * @param string     $redirect_url Redirect URL.
	 *
	 * @return void
	 */
	public function set_order_to_cart( string $cart_id, $order_id, string $redirect_url ): void {
		$entity = $this->get_entity_cached( $cart_id );

		if ( ! $entity || ! $entity->get_id() ) {
			return;
		}

		$entity->set_order_id( $order_id );
		$entity->set_redirect_url( $redirect_url );
		$this->repository->save( $entity );
		$this->entity_cache[ $cart_id ] = $entity;
	}

	/**
	 * Get cart ID associated with WooCommerce order ID.
	 *
	 * @param int|string $order_id WooCommerce order ID.
	 *
	 * @return string|null
	 */
	public function get_cart_id_by_order_id( $order_id ): ?string {
		$entity = $this->repository->find_one_by( array( 'order_id' => $order_id ) );

		if ( ! $entity || ! $entity->get_id() ) {
			return null;
		}

		return $entity->get_cart_id();
	}

	/**
	 * Store confirmation response for given cart.
	 *
	 * @param string      $cart_id      WooCommerce cart identifier.
	 * @param string|null $confirmation Confirmation response string.
	 *
	 * @return void
	 */
	public function set_confirmation_to_cart( string $cart_id, ?string $confirmation ): void {
		if ( ! $confirmation ) {
			return;
		}

		Logger::log( 'CONFIRMATION_TO_CART: ' . $cart_id . ' ' . $confirmation . '' );

		$entity = $this->get_entity_cached( $cart_id );
		if ( ! $entity || ! $entity->get_id() ) {
			Logger::log( LoggerTrace::compact_backtrace() );
			Logger::response( 'NOT FOUND' );

			return;
		}

		$entity->set_confirmation_response( $confirmation );
		$this->repository->save( $entity );
		$this->entity_cache[ $cart_id ] = $entity;
	}

	/**
	 * Get redirect URL for cart order.
	 *
	 * @param string $cart_id WooCommerce cart identifier.
	 *
	 * @return string|null
	 */
	public function get_cart_order_redirect_url( string $cart_id ): ?string {
		$entity = $this->get_entity_cached( $cart_id );

		if ( ! $entity || ! $entity->get_id() ) {
			return null;
		}

		if ( 'deleted' === $entity->get_confirmation_response() ) {
			return 'deleted';
		}

		return $entity->get_redirect_url();
	}

	/**
	 * Get confirmation response for given cart.
	 *
	 * @param string $cart_id WooCommerce cart identifier.
	 *
	 * @return string|null
	 */
	public function get_cart_confirmation( string $cart_id ): ?string {
		$entity = $this->get_entity_cached( $cart_id );

		if ( ! $entity || ! $entity->get_id() ) {
			return null;
		}

		return $entity->get_confirmation_response();
	}

	/**
	 * Ensure WooCommerce cart and session are initialized.
	 *
	 * @throws RuntimeException When WooCommerce is not initialized.
	 *
	 * @return void
	 */
	// TODO: Add param for set customer session cookie
	public function initiate_wc_cart(): void {
		if ( defined( 'DOING_CRON' ) && DOING_CRON ) {
			return;
		}

		if ( ! did_action( 'woocommerce_init' ) ) {
			throw new RuntimeException( 'WooCommerce not initialized.' );
		}

		include_once WC_ABSPATH . 'includes/wc-cart-functions.php';
		include_once WC_ABSPATH . 'includes/class-wc-cart.php';

		if ( is_null( WC()->cart ) ) {
			\wc_load_cart();
		}

		if ( class_exists( 'WC_Session_Handler' ) && null === WC()->session ) {
			WC()->session = new WC_Session_Handler();
			WC()->session->init();
		}

		if ( ! WC()->session->get_session_cookie() ) {
			WC()->session->set_customer_session_cookie( true );
		}
	}

	/**
	 * Save base64-encoded basket cache data.
	 *
	 * @param string $cart_id WooCommerce cart identifier.
	 * @param string $data    Raw cache data.
	 *
	 * @return void
	 */
	public function set_cart_cache_by_id( string $cart_id, string $data ): void {
		$entity = $this->get_entity_cached( $cart_id );

		if ( ! $entity || ! $entity->get_id() ) {
			Logger::log( "CANNOT ADD CACHE! BASKET NOT FOUND {$cart_id}" );

			return;
		}

		Logger::log( "BASKET FOUND {$cart_id} ADDING CACHE" );
		$entity->set_basket_cache( base64_encode( $data ) );
		$this->repository->save( $entity );
		$this->entity_cache[ $cart_id ] = $entity;
	}

	/**
	 * Retrieve decoded cart cache data.
	 *
	 * @param string $cart_id WooCommerce cart identifier.
	 *
	 * @return string|null
	 */
	public function get_cart_cache_by_id( string $cart_id ): ?string {
		$entity = $this->get_entity_cached( $cart_id );

		if ( ! $entity || ! $entity->get_id() || ! $entity->get_basket_cache() ) {
			return null;
		}

		return base64_decode( $entity->get_basket_cache(), true );
	}

	/**
	 * Store WooCommerce cart snapshot.
	 *
	 * @param string $cart_id WooCommerce cart identifier.
	 *
	 * @return void
	 */
	public function set_wc_cart_snapshot( string $cart_id ): void {
		$data   = WooCommerceBasketCache::store( $cart_id );
		$entity = $this->get_entity_cached( $cart_id );

		if ( ! $entity || ! $entity->get_id() ) {
			return;
		}

		$entity->set_basket_cached( $data );
		$this->repository->save( $entity );
		$this->entity_cache[ $cart_id ] = $entity;
	}

	/**
	 * Get WooCommerce cart snapshot.
	 *
	 * @param string $cart_id WooCommerce cart identifier.
	 *
	 * @return string|null
	 */
	public function get_wc_cart_snapshot( string $cart_id ): ?string {
		$entity = $this->get_entity_cached( $cart_id );

		if ( ! $entity || ! $entity->get_id() ) {
			return null;
		}

		return $entity->get_basket_cached();
	}

	/**
	 * Retrieve entity object for given cart ID.
	 *
	 * @param string $cart_id WooCommerce cart identifier.
	 *
	 * @return CartSessionEntity|null
	 */
	public function get_object_by_id( string $cart_id ): ?CartSessionEntity {
		$entity = $this->get_entity_cached( $cart_id );

		if ( ! $entity || ! $entity->get_id() ) {
			return null;
		}

		return $entity;
	}

	/**
	 * Store cart coupons data.
	 *
	 * @param string $cart_id WooCommerce cart identifier.
	 * @param string $data    Coupons data JSON.
	 *
	 * @return void
	 */
	public function set_cart_coupons_by_id( string $cart_id, string $data ): void {
		$entity = $this->get_entity_cached( $cart_id );

		if ( ! $entity || ! $entity->get_id() ) {
			return;
		}

		$entity->set_coupons( $data );
		$this->repository->save( $entity );
		$this->entity_cache[ $cart_id ] = $entity;
	}

	/**
	 * Save cart delivery cache data as JSON.
	 *
	 * @param string $cart_id WooCommerce cart identifier.
	 * @param array  $data    Delivery data array.
	 *
	 * @return void
	 */
	public function set_cart_delivery_cache_by_id( string $cart_id, array $data ): void {
		$entity = $this->get_entity_cached( $cart_id );

		if ( ! $entity || ! $entity->get_id() ) {
			return;
		}

		$json = wp_json_encode( $data );
		$entity->set_basket_delivery_cache( $json );
		$this->repository->save( $entity );
		$this->entity_cache[ $cart_id ] = $entity;
	}

	/**
	 * Retrieve cart delivery cache as array.
	 *
	 * @param string $cart_id WooCommerce cart identifier.
	 *
	 * @return array
	 */
	public function get_cart_delivery_cache_by_id( string $cart_id ): array {
		$entity = $this->get_entity_cached( $cart_id );

		if ( ! $entity || ! $entity->get_id() ) {
			return array();
		}

		$data = $entity->get_basket_delivery_cache();

		if ( empty( $data ) ) {
			return array();
		}

		return json_decode( $data, true, 512, JSON_THROW_ON_ERROR );
	}

	/**
	 * Retrieve WooCommerce session ID for cart.
	 *
	 * @param string $cart_id WooCommerce cart identifier.
	 *
	 * @return string|null
	 */
	public function get_wc_cart_session( string $cart_id ): ?string {
		$entity = $this->get_entity_cached( $cart_id );

		if ( ! $entity || ! $entity->get_id() ) {
			return null;
		}

		return $entity->get_wc_cart_session();
	}

	/**
	 * Set action type for cart entity.
	 *
	 * @param string $cart_id WooCommerce cart identifier.
	 * @param string $data    Action type value.
	 *
	 * @return void
	 */
	public function set_action_by_id( string $cart_id, string $data ): void {
		$entity = $this->get_entity_cached( $cart_id );

		if ( ! $entity || ! $entity->get_id() ) {
			return;
		}

		$entity->set_action_type( $data );
		$this->repository->save( $entity );
		$this->entity_cache[ $cart_id ] = $entity;
	}

	/**
	 * Retrieve session cookies array for given cart.
	 *
	 * @param string $cart_id WooCommerce cart identifier.
	 *
	 * @return array
	 */
	public function get_session_id( string $cart_id ): array {
		$entity = $this->get_entity_cached( $cart_id );

		if ( ! $entity || ! $entity->get_id() ) {
			die( wp_json_encode( array() ) );
		}

		$session_id = $entity->get_session_id();

		if ( empty( $session_id ) ) {
			return array();
		}

		return json_decode( $session_id, true, 512, JSON_THROW_ON_ERROR );
	}

	/**
	 * Retrieve basket binding API key.
	 *
	 * @param string $cart_id WooCommerce cart identifier.
	 *
	 * @return string|null
	 */
	public function basket_binding_api_key( string $cart_id ): ?string {
		$api_key_from_cookie = $this->basket_binding_api_key_from_cookie( $cart_id );

		return $api_key_from_cookie ?? $this->basket_binding_api_key_from_database( $cart_id );
	}

	/**
	 * Bind cart to the API key.
	 *
	 * @param string $cart_id Cart identifier.
	 * @param string $basket_binding_api_key API key to bind.
	 *
	 * @return void
	 */
	public function set_basket_binding_api_key( string $cart_id, string $basket_binding_api_key ): void {
		$this->binding_repository->create_or_update( $cart_id, $basket_binding_api_key );
		Logger::log( "[CartSession] Bound basket={$cart_id} to api_key={$basket_binding_api_key}" );

		$entity = $this->get_entity_by_cart_id( $cart_id );

		if ( ! $entity ) {
			Logger::log( '[CartSession] No entity for basket - creating' );
			$this->store_current();
			$entity = $this->get_entity_by_cart_id( $cart_id );
		}

		$entity->set_basket_binding_api_key( $basket_binding_api_key );
		$this->repository->save( $entity );

		if ( $entity->get_id() ) {
			$this->entity_cache[ $entity->get_id() ] = $entity;
		}

		Logger::log( "[CartSession] Saved entity id={$entity->get_id()} for cart={$cart_id}" );
	}

	/**
	 * Retrieve cart entity by cart ID.
	 *
	 * @param string $cart_id Cart identifier.
	 *
	 * @return CartSessionEntity|null
	 */
	public function get_entity_by_cart_id( string $cart_id ): ?CartSessionEntity {
		$entity = $this->repository->find_one_by( array( 'cart_id' => $cart_id ) );

		if ( ! $entity instanceof CartSessionEntity ) {
			return null;
		}

		return $entity;
	}

	/**
	 * Get cart ID by WooCommerce session ID.
	 *
	 * @param string $session_id WooCommerce session ID.
	 *
	 * @return string|null
	 */
	public function get_session_by_wc_session_id( string $session_id ): ?string {
		$entity = $this->repository->find_one_by( array( 'wc_cart_session' => $session_id ) );

		if ( ! $entity || ! $entity->get_cart_id() ) {
			return null;
		}

		return $entity->get_cart_id();
	}

	/**
	 * Get analytics data for given cart.
	 *
	 * @param string $cart_id WooCommerce cart identifier.
	 *
	 * @return array
	 */
	public function get_analytics( string $cart_id ): array {
		$entity = $this->get_entity_cached( $cart_id );

		if ( ! $entity || ! $entity->get_id() ) {
			return array();
		}

		$analytics = $entity->get_analytics();

		if ( empty( $analytics ) ) {
			return array();
		}

		return json_decode( $analytics, true, 512, JSON_THROW_ON_ERROR );
	}

	/**
	 * Store analytics data for given cart.
	 *
	 * @param string $cart_id WooCommerce cart identifier.
	 * @param array  $data    Analytics data array.
	 *
	 * @return void
	 */
	public function store_analytics( string $cart_id, array $data ): void {
		$entity = $this->get_entity_cached( $cart_id );

		if ( ! $entity || ! $entity->get_id() ) {
			return;
		}

		$entity->set_analytics( wp_json_encode( $data ) );
		$this->repository->save( $entity );
		$this->entity_cache[ $cart_id ] = $entity;
	}

	/**
	 * Retrieve WooCommerce order ID for given cart.
	 *
	 * @param string $cart_id WooCommerce cart identifier.
	 *
	 * @return int|null
	 */
	public function get_order_id_by_cart_id( string $cart_id ): ?int {
		$entity = $this->get_entity_cached( $cart_id );

		if ( ! $entity || ! $entity->get_id() || ! $entity->get_order_id() ) {
			return null;
		}

		$order_id = $entity->get_order_id();

		if ( ! is_numeric( $order_id ) || (int) $order_id <= 0 ) {
			return null;
		}

		return (int) $order_id;
	}

	/**
	 * Remove basket binding API key.
	 *
	 * @param string $cart_id WooCommerce cart identifier.
	 *
	 * @return void
	 */
	public function remove_basket_binding_api_key( string $cart_id ): void {
		$binding_entity = $this->binding_repository->find_by_basket_id( $cart_id );

		if ( $binding_entity ) {
			$this->binding_repository->delete( $binding_entity );
			Logger::log( "[CartSession] Removed binding for cart_id={$cart_id}" );
		}

		$entity = $this->get_entity_cached( $cart_id );

		if ( ! $entity || ! $entity->get_id() ) {
			return;
		}

		$entity->set_basket_binding_api_key( null );
		$this->repository->save( $entity );
		$this->entity_cache[ $cart_id ] = $entity;
	}

	/**
	 * Determine whether cart should be redirected.
	 *
	 * @param string $cart_id WooCommerce cart identifier.
	 *
	 * @return bool
	 */
	public function should_redirect( string $cart_id ): bool {
		$entity = $this->get_entity_cached( $cart_id );

		if ( ! $entity || ! $entity->get_id() ) {
			return false;
		}

		$redirect_url = $entity->get_redirect_url();
		$confirmation = $entity->get_confirmation_response();

		if ( ! $redirect_url || 'deleted' === $redirect_url || 'deleted' === $confirmation ) {
			return false;
		}

		return $entity->get_redirected() <= 0;
	}

	/**
	 * Get redirect URL for front-end template.
	 *
	 * @param string $cart_id WooCommerce cart identifier.
	 *
	 * @return string|null
	 */
	public function get_redirect_url_for_template( string $cart_id ): ?string {
		$entity = $this->get_entity_cached( $cart_id );

		if ( ! $entity || ! $entity->get_id() ) {
			return null;
		}

		if ( 'deleted' === $entity->get_confirmation_response() ) {
			return null;
		}

		return $entity->get_redirect_url();
	}

	/**
	 * Reset cart state after successful order.
	 *
	 * @param string $cart_id WooCommerce cart identifier.
	 *
	 * @return void
	 */
	public function reset_after_order( string $cart_id ): void {
		$entity = $this->get_entity_cached( $cart_id );

		if ( ! $entity || ! $entity->get_id() ) {
			return;
		}

		$entity->set_order_id( null );
		$entity->set_redirect_url( 'deleted' );
		$entity->set_confirmation_response( 'deleted' );
		$entity->set_basket_binding_api_key( null );

		$this->repository->save( $entity );
		unset( $this->entity_cache[ $cart_id ] );
	}

	/**
	 * Retrieve basket binding API key from cookie.
	 *
	 * @param string $cart_id WooCommerce cart identifier.
	 *
	 * @return string|null
	 *
	 * @since 2.0.7
	 */
	private function basket_binding_api_key_from_cookie( string $cart_id ): ?string {
		$basket_binding_api_key_cookie = sanitize_text_field( wp_unslash( $_COOKIE['basket_binding_api_key'] ?? '' ) );

		$cookie_entity = null;
		if ( $basket_binding_api_key_cookie ) {
			$cookie_entity = $this->binding_repository->find_by_api_key( $basket_binding_api_key_cookie );
		}

		if ( $cookie_entity && $cookie_entity->get_basket_id() && $cookie_entity->get_basket_id() === $cart_id ) {
			Logger::log( '[CartSession] Basket binding API key found in cookie' );
			return $basket_binding_api_key_cookie;
		}

		return null;
	}

	/**
	 * Retrieve basket binding API key from database.
	 *
	 * @param string $cart_id WooCommerce cart identifier.
	 *
	 * @return string|null
	 *
	 * @since 2.0.7
	 */
	private function basket_binding_api_key_from_database( string $cart_id ): ?string {
		$binding_entity = $this->binding_repository->find_by_basket_id( $cart_id );
		if ( ! $binding_entity || ! $binding_entity->get_basket_binding_api_key() ) {
			return null;
		}

		return $binding_entity->get_basket_binding_api_key();
	}
}
