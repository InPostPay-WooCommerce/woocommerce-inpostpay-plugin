<?php
/**
 * Shipping mapping field interface.
 *
 * @package Ilabs\Inpost_Pay\Lib\config\ShippingCost
 */

declare( strict_types=1 );

namespace Ilabs\Inpost_Pay\Lib\config\ShippingCost;

/**
 * Interface ShippingMappingFieldInterface
 *
 * Marks a field as belonging to a shipping mapping configuration group.
 */
interface ShippingMappingFieldInterface {

	/**
	 * Returns the delivery option code for this field.
	 *
	 * @return string
	 */
	public function get_delivery_option_code(): string;
}
