<?php
/**
 * Product attribute item.
 *
 * @package InpostPay
 */

declare( strict_types=1 );

namespace Ilabs\Inpost_Pay\Lib\item;

use Ilabs\Inpost_Pay\Lib\helpers\JsonSerializationHelper;
use Ilabs\Inpost_Pay\Lib\Item;
use JsonSerializable;

/**
 * Represents a single product attribute with name and value.
 */
class ProductAttribute extends Item implements JsonSerializable {
	use JsonSerializationHelper;

	protected string $attribute_name;

	protected string $attribute_value;

	/**
	 * Constructor.
	 *
	 * @param mixed $attribute_name  Attribute name.
	 * @param mixed $attribute_value Attribute value.
	 */
	public function __construct( $attribute_name, $attribute_value ) {
		$label                 = wc_attribute_label( $attribute_name );
		$this->attribute_name  = (string) ( $label ? $label : $attribute_name );
		$this->attribute_value = is_scalar( $attribute_value ) ? wp_strip_all_tags( (string) $attribute_value ) : '';

		if ( '' === $this->attribute_name && '' !== $this->attribute_value ) {
			$this->attribute_name = 'O';
		}
	}

	/**
	 * Serialize item to JSON.
	 *
	 * @return array<string, mixed>
	 */
	public function jsonSerialize(): array {
		return $this->auto_serialize();
	}
}
