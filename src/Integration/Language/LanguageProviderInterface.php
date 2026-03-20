<?php

declare(strict_types=1);

namespace Ilabs\Inpost_Pay\Integration\Language;

interface LanguageProviderInterface {
	public function isActive(): bool;

	/**
	 * Returns current language slug, eg. 'pl', 'en'.
	 */
	public function getCurrentSlug(): ?string;

	/**
	 * Returns all available language slugs, eg. ['pl', 'en'].
	 */
	public function getAvailableSlugs(): array;

	/**
	 * Returns current locale, eg. 'pl_PL'.
	 */
	public function getCurrentLocale(): ?string;

	/**
	 * Returns all available locales, eg. ['pl_PL', 'en_GB'].
	 */
	public function getAvailableLocales(): array;

	/**
	 * Returns mapping like ['pl' => 'pl_PL'].
	 */
	public function getSlugToLocaleMap(): array;
}
