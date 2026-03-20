<?php
/**
 * Basket identification.
 *
 * @package Ilabs\Inpost_Pay
 */

namespace Ilabs\Inpost_Pay\Lib;

use Ilabs\Inpost_Pay\Logger;
use Ilabs\Inpost_Pay\models\CartSessionService;
use Ilabs\Inpost_Pay\rest\RestRequest;
use function Ilabs\Inpost_Pay\inpost_pay_container;

/**
 * Basket identification helper.
 *
 * Provides methods for reading and generating basket identification used by
 * InPost Pay widget/binding flow. Uses WooCommerce session when available.
 *
 * @package Ilabs\Inpost_Pay
 */
class BasketIdentification {
	/**
	 * Session key used for storing basket identification.
	 */
	public const INPOSTIZI_BASKET_ID = 'inpostizi_basket_id';

	/**
	 * Cached cart session service instance.
	 *
	 * @var CartSessionService|null
	 */
	private static ?CartSessionService $cart_session_service = null;

	/**
	 * In-memory cache for the current basket ID.
	 *
	 * @var array
	 */
	private static array $id_cache = array();

	/**
	 * Get basket identification from session (or generate when session not available).
	 *
	 * Does not initialize WooCommerce session.
	 *
	 * @return string Basket identification.
	 */
	public static function getFromSession(): string {
		// Logger::log('Getting basket identification from session');
		$cart_session = self::get_cart_service();

		// Don't initialize WC session here - it's initialized in Add.php/Binding.php
		// findSession() returns null if session doesn't exist (cache-friendly pages)
		$identificationStored = InPostIzi::getStorage()->findSession( self::INPOSTIZI_BASKET_ID );

		if ( ! $identificationStored && get_current_user_id() ) {
			$userId               = get_current_user_id();
			$identificationStored = $cart_session->get_session_by_wc_session_id( (string) $userId );

//			if ( $identificationStored ) {
//				Logger::log( 'BASKET IDENTIFICATION FROM SESSION: ' . $identificationStored . ' - USER ID' );
//			}
		}

		if ( $identificationStored && 0 !== $cart_session->get_redirected_by_id( $identificationStored ) ) {
			BindingProvider::unsetBinding();
			$cart_session->set_confirmation_to_cart( $identificationStored, null );
			$cart_session->clear_redirect_url_by_cart_id( $identificationStored );

			if ( $identificationStored ) {
				Logger::log( 'BASKET IDENTIFICATION FROM SESSION: ' . $identificationStored . ' - REDIRECTED' );
			}
		}

		if ( $identificationStored && 'deleted' === $cart_session->get_cart_order_redirect_url( $identificationStored ) ) {
			BindingProvider::unsetBinding();
			$cart_session->set_confirmation_to_cart( $identificationStored, null );
			$cart_session->clear_redirect_url_by_cart_id( $identificationStored );

			if ( $identificationStored ) {
				Logger::log( 'BASKET IDENTIFICATION FROM SESSION: ' . $identificationStored . ' - ORDER DELETED' );
			}
		}

		if ( $identificationStored && $cart_session->get_order_id_by_cart_id( $identificationStored ) ) {
			BindingProvider::unsetBinding();
			$cart_session->set_confirmation_to_cart( $identificationStored, null );
			$cart_session->clear_redirect_url_by_cart_id( $identificationStored );

			if ( $identificationStored ) {
				Logger::log( 'BASKET IDENTIFICATION FROM SESSION: ' . $identificationStored . ' - ORDER EXISTS' );
			}
		}

		$identificationStored = InPostIzi::getStorage()->findSession( self::INPOSTIZI_BASKET_ID );

		if ( $identificationStored ) {
			InPostIzi::$inpostIziBasketId = $identificationStored;

			return $identificationStored;
		}

		// If no session exists, generate ID but DON'T store in session
		// insertSession() would throw exception here (session not initialized)
		// Session will be created when user adds to cart (Add.php REST endpoint)
		$identificationGenerated      = IdentificationGenerator::generate();
		InPostIzi::$inpostIziBasketId = $identificationGenerated;

		return $identificationGenerated;
	}

	/**
	 * Get cart session service from DI container.
	 *
	 * @return CartSessionService
	 */
	private static function get_cart_service(): CartSessionService {
		if ( null === self::$cart_session_service ) {
			self::$cart_session_service = inpost_pay_container()->get( CartSessionService::SERVICE_KEY );
		}

		return self::$cart_session_service;
	}

	/**
	 * Get current basket identification.
	 *
	 * Uses in-memory cache when available. When not in REST request and global
	 * basket ID is not set, tries to read it from session.
	 *
	 * @return string Basket identification.
	 */
	public static function get(): string {
		if ( isset( self::$id_cache['current'] ) ) {
			return self::$id_cache['current'];
		}

		if ( ! InPostIzi::$inpostIziBasketId && ! RestRequest::isRequested() ) {
			$id = self::getFromSession();
		} else {
			$id = InPostIzi::$inpostIziBasketId;
		}

		self::$id_cache['current'] = $id;

		return $id;
	}

	/**
	 * Generate new basket identification and store in session.
	 *
	 * This method requires session to be initialized.
	 *
	 * @return void
	 */
	public static function generate(): void {
		InPostIzi::$inpostIziBasketId = IdentificationGenerator::generate();
		// insertSession() will throw exception if session not initialized
		// This is intended - generate() should only be called when session exists
		InPostIzi::getStorage()->insertSession( self::INPOSTIZI_BASKET_ID, InPostIzi::$inpostIziBasketId );
	}

	/**
	 * Set basket identification and store it in session.
	 *
	 * This method requires session to be initialized.
	 *
	 * @param string $id Basket identification.
	 *
	 * @return void
	 */
	public static function set( $id ): void {
		InPostIzi::$inpostIziBasketId = $id;
		self::$id_cache['current']    = $id;
		// insertSession() will throw exception if session not initialized
		// This is intended - set() should only be called when session exists
		InPostIzi::getStorage()->insertSession( self::INPOSTIZI_BASKET_ID, $id );
	}

	/**
	 * Drop basket identification from session.
	 *
	 * @return void
	 */
	public static function drop(): void {
		Logger::log( 'DROPPING IDENTIFICATION' );
		InPostIzi::getStorage()->eraseSession( self::INPOSTIZI_BASKET_ID );
		BindingProvider::unsetBinding();
		InPostIzi::$inpostIziBasketId = '';
	}
}
