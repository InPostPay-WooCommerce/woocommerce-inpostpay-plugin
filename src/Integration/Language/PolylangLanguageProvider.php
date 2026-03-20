<?php

declare(strict_types=1);

namespace Ilabs\Inpost_Pay\Integration\Language;

class PolylangLanguageProvider implements LanguageProviderInterface
{
	public function isActive(): bool
	{
		return function_exists('pll_languages_list') && defined('POLYLANG_BASENAME');
	}

	public function getCurrentSlug(): ?string
	{
		return function_exists('pll_current_language') ? pll_current_language() : null;
	}

	public function getDefaultSlug(): ?string
	{
		return function_exists('pll_default_language') ? pll_default_language() : null;
	}

	public function getAvailableSlugs(): array
	{
		return function_exists('pll_languages_list') ? pll_languages_list() : [];
	}

	public function getAvailableLocales(): array
	{
		return function_exists('pll_languages_list')
			? pll_languages_list(['fields' => 'locale'])
			: [];
	}

	public function getSlugToLocaleMap(): array
	{
		if ( ! function_exists('pll_languages_list') ) {
			return [];
		}

		$slugs   = pll_languages_list(['fields' => 'slug']);
		$locales = pll_languages_list(['fields' => 'locale']);

		if ( ! is_array($slugs) || ! is_array($locales) || count($slugs) !== count($locales) ) {
			return [];
		}

		return array_combine($slugs, $locales);
	}

	public function getCurrentLocale(): ?string
	{
		if ( ! function_exists('pll_current_language') || ! function_exists('pll_languages_list') ) {
			return null;
		}

		$currentSlug = pll_current_language();
		$slugs       = pll_languages_list(['fields' => 'slug']);
		$locales     = pll_languages_list(['fields' => 'locale']);

		if ( ! is_array($slugs) || ! is_array($locales) || count($slugs) !== count($locales) ) {
			return null;
		}

		$map = array_combine($slugs, $locales);

		return $map[$currentSlug] ?? null;
	}
}
