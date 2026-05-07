<?php

declare( strict_types=1 );

namespace Ilabs\Inpost_Pay\Integration\Currency;

use Ilabs\Inpost_Pay\Integration\Currency\Provider\CurcyCurrencyProvider;
use Ilabs\Inpost_Pay\Integration\Currency\Provider\FoxCurrencyProvider;
use Ilabs\Inpost_Pay\Integration\Currency\Provider\WpmlCurrencyProvider;
use Ilabs\Inpost_Pay\Integration\Currency\Provider\YayCurrencyProvider;

class CurrencyHelper {
	public const AVAILABLE_CURRENCIES = array(
		'PLN',
	);

	private static ?CurrencyProviderInterface $provider = null;

	public static function getProvider(): ?CurrencyProviderInterface {
		if ( self::$provider !== null ) {
			return self::$provider;
		}

		$wpml  = new WpmlCurrencyProvider();
		$fox   = new FoxCurrencyProvider();
		$curcy = new CurcyCurrencyProvider();
		$yay   = new YayCurrencyProvider();

		if ( $fox->isActive() ) {
			self::$provider = $fox;

			return $fox;
		}

		if ( $wpml->isActive() ) {
			self::$provider = $wpml;

			return $wpml;
		}

		if ( $curcy->isActive() ) {
			self::$provider = $curcy;

			return $curcy;
		}

		if ( $yay->isActive() ) {
			self::$provider = $yay;

			return $yay;
		}

		return null;
	}

	public static function getProviderClass(): ?string {
		$provider = self::getProvider();

		return $provider ? get_class( $provider ) : null;
	}

	public static function isCurrencySystemActive(): bool {
		return self::getProvider() !== null;
	}

	public static function getCurrentCurrency(): string {
		$provider = self::getProvider();

		if ( $provider && ( $currency = $provider->getCurrentCurrency() ) ) {
			return $currency;
		}

		return get_woocommerce_currency();
	}

	public static function getDefaultCurrency(): string {
		$provider = self::getProvider();

		if ( $provider && ( $currency = $provider->getDefaultCurrency() ) ) {
			return $currency;
		}

		return get_woocommerce_currency();
	}

	public static function getAvailableCurrencies(): array {
		$provider = self::getProvider();

		if ( $provider ) {
			$currencies = $provider->getAvailableCurrencies();
			if ( ! empty( $currencies ) ) {
				return $currencies;
			}
		}

		return array( get_woocommerce_currency() );
	}

	public static function isCurrencyAllowed(): bool {
		return in_array( self::getCurrentCurrency(), self::AVAILABLE_CURRENCIES, true );
	}
}
