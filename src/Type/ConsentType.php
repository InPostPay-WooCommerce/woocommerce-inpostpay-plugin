<?php
/**
 * Consent type helper class.
 *
 * @package Inpost_Pay
 */

namespace Ilabs\Inpost_Pay\Type;

/**
 * Class ConsentType
 *
 * Provides label mapping for consent types.
 */
class ConsentType {

	/**
	 * Consent is always required.
	 *
	 * @var string
	 */
	public const REQUIRED_ALWAYS = 'REQUIRED_ALWAYS';

	/**
	 * Consent is required only once.
	 *
	 * @var string
	 */
	public const REQUIRED_ONCE = 'REQUIRED_ONCE';

	/**
	 * Consent is optional.
	 *
	 * @var string
	 */
	public const OPTIONAL = 'OPTIONAL';

	/**
	 * Returns a map of all consent types and their translated labels.
	 *
	 * @return array<string,string> Consent type => label.
	 */
	public static function all(): array {
		return array(
			self::OPTIONAL        => __( 'Optional', 'inpost-pay' ),
			self::REQUIRED_ONCE   => __( 'Required once', 'inpost-pay' ),
			self::REQUIRED_ALWAYS => __( 'Required always', 'inpost-pay' ),
		);
	}

	/**
	 * Returns the translated label for a given consent type.
	 *
	 * @param string $type Consent type value.
	 *
	 * @return string Translated consent label.
	 */
	public static function get_label( string $type ): string {
		$labels = self::all();

		return $labels[ $type ] ?? '';
	}
}
