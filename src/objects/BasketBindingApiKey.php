<?php
/**
 * BasketBindingApiKey class file.
 *
 * Manages basket binding API key lifecycle and persistence across
 * cookies, session storage, and database repository.
 *
 * @package Ilabs\Inpost_Pay\Objects
 */

declare( strict_types=1 );

namespace Ilabs\Inpost_Pay\objects;

use Ilabs\Inpost_Pay\Lib\BasketIdentification;
use Ilabs\Inpost_Pay\Lib\BindingProvider;
use Ilabs\Inpost_Pay\Lib\helpers\CookieHelper;
use Ilabs\Inpost_Pay\Lib\IdentificationGenerator;
use Ilabs\Inpost_Pay\Lib\InPostIzi;
use Ilabs\Inpost_Pay\Lib\Remote;
use Ilabs\Inpost_Pay\Logger;
use Ilabs\Inpost_Pay\LoggerTrace;
use Ilabs\Inpost_Pay\models\CartSessionService;
use function Ilabs\Inpost_Pay\inpost_pay_container;

/**
 * Class BasketBindingApiKey
 *
 * Handles retrieval, storage, and lifecycle of basket binding API keys
 * used for InPost Pay basket synchronization.
 */
class BasketBindingApiKey {

	/**
	 * Cached basket binding API key.
	 *
	 * @var string|null
	 */
	private ?string $basket_binding_api_key = null;

	/**
	 * Global flag to block automatic key obtaining.
	 *
	 * @var bool
	 */
	public static bool $BLOCK_OBTAIN = false;

	/**
	 * Cart session service instance.
	 *
	 * @var CartSessionService
	 */
	private CartSessionService $cart_session;

	/**
	 * Constructor.
	 *
	 * Attempts to restore basket binding API key from multiple sources
	 * in order: cookies, session storage, database repository.
	 */
	public function __construct() {
		/**
		 * Get from container DI.
		 *
		 * @var CartSessionService $cart_session
		 */
		$this->cart_session = inpost_pay_container()->get( CartSessionService::SERVICE_KEY );

		if ( ! empty( $_COOKIE['basket_binding_api_key'] ) ) {
			$this->basket_binding_api_key = sanitize_text_field( wp_unslash( $_COOKIE['basket_binding_api_key'] ) );
		}

		if ( null === $this->basket_binding_api_key && InPostIzi::getStorage()->issetSession( 'basket_binding_api_key' ) ) {
			$this->basket_binding_api_key = InPostIzi::getStorage()->findSession( 'basket_binding_api_key' );
		}

		if ( null === $this->basket_binding_api_key ) {
			$cart_id   = BasketIdentification::get();
			$from_repo = $this->cart_session->basket_binding_api_key( $cart_id );

			if ( $from_repo ) {
				$this->basket_binding_api_key = $from_repo;
//			} else {
//				Logger::log( "[BasketBindingApiKey] No binding found for cart_id={$cart_id}, will obtain on next get()" );
			}
		}
	}

	/**
	 * Retrieve basket binding API key.
	 *
	 * If key is not cached and $obtain is true, attempts to obtain
	 * a new key from InPost API.
	 *
	 * @param bool $obtain Whether to obtain new key if not cached.
	 *
	 * @return string|null Basket binding API key or null.
	 */
	public function get( bool $obtain = true ): ?string {
		if ( null !== $this->basket_binding_api_key ) {
			return $this->basket_binding_api_key;
		}

		if ( self::$BLOCK_OBTAIN ) {
			return null;
		}

		$cart_id   = BasketIdentification::get();
		$from_repo = $this->cart_session->basket_binding_api_key( $cart_id );
		if ( $from_repo ) {
			$this->basket_binding_api_key = $from_repo;

			return $this->basket_binding_api_key;
		}

		return $obtain ? $this->obtain() : null;
	}

	/**
	 * Drop basket binding API key from all storage layers.
	 *
	 * Clears session storage, cookie, and internal cache.
	 *
	 * @return void
	 */
	public function drop(): void {
		InPostIzi::getStorage()->eraseSession( 'basket_binding_api_key' );
		CookieHelper::delete( 'basket_binding_api_key' );
		$this->basket_binding_api_key = null;
	}

	/**
	 * Obtain new basket binding API key from InPost API.
	 *
	 * Initiates WooCommerce cart, sends basket binding request,
	 * stores obtained key in session, cookie, and database.
	 *
	 * @return string|null Newly obtained API key or null on failure.
	 */
	private function obtain(): ?string {
		if ( null !== $this->basket_binding_api_key ) {
			return $this->basket_binding_api_key;
		}

		if ( ! WC()->session ) {
			return null;
		}

//		Logger::log( '[BasketBindingApiKey] Obtaining new basket_binding_api_key' );

		try {
			$remote   = new Remote();
			$response = $remote->basket_binding_put( wp_json_encode( array(), JSON_THROW_ON_ERROR ) );

			$this->basket_binding_api_key = $response->basket_binding_api_key ?? null;

			if ( null === $this->basket_binding_api_key ) {
				Logger::log( '[BasketBindingApiKey] Failed to obtain basket_binding_api_key from API' );

				return null;
			}

			$cart_id = BasketIdentification::get();
			$this->cart_session->set_basket_binding_api_key( $cart_id, $this->basket_binding_api_key );

			CookieHelper::set_basket_binding_api_key( $this->basket_binding_api_key );

			Logger::log( "[BasketBindingApiKey] Obtained and stored basket_binding_api_key for cart_id={$cart_id}" );

		} catch ( \Throwable $e ) {
			Logger::log( '[BasketBindingApiKey] Exception during obtain: ' . $e->getMessage() );
			Logger::log( LoggerTrace::compact_backtrace() );

			return null;
		}

		return $this->basket_binding_api_key;
	}
}
