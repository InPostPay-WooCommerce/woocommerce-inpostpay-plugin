<?php
/**
 * Cart hooks configuration option.
 *
 * @package Ilabs\Inpost_Pay\Lib\config\Hooks
 */

declare( strict_types=1 );

namespace Ilabs\Inpost_Pay\Lib\config\Hooks;

/**
 * Class CartHooksConfig
 *
 * WordPress option controlling which WooCommerce cart-related hooks are enabled.
 */
final class CartHooksConfig extends AbstractHookConfig {

	const OPTION_NAME = 'izi_enabled_hooks_cart';

	protected array $available_hooks = array(
		'cart_item_set_quantity' => 'woocommerce_cart_item_set_quantity',
	);

	/**
	 * Constructor.
	 */
	public function __construct() {
		parent::__construct(
			self::OPTION_NAME,
			'Additional WooCommerce Cart Actions',
			'Enable WooCommerce cart-related actions if needed (e.g. for custom integration).'
		);
	}
}
