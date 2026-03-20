<?php
/**
 * Entity representing WooCommerce cart session data.
 *
 * @package Ilabs\Inpost_Pay\EntityLayer\Entity
 */

declare( strict_types=1 );

namespace Ilabs\Inpost_Pay\EntityLayer\Entity;

/**
 * Class CartSessionEntity
 *
 * Represents a persisted WooCommerce cart session with metadata and status.
 */
class CartSessionEntity extends BaseEntity {

	/**
	 * Table name for the cart session entity.
	 *
	 * @var string
	 */
	protected static string $table = 'izi_cart_session';

	/**
	 * Primary key column name.
	 *
	 * @var string
	 */
	protected static string $primary_key = 'id';

	/**
	 * Primary identifier.
	 *
	 * @var int|null
	 */
	protected ?int $id = null;

	/**
	 * Unique session identifier.
	 *
	 * @var string|null
	 */
	protected ?string $session_id = null;

	/**
	 * Confirmation response from API.
	 *
	 * @var string|null
	 */
	protected ?string $confirmation_response = null;

	/**
	 * Associated WooCommerce cart ID.
	 *
	 * @var string|null
	 */
	protected ?string $cart_id = null;

	/**
	 * Related WooCommerce order ID.
	 *
	 * @var mixed
	 */
	protected $order_id = null;

	/**
	 * Redirect URL for checkout or payment.
	 *
	 * @var string|null
	 */
	protected ?string $redirect_url = null;

	/**
	 * Cached basket JSON data.
	 *
	 * @var string|null
	 */
	protected ?string $basket_cache = null;

	/**
	 * Marker or timestamp for cached basket.
	 *
	 * @var string|null
	 */
	protected ?string $basket_cached = null;

	/**
	 * Cached basket delivery data.
	 *
	 * @var string|null
	 */
	protected ?string $basket_delivery_cache = null;

	/**
	 * Applied coupons data.
	 *
	 * @var string|null
	 */
	protected ?string $coupons = null;

	/**
	 * Indicates if the user has been redirected.
	 *
	 * @var int
	 */
	protected int $redirected = 0;

	/**
	 * Raw WooCommerce cart session data.
	 *
	 * @var string|null
	 */
	protected ?string $wc_cart_session = null;

	/**
	 * Session expiry timestamp (UNIX time).
	 *
	 * @var int
	 */
	protected int $session_expiry = 0;

	/**
	 * Cached Izi basket payload.
	 *
	 * @var string|null
	 */
	protected ?string $izi_basket = null;

	/**
	 * Basket binding API key (InPost identifier).
	 *
	 * @var string|null
	 */
	protected ?string $basket_binding_api_key = null;

	/**
	 * Stored analytics data.
	 *
	 * @var string|null
	 */
	protected ?string $analytics = null;

	/**
	 * Type of action triggering the session.
	 *
	 * @var string|null
	 */
	protected ?string $action_type = null;

	/**
	 * Get session entity ID.
	 *
	 * @return int|null The session ID.
	 */
	public function get_id(): ?int {
		return $this->id;
	}

	/**
	 * Set session entity ID.
	 *
	 * @param int|null $value The ID value.
	 *
	 * @return void
	 */
	public function set_id( $value ): void {
		$this->id = ( null !== $value ) ? (int) $value : null;
	}

	/**
	 * Get session identifier.
	 *
	 * @return string|null Session ID.
	 */
	public function get_session_id(): ?string {
		return $this->session_id;
	}

	/**
	 * Set session identifier.
	 *
	 * @param string|null $value Session ID.
	 *
	 * @return void
	 */
	public function set_session_id( ?string $value ): void {
		$this->session_id = $value;
	}

	/**
	 * Get confirmation response data.
	 *
	 * @return string|null Confirmation response.
	 */
	public function get_confirmation_response(): ?string {
		return $this->confirmation_response;
	}

	/**
	 * Set confirmation response data.
	 *
	 * @param string|null $value Confirmation response.
	 *
	 * @return void
	 */
	public function set_confirmation_response( ?string $value ): void {
		$this->confirmation_response = $value;
	}

	/**
	 * Get cart ID.
	 *
	 * @return string|null Cart ID.
	 */
	public function get_cart_id(): ?string {
		return $this->cart_id;
	}

	/**
	 * Set cart ID.
	 *
	 * @param string|null $value Cart ID.
	 *
	 * @return void
	 */
	public function set_cart_id( ?string $value ): void {
		$this->cart_id = $value;
	}

	/**
	 * Get order ID.
	 *
	 * @return mixed Order ID.
	 */
	public function get_order_id() {
		return $this->order_id;
	}

	/**
	 * Set order ID.
	 *
	 * @param mixed $value Order ID.
	 *
	 * @return void
	 */
	public function set_order_id( $value ): void {
		$this->order_id = $value;
	}

	/**
	 * Get redirect URL.
	 *
	 * @return string|null Redirect URL.
	 */
	public function get_redirect_url(): ?string {
		return $this->redirect_url;
	}

