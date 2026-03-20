<?php

declare( strict_types=1 );

namespace Ilabs\Inpost_Pay\Integration\Currency\Provider;

use Ilabs\Inpost_Pay\Integration\Currency\CurrencyProviderInterface;

class CurcyCurrencyProvider implements CurrencyProviderInterface {
	public function isActive(): bool {
		if ( ! function_exists( 'is_plugin_active' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		if ( is_plugin_active( 'woo-multi-currency/woo-multi-currency.php' ) ) {
			return true;
		}

		return false;
	}

	public function getAvailableCurrencies(): array {
		$general = get_option( 'wmc_general' );

		if ( is_array( $general ) ) {
			$currencies = [];

			if ( ! empty( $general['default_currency'] ) ) {
				$currencies[] = strtoupper( $general['default_currency'] );
			}

			if ( ! empty( $general['additional_currencies'] ) && is_array( $general['additional_currencies'] ) ) {
				foreach ( $general['additional_currencies'] as $code ) {
					$currencies[] = strtoupper( $code );
				}
			}

			return array_unique( $currencies );
		}

		return [ get_woocommerce_currency() ];
	}

	public function getDefaultCurrency(): ?string {
		$general = get_option( 'wmc_general' );

		if ( is_array( $general ) && ! empty( $general['default_currency'] ) ) {
			return strtoupper( $general['default_currency'] );
		}

		return get_woocommerce_currency();
	}

	public function getCurrentCurrency(): ?string {
		if ( isset( $_COOKIE['wmc_current_currency'] ) ) {
			return strtoupper( $_COOKIE['wmc_current_currency'] );
		}

		$general = get_option( 'wmc_general' );
		if ( is_array( $general ) && ! empty( $general['default_currency'] ) ) {
			return strtoupper( $general['default_currency'] );
		}

		return get_woocommerce_currency();
	}

}
