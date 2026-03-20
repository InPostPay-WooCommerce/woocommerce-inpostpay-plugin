<?php

namespace Ilabs\Inpost_Pay\Lib\helpers;

class LangHelper {

	private static $widgetLangAttr = null;

	private static function getPolylangLanguageCode(): ?string {
		if ( function_exists( 'pll_current_language' )
		     && function_exists( 'pll_default_language' ) ) {
			$language = \pll_current_language( 'slug' );

			if ( $language === false ) {
				$language = \pll_default_language( 'slug' );
			}

			return $language;
		}

		return null;
	}

	private static function getWpmlLanguageCode(): ?string {
		if ( function_exists( 'icl_object_id' ) ) {
			global $sitepress;

			return $sitepress->get_current_language();
		}

		return null;

	}

	private static function getWidgetLangAttrByLangCode( string $code
	): string {
		$languagesAttributes = [
			'pl'    => 'pl',
			'en'    => 'en',
			'pl_PL' => 'pl',
			'en_EN' => 'en',
		];

		if ( key_exists( $code, $languagesAttributes ) ) {
			return $languagesAttributes[ $code ];
		}

		return 'en';
	}

	public static function getWidgetLangAttr(): string {
		if ( self::$widgetLangAttr ) {
			return self::$widgetLangAttr;
		}

		$language = self::getPolylangLanguageCode();
		if ( ! $language ) {
			$language = self::getWpmlLanguageCode();
		}

		if ( ! $language ) {
			$language = get_locale();
		}

		$language             = $language ?: 'en';
		self::$widgetLangAttr = self::getWidgetLangAttrByLangCode( $language );

		return self::$widgetLangAttr;
	}
}
