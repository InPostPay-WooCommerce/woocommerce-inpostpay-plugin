<?php

namespace Ilabs\Inpost_Pay\Lib\config\Hooks;

final class OrderHooksConfig extends AbstractHookConfig {

	const OPTION_NAME = 'izi_enabled_hooks_order';

	protected array $available_hooks = array(
		'checkout_order_processed'   => 'woocommerce_checkout_order_processed',
		'checkout_create_order'      => 'woocommerce_checkout_create_order',
		'checkout_update_order_meta' => 'woocommerce_checkout_update_order_meta',
		'checkout_order_created'     => 'woocommerce_checkout_order_created',
	);

	public function __construct() {
		parent::__construct(
			self::OPTION_NAME,
			'Additional WooCommerce Order Actions',
			'Enable WooCommerce checkout-related actions if needed (e.g. for plugins like SalesKing).'
		);
	}
}
