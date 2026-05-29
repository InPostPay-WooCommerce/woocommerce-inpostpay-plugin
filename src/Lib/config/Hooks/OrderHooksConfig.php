<?php
/**
 * Order hooks configuration option.
 *
 * @package Ilabs\Inpost_Pay\Lib\config\Hooks
 */

declare( strict_types=1 );

namespace Ilabs\Inpost_Pay\Lib\config\Hooks;

/**
 * Class OrderHooksConfig
 *
 * WordPress option controlling which WooCommerce checkout/order-related hooks are enabled.
 */
final class OrderHooksConfig extends AbstractHookConfig {

	const OPTION_NAME = 'izi_enabled_hooks_order';

	protected array $available_hooks = array(
		'checkout_order_processed'   => 'woocommerce_checkout_order_processed',
		'checkout_create_order'      => 'woocommerce_checkout_create_order',
		'checkout_update_order_meta' => 'woocommerce_checkout_update_order_meta',
		'checkout_order_created'     => 'woocommerce_checkout_order_created',
	);

	/**
	 * Constructor.
	 */
	public function __construct() {
		parent::__construct(
			self::OPTION_NAME,
			'Additional WooCommerce Order Actions',
			'Enable WooCommerce checkout-related actions if needed (e.g. for plugins like SalesKing).'
		);
	}
}
