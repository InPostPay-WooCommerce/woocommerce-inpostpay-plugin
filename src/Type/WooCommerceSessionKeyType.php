<?php
/**
 * WooCommerce session key type helper class.
 *
 * @package Inpost_Pay
 */

declare( strict_types=1 );

namespace Ilabs\Inpost_Pay\Type;

/**
 * Class WooCommerceSessionKeyType
 *
 * Provides WooCommerce session key constants used by the plugin.
 */
class WooCommerceSessionKeyType {

	/**
	 * InPost cart hash session key.
	 *
	 * @var string
	 */
	public const INPOST_CART_HASH = 'inpost_cart_hash';
}
