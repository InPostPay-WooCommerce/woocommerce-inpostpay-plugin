<?php

declare( strict_types=1 );

namespace Ilabs\Inpost_Pay\Integration\Currency\Provider;

use Ilabs\Inpost_Pay\Integration\Currency\CurrencyProviderInterface;

class YayCurrencyProvider implements CurrencyProviderInterface {
	public function isActive(): bool {
		return defined( 'YAY_CURRENCY_VERSION' );
	}

	public function getCurrentCurrency(): ?string {
		if ( class_exists( 'Yay_Currency\Helpers\YayCurrencyHelper' ) &&
		     method_exists( 'Yay_Currency\Helpers\YayCurrencyHelper', 'get_current_currency' ) ) {

			$current_currency = \Yay_Currency\Helpers\YayCurrencyHelper::get_current_currency();
			if ( is_array( $current_currency ) && isset( $current_currency['currency'] ) && is_string( $current_currency['currency'] ) ) {
				return strtoupper( $current_currency['currency'] );
			}
		}

		return get_woocommerce_currency();
	}


	public function getAvailableCurrencies(): array {
		if (
			class_exists( 'Yay_Currency\Helpers\Helper' ) &&
			method_exists( 'Yay_Currency\Helpers\Helper', 'get_currencies_post_type' )
		) {
			$posts = \Yay_Currency\Helpers\Helper::get_currencies_post_type();
			$codes = [];

			if ( is_array( $posts ) ) {
				foreach ( $posts as $post ) {
					if ( $post instanceof \WP_Post && ! empty( $post->post_title ) ) {
						$codes[] = strtoupper( $post->post_title );
					}
				}
			}

			if ( ! empty( $codes ) ) {
				return array_unique( $codes );
			}
		}

		return [ $this->getDefaultCurrency() ];
	}

	public function getDefaultCurrency(): string {
		if (
			class_exists( 'Yay_Currency\Helpers\Helper' ) &&
			method_exists( 'Yay_Currency\Helpers\Helper', 'default_currency_code' )
		) {
			$default = \Yay_Currency\Helpers\Helper::default_currency_code();
			if ( is_string( $default ) && ! empty( $default ) ) {
				return strtoupper( $default );
			}
		}

		return get_woocommerce_currency();
	}
}
