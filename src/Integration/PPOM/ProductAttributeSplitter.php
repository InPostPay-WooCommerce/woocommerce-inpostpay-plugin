<?php
/**
 * Product Attribute Splitter
 *
 * Splits multi-value product attributes (e.g. PPOM options joined with a comma)
 * into individual ProductAttribute records — one per option.
 *
 * @package Ilabs\Inpost_Pay\Integration\PPOM
 */

declare( strict_types=1 );

namespace Ilabs\Inpost_Pay\Integration\PPOM;

use Ilabs\Inpost_Pay\Lib\item\ProductAttribute;

/**
 * Class ProductAttributeSplitter
 *
 * When PPOM combines multiple selected options into a single comma-separated
 * attribute value, this class explodes them so each option is sent as its own
 * ProductAttribute record to the InPost Pay API.
 */
final class ProductAttributeSplitter {

	/**
	 * Separator used between individual options in the attribute value.
	 *
	 * @var string
	 */
	private string $separator;

	/**
	 * @param string $separator Separator between options (default: ', ').
	 */
	public function __construct( string $separator = ', ' ) {
		$this->separator = $separator;
	}

	/**
	 * Process an array of ProductAttribute objects.
	 *
	 * Attributes whose value contains multiple options (separated by the separator)
	 * are exploded into individual ProductAttribute objects, each keeping the
	 * original attribute_name. Single-value attributes pass through unchanged.
	 *
	 * @param ProductAttribute[] $attributes Array of ProductAttribute objects.
	 *
	 * @return ProductAttribute[] Processed array, potentially with more elements.
	 */
	public function process( array $attributes ): array {
		$result = array();

		foreach ( $attributes as $attribute ) {
			$name  = rtrim( $attribute->attribute_name, ': ' );
			$value = $attribute->attribute_value;

			$options = array_map( 'trim', explode( $this->separator, $value ) );
			$options = array_filter(
				$options,
				static function ( $opt ) {
					return '' !== $opt;
				}
			);

			if ( count( $options ) <= 1 ) {
				$result[] = new ProductAttribute( $name, html_entity_decode( $value, ENT_QUOTES | ENT_HTML5, 'UTF-8' ) );
				continue;
			}

			foreach ( $options as $option ) {
				$result[] = new ProductAttribute( $name, html_entity_decode( $option, ENT_QUOTES | ENT_HTML5, 'UTF-8' ) );
			}
		}

		return $result;
	}
}
