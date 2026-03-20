<?php

declare(strict_types=1);

namespace Ilabs\Inpost_Pay\Integration\Language;

class WpmlLanguageProvider implements LanguageProviderInterface
{
	public function isActive(): bool
	{
		return defined('ICL_SITEPRESS_VERSION') && function_exists('wpml_get_current_language');
	}

	public function getCurrentSlug(): ?string
	{
		return $this->isActive() ? wpml_get_current_language() : null;
	}

	public function getDefaultSlug(): ?string
	{
		return function_exists('wpml_get_default_language')
			? wpml_get_default_language()
			: null;
	}

	public function getAvailableSlugs(): array
	{
		if ( ! $this->isActive() ) {
			return [];
		}

		$languages = apply_filters('wpml_active_languages', null, ['skip_missing' => 0]);
		return is_array($languages) ? array_keys($languages) : [];
	}

	public function getCurrentLocale(): ?string
	{
		if ( ! $this->isActive() ) {
			return null;
		}

		$languages = apply_filters('wpml_active_languages', null);
		if ( is_array($languages) ) {
			foreach ( $languages as $lang ) {
				if ( isset($lang['active'], $lang['default_locale']) && $lang['active'] ) {
					return $lang['default_locale'];
				}
			}
		}
		return null;
	}

	public function getAvailableLocales(): array
	{
		if ( ! $this->isActive() ) {
			return [];
		}

		$languages = apply_filters('wpml_active_languages', null, ['skip_missing' => 0]);

		if ( ! is_array($languages) ) {
			return [];
		}

		return array_values(array_filter(array_map(
			static function ($lang) {
				return $lang['default_locale'] ?? null;
			},
			$languages
		)));
	}

	public function getSlugToLocaleMap(): array
	{
		if ( ! $this->isActive() ) {
			return [];
		}

		$languages = apply_filters('wpml_active_languages', null, ['skip_missing' => 0]);
		if ( ! is_array($languages) ) {
			return [];
		}

		$map = [];
		foreach ( $languages as $slug => $data ) {
			if ( isset($data['default_locale']) ) {
				$map[$slug] = $data['default_locale'];
			}
		}
		return $map;
	}
}
