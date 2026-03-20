<?php

declare( strict_types=1 );

namespace Ilabs\Inpost_Pay\Integration\Currency;

use Ilabs\Inpost_Pay\InpostPay;
use Ilabs\Inpost_Pay\Lib\BasketIdentification;
use Ilabs\Inpost_Pay\Lib\Remote;
use Ilabs\Inpost_Pay\Logger;
use Ilabs\Inpost_Pay\models\CartSessionService;
use function Ilabs\Inpost_Pay\inpost_pay_container;

class CurrencyStateManager {
	private const LAST_CURRENCY_TRANSIENT = 'inpost_pay_last_currency';
	private const CURRENCY_RESTORED_TRANSIENT = 'inpost_pay_currency_restored_';
	private const WAS_BINDING_RESTORED = 'inpost_pay_was_binding_restored_';
	private const WAS_INPOST_PAY_UNBOUND = 'inpost_pay_was_unbound_';
	private const STORED_BINDING_PREFIX = 'inpost_pay_stored_binding_';

	private static ?CartSessionService $cart_session_service = null;

	private static function get_cart_service(): CartSessionService {
		if ( null === self::$cart_session_service ) {
			self::$cart_session_service = inpost_pay_container()->get( CartSessionService::SERVICE_KEY );
		}

		return self::$cart_session_service;
	}

	public static function handleCurrencyChange(): void {
		$currentCurrency = CurrencyHelper::getCurrentCurrency();
		$lastCurrency    = get_transient( self::LAST_CURRENCY_TRANSIENT );

		if ( empty( $lastCurrency ) ) {
			set_transient( self::LAST_CURRENCY_TRANSIENT, $currentCurrency, DAY_IN_SECONDS );

			return;
		}

		if ( $currentCurrency !== $lastCurrency ) {
//			Logger::log( "Currency changed from {$lastCurrency} to {$currentCurrency}" );

			$wasAllowed = in_array( $lastCurrency, CurrencyHelper::AVAILABLE_CURRENCIES, true );
			$isAllowed  = in_array( $currentCurrency, CurrencyHelper::AVAILABLE_CURRENCIES, true );

//			Logger::log( "[CurrencyState] wasAllowed: {$wasAllowed}, isAllowed: {$isAllowed}" );

			$basketId = BasketIdentification::get();
			if ( empty( $basketId ) ) {
				Logger::log( '[CurrencyState] No basket ID found, skipping currency handling' );

				return;
			}

			$cart_session = self::get_cart_service();

			if ( $wasAllowed && ! $isAllowed ) {
//				Logger::log( "[CurrencyState] Unbinding triggered. Basket ID: {$basketId}" );
				$bindingKey = $cart_session->basket_binding_api_key( $basketId );
//				Logger::log( "[CurrencyState] Unbinding triggered. Basket ID: {$basketId}, Key: {$bindingKey}" );

				if ( ! empty( $bindingKey ) ) {
					self::unbindAppCart( $bindingKey, $basketId );
				}
			} elseif ( ! $wasAllowed && $isAllowed ) {
				$wasRestored = static::restoreAppCartBinding();

				if ( $wasRestored ) {
					set_transient(
						self::WAS_BINDING_RESTORED . self::getUserIdentifier(),
						'1',
						MINUTE_IN_SECONDS * 5
					);

//					Logger::log('[CurrencyState] Cart binding restored: set_transient WAS_BINDING_RESTORED_' . self::getUserIdentifier());
				}

				set_transient(
					self::CURRENCY_RESTORED_TRANSIENT . self::getUserIdentifier(),
					'1',
					MINUTE_IN_SECONDS * 5
				);
			}

			set_transient( self::LAST_CURRENCY_TRANSIENT, $currentCurrency, DAY_IN_SECONDS );
		}
	}

	public static function wasBindingRestored(): bool {
		$user        = self::getUserIdentifier();
		$wasRestored = get_transient( self::WAS_BINDING_RESTORED . $user ) === '1';

		if ( $wasRestored ) {
			self::clearBindingRestoredFlag();
		}

		return $wasRestored;
	}

	public static function clearBindingRestoredFlag(): void {
		$user = self::getUserIdentifier();
		delete_transient( self::CURRENCY_RESTORED_TRANSIENT . $user );
		delete_transient( self::WAS_BINDING_RESTORED . $user );
		delete_transient( self::WAS_INPOST_PAY_UNBOUND . $user );
	}

