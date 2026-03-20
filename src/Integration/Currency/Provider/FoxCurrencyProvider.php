<?php

declare( strict_types=1 );

namespace Ilabs\Inpost_Pay\Integration\Currency\Provider;

use Ilabs\Inpost_Pay\Integration\Currency\CurrencyProviderInterface;
use function Ilabs\Inpost_Pay\Integration\Currency\woocs;

class FoxCurrencyProvider implements CurrencyProviderInterface {
	public function isActive(): bool {
		return defined( 'WOOCS_VERSION' ) || isset( $_COOKIE['woocommerce_currency'] );
	}

	public function getCurrentCurrency(): ?string {
		if ( isset( $_COOKIE['woocommerce_currency'] ) ) {
			return strtoupper( $_COOKIE['woocommerce_currency'] );
		}

		return get_woocommerce_currency();
	}

	public function getAvailableCurrencies(): array {
		if ( function_exists( 'woocs' ) ) {
			$woocs = woocs();
			if ( $woocs && is_array( $woocs->get_currencies() ) ) {
				return array_keys( $woocs->get_currencies() );
			}
		}

		global $WOOCS;
		if ( isset( $WOOCS ) && method_exists( $WOOCS, 'get_currencies' ) ) {
			return array_keys( $WOOCS->get_currencies() );
		}

		return [ get_woocommerce_currency() ];
	}

	public function getDefaultCurrency(): ?string {
		if ( function_exists( 'woocs' ) ) {
			$woocs = woocs();
			if ( $woocs && ! empty( $woocs->default_currency ) ) {
				return strtoupper( $woocs->default_currency );
			}
		}

		global $WOOCS;
		if ( isset( $WOOCS ) && ! empty( $WOOCS->default_currency ) ) {
			return strtoupper( $WOOCS->default_currency );
		}

		return get_woocommerce_currency();
	}
}
