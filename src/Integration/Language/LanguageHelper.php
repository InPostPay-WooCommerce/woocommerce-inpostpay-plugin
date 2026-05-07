<?php

declare(strict_types=1);

namespace Ilabs\Inpost_Pay\Integration\Language;

class LanguageHelper {

	private static ?LanguageProviderInterface $provider = null;

	public static function getProvider(): ?LanguageProviderInterface {
		if ( self::$provider !== null ) {
			return self::$provider;
		}

		$wpml = new WpmlLanguageProvider();
		$pll  = new PolylangLanguageProvider();

		$wpmlActive = $wpml->isActive();
		$pllActive  = $pll->isActive();

		if ( $wpmlActive ) {
			self::$provider = $wpml;
			return $wpml;
		}

		if ( $pllActive ) {
			self::$provider = $pll;
			return $pll;
		}

		return null;
	}

	public static function isLanguageSystemActive(): bool {
		return self::getProvider() !== null;
	}

	public static function getCurrentSlug(): ?string {
		$provider = self::getProvider();
		return $provider ? $provider->getCurrentSlug() : null;
	}

	public static function getCurrentLocale(): ?string {
		$provider = self::getProvider();
		return $provider ? $provider->getCurrentLocale() : null;
	}

	public static function getAvailableSlugs(): array {
		$provider = self::getProvider();
		return $provider ? $provider->getAvailableSlugs() : array();
	}

	public static function getAvailableLocales(): array {
		$provider = self::getProvider();
		return $provider ? $provider->getAvailableLocales() : array();
	}

	public static function getSlugToLocaleMap(): array {
		$provider = self::getProvider();
		return $provider ? $provider->getSlugToLocaleMap() : array();
	}

	public static function getDefaultSlug(): ?string {
		$provider = self::getProvider();
		return $provider ? $provider->getDefaultSlug() : null;
	}
}
