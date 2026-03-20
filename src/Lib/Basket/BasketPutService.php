<?php
/**
 * BasketPutService class.
 *
 * Handles the basket synchronization process with the InPost Pay API.
 * This service is responsible for building, caching, and sending basket data
 * to the remote InPost server when cart changes occur.
 *
 * @package Ilabs\Inpost_Pay\Lib\Basket
 * @since 1.0.0
 */

declare( strict_types=1 );

namespace Ilabs\Inpost_Pay\Lib\Basket;

use Ilabs\Inpost_Pay\Lib\BasketIdentification;
use Ilabs\Inpost_Pay\Lib\BindingProvider;
use Ilabs\Inpost_Pay\Lib\Controller;
use Ilabs\Inpost_Pay\Lib\InPostIzi;
use Ilabs\Inpost_Pay\Logger;
use Ilabs\Inpost_Pay\LoggerTrace;
use Ilabs\Inpost_Pay\models\CartSessionService;

/**
 * BasketPutService class.
 *
 * Service responsible for synchronizing the WooCommerce cart basket with InPost Pay API.
 * Manages basket building, caching, and sending to the remote server.
 *
 * @package Ilabs\Inpost_Pay\Lib\Basket
 */
class BasketPutService {
	public const SERVICE_KEY = 'basket_put_service';

	private CartSessionService $cart_session;

	/**
	 * Constructor.
	 *
	 * Initializes the BasketPutService with required dependencies.
	 *
	 * @param CartSessionService $cart_session Cart session service for managing basket cache.
	 */
	public function __construct( CartSessionService $cart_session ) {
		$this->cart_session = $cart_session;
	}

	/**
	 * Perform basket PUT operation.
	 *
	 * Builds the basket data, caches it, and optionally sends it to the InPost Pay API.
	 * This method handles the complete basket synchronization workflow including:
	 * - Building basket from provided callable
	 * - Caching basket data by basket ID
	 * - Checking binding status
	 * - Sending basket to remote API when appropriate
	 *
	 * @param callable   $basket_builder Callable that returns basket data to be processed.
	 * @param Controller $controller    Controller instance for API communication.
	 * @param bool       $force_unbound  Optional. Force basket PUT even without binding. Default false.
	 * @param bool       $just_store     Optional. Only store basket in cache without sending to API. Default false.
	 *
	 * @return void
	 */
	public function put(
		callable $basket_builder,
		Controller $controller,
		bool $force_unbound = false,
		bool $just_store = false
	): void {
		$block_put = InPostIzi::isBlockPut();

		Logger::log(
			'PERFORMING PUT WITH PARAMETERS: $forceUnbound = ' . (int) $force_unbound .
				', $justStore = ' . (int) $just_store .
				' self::$blockPut = ' . (int) $block_put
		);

		Logger::log( LoggerTrace::compact_backtrace() );

		if ( $block_put ) {
			Logger::log( 'Block PUT' );
			Logger::log( '[basketPut] Finished' );

			return;
		}

		Logger::log( '[basketPut] Step 1: Building basket' );

		try {
			$basket_data = $basket_builder();
			$basket      = ( is_object( $basket_data ) && method_exists( $basket_data, 'encode' ) ) ? $basket_data->encode() : (string) $basket_data;
		} catch ( \Throwable $e ) {
			Logger::log( '[basketPut] FATAL: getBasket() failed: ' . $e->getMessage() );
			Logger::log( '[basketPut] Stack trace: ' . $e->getTraceAsString() );

			return;
		}

		Logger::log( '[basketPut] Step 2: Saving basket cache' );

		$basket_id = BasketIdentification::get();

		$this->cart_session->set_cart_cache_by_id( $basket_id, $basket );

		if ( $just_store ) {
			Logger::log( '[basketPut] Just store mode - exiting' );

			return;
		}

		Logger::log( '[basketPut] Step 3: Checking binding' );
		$has_binding = BindingProvider::getBinding();
		Logger::log( '[basketPut] Has binding: ' . ( $has_binding ? 'YES' : 'NO' ) );

		if ( $force_unbound || $has_binding ) {
			Logger::log( 'Send basket to InPost' );

			try {
				$controller->basket_put( $basket, true );
				Logger::log( '[basketPut] PUT successful' );
			} catch ( \Throwable $e ) {
				Logger::log( '[basketPut] PUT failed: ' . $e->getMessage() );
				Logger::log( '[basketPut] Stack trace: ' . $e->getTraceAsString() );
			}
		} else {
			Logger::log( '[basketPut] No binding - skipping PUT' );
		}

		Logger::log( '[basketPut] Finished' );
	}
}
