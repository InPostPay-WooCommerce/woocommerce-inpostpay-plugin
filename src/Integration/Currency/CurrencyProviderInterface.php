<?php

declare( strict_types=1 );

namespace Ilabs\Inpost_Pay\Integration\Currency;

interface CurrencyProviderInterface {
	public function isActive(): bool;

	/**
	 * Returns current currency code, e.g. 'PLN', 'EUR'.
	 */
	public function getCurrentCurrency(): ?string;

	/**
	 * Returns all available currencies, e.g. ['PLN', 'EUR'].
	 */
	public function getAvailableCurrencies(): array;

	/**
	 * Returns default currency code.
	 */
	public function getDefaultCurrency(): ?string;
}
