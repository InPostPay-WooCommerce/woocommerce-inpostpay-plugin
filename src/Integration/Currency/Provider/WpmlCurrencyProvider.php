<?php

declare( strict_types=1 );

namespace Ilabs\Inpost_Pay\Integration\Currency\Provider;

use Ilabs\Inpost_Pay\Integration\Currency\CurrencyProviderInterface;
use WoocsHpos;

class WpmlCurrencyProvider implements CurrencyProviderInterface {
	public function isActive(): bool {
		return defined( 'ICL_SITEPRESS_VERSION' ) && class_exists( WoocsHpos::class );
	}

	public function getCurrentCurrency(): ?string {
		if ( ! $this->isActive() ) {
			return null;
		}

		if ( function_exists( 'get_woocommerce_currency' ) ) {
			return strtoupper( apply_filters( 'wcml_price_currency', get_woocommerce_currency() ) );
		}

		global $woocommerce_wpml;

		if ( isset( $woocommerce_wpml->multi_currency ) ) {
			return strtoupper( $woocommerce_wpml->multi_currency->get_client_currency() );
		}

		return null;
	}

	public function getAvailableCurrencies(): array {
		if ( ! $this->isActive() ) {
			return [];
		}

		global $woocommerce_wpml;

		if ( isset( $woocommerce_wpml->multi_currency ) ) {
			return array_keys( $woocommerce_wpml->multi_currency->get_currency_codes() ?? [] );
		}

		return [];
	}

	public function getDefaultCurrency(): ?string {
		if ( ! $this->isActive() ) {
			return null;
		}

		global $woocommerce_wpml;

		if ( isset( $woocommerce_wpml->multi_currency ) ) {
			return strtoupper( $woocommerce_wpml->multi_currency->get_default_currency() );
		}

		return null;
	}
}