	/**
	 * Set redirect URL.
	 *
	 * @param string|null $value Redirect URL.
	 *
	 * @return void
	 */
	public function set_redirect_url( ?string $value ): void {
		$this->redirect_url = $value;
	}

	/**
	 * Get basket cache data.
	 *
	 * @return string|null Basket cache.
	 */
	public function get_basket_cache(): ?string {
		return $this->basket_cache;
	}

	/**
	 * Set basket cache data.
	 *
	 * @param string|null $value Basket cache.
	 *
	 * @return void
	 */
	public function set_basket_cache( ?string $value ): void {
		$this->basket_cache = $value;
	}

	/**
	 * Get basket cached timestamp or marker.
	 *
	 * @return string|null Basket cached data.
	 */
	public function get_basket_cached(): ?string {
		return $this->basket_cached;
	}

	/**
	 * Set basket cached timestamp or marker.
	 *
	 * @param string|null $value Basket cached data.
	 *
	 * @return void
	 */
	public function set_basket_cached( ?string $value ): void {
		$this->basket_cached = $value;
	}

	/**
	 * Get basket delivery cache data.
	 *
	 * @return string|null Basket delivery cache.
	 */
	public function get_basket_delivery_cache(): ?string {
		return $this->basket_delivery_cache;
	}

	/**
	 * Set basket delivery cache data.
	 *
	 * @param string|null $value Basket delivery cache.
	 *
	 * @return void
	 */
	public function set_basket_delivery_cache( ?string $value ): void {
		$this->basket_delivery_cache = $value;
	}

	/**
	 * Get applied coupons.
	 *
	 * @return string|null Coupons data.
	 */
	public function get_coupons(): ?string {
		return $this->coupons;
	}

	/**
	 * Set applied coupons.
	 *
	 * @param string|null $value Coupons data.
	 *
	 * @return void
	 */
	public function set_coupons( ?string $value ): void {
		$this->coupons = $value;
	}

	/**
	 * Get redirect status flag.
	 *
	 * @return int Redirected flag.
	 */
	public function get_redirected(): int {
		return $this->redirected;
	}

	/**
	 * Set redirect status flag.
	 *
	 * @param int $value Redirected flag.
	 *
	 * @return void
	 */
	public function set_redirected( int $value ): void {
		$this->redirected = $value;
	}

	/**
	 * Check if the session was redirected.
	 *
	 * @return bool True if redirected, false otherwise.
	 */
	public function is_redirected(): bool {
		return ( 1 === $this->redirected );
	}

	/**
	 * Get WooCommerce cart session data.
	 *
	 * @return string|null WC cart session.
	 */
	public function get_wc_cart_session(): ?string {
		return $this->wc_cart_session;
	}

	/**
	 * Set WooCommerce cart session data.
	 *
	 * @param string|null $value WC cart session.
	 *
	 * @return void
	 */
	public function set_wc_cart_session( ?string $value ): void {
		$this->wc_cart_session = $value;
	}

	/**
	 * Get session expiry timestamp.
	 *
	 * @return int Expiry timestamp.
	 */
	public function get_session_expiry(): int {
		return $this->session_expiry;
	}

	/**
	 * Set session expiry timestamp.
	 *
	 * @param int $value Expiry timestamp.
	 *
	 * @return void
	 */
	public function set_session_expiry( int $value ): void {
		$this->session_expiry = $value;
	}

	/**
	 * Check if the session has expired.
	 *
	 * @return bool True if expired, false otherwise.
	 */
	public function is_expired(): bool {
		if ( 0 === $this->session_expiry ) {
			return false;
		}

		return ( time() > $this->session_expiry );
	}

	/**
	 * Get basket binding API key.
	 *
	 * @return string|null Basket binding API key.
	 */
	public function get_basket_binding_api_key(): ?string {
		return $this->basket_binding_api_key;
	}

	/**
	 * Set basket binding API key.
	 *
	 * @param string|null $value Basket binding API key.
	 *
	 * @return void
	 */
	public function set_basket_binding_api_key( ?string $value ): void {
		$this->basket_binding_api_key = $value;
	}

	/**
	 * Get analytics data.
	 *
	 * @return string|null Analytics data.
	 */
	public function get_analytics(): ?string {
		return $this->analytics;
	}

	/**
	 * Set analytics data.
	 *
	 * @param string|null $value Analytics data.
	 *
	 * @return void
	 */
	public function set_analytics( ?string $value ): void {
		$this->analytics = $value;
	}

	/**
	 * Get action type.
	 *
	 * @return string|null Action type.
	 */
	public function get_action_type(): ?string {
		return $this->action_type;
	}

	/**
	 * Set action type.
	 *
	 * @param string|null $value Action type.
	 *
	 * @return void
	 */
	public function set_action_type( ?string $value ): void {
		$this->action_type = $value;
	}
}