	private static function unbindAppCart( string $bindingKey, string $cart_id ): void {
		Logger::log( "[CurrencyState] Unbinding cart {$cart_id} with key {$bindingKey}" );

		set_transient( self::STORED_BINDING_PREFIX . $cart_id, $bindingKey, HOUR_IN_SECONDS * 12 );

		$response = ( new Remote() )->basket_binding_delete();

		if (
			! isset( $response->error_code ) ||
			! in_array( $response->error_code, [ 'BASKET_NOT_FOUND', 'BASKET_NOT_BOUND' ], true )
		) {
			set_transient(
				self::WAS_INPOST_PAY_UNBOUND . self::getUserIdentifier(),
				'1',
				MINUTE_IN_SECONDS * 5
			);
			Logger::log( '[CurrencyState] Set WAS_INPOST_PAY_UNBOUND for user: ' . self::getUserIdentifier() );
		} else {
			Logger::log( '[CurrencyState] Skip setting WAS_INPOST_PAY_UNBOUND due to error: ' . $response->error_code );
		}

		$cart_session = self::get_cart_service();
		$cart_session->remove_basket_binding_api_key( $cart_id );

		delete_transient( self::WAS_BINDING_RESTORED . self::getUserIdentifier() );
		Logger::log( '[CurrencyState] Cleared WAS_BINDING_RESTORED after unbind. Cart unbound and binding key stored' );
	}

	private static function restoreAppCartBinding(): bool {
		$user = self::getUserIdentifier();

		if ( get_transient( self::WAS_INPOST_PAY_UNBOUND . $user ) !== '1' ) {
			Logger::log( '[CurrencyState] Restore skipped – cart was not previously unbound by us' );
			return false;
		}

		$cart_id = BasketIdentification::get();
		if ( empty( $cart_id ) ) {
			Logger::log( '[CurrencyState] Restore failed – no basket ID' );

			return false;
		}

		$storedBindingKey = get_transient( self::STORED_BINDING_PREFIX . $cart_id );
		if ( ! $storedBindingKey ) {
			Logger::log( '[CurrencyState] No stored binding key for basket, skipping restore' );

			return false;
		}

		Logger::log( "[CurrencyState] Restoring cart binding for basket {$cart_id}" );
		$cart_session = self::get_cart_service();
		$cart_session->set_basket_binding_api_key( $cart_id, $storedBindingKey );
		$cart_session->initiate_wc_cart();
		$cart_session->store_current();

		$basketData = self::getBasketData();
		if ( $basketData ) {
			( new Remote() )->basket_put( $basketData, true );
			Logger::log( '[CurrencyState] Basket data sent to app after binding restore' );
		} else {
			Logger::log( '[CurrencyState] No basket data to send to app after binding restore' );
		}

		delete_transient( self::STORED_BINDING_PREFIX . $cart_id );
		delete_transient( self::WAS_INPOST_PAY_UNBOUND . $user );

		return true;
	}

	private static function getBasketData(): ?string {
		try {
			$cart_session = self::get_cart_service();
			$basketId   = BasketIdentification::get();
			$basketData = $cart_session->get_cart_cache_by_id( $basketId );

			if ( empty( $basketData ) ) {
				$inpostPay = InpostPay::get_instance();
				if ( $inpostPay ) {
					$basket     = $inpostPay->get_lib()->getBasket();
					$basketData = $basket->encode();
					$cart_session->set_cart_cache_by_id( $basketId, $basketData );
				}
			}

			return $basketData;
		} catch ( \Exception $e ) {
			Logger::log( '[CurrencyState] Error getting basket data: ' . $e->getMessage() );

			return null;
		}
	}

	private static function getUserIdentifier(): string {
		if ( isset( $_COOKIE['inpost_pay_currency_restore_uid'] ) ) {
			return 'cookie_' . $_COOKIE['inpost_pay_currency_restore_uid'];
		}

		$uid = bin2hex( random_bytes( 8 ) );
		setcookie( 'inpost_pay_currency_restore_uid', $uid, time() + WEEK_IN_SECONDS, '/' );

		return 'cookie_' . $uid;
	}
}
