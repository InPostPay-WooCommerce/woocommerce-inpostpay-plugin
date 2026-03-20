<?php

namespace Ilabs\Inpost_Pay\Lib\config\Hooks;

final class CartHooksConfig extends AbstractHookConfig {

	const OPTION_NAME = 'izi_enabled_hooks_cart';

	protected array $available_hooks = [
		'cart_item_set_quantity' => 'woocommerce_cart_item_set_quantity',
	];

	public function __construct() {
		parent::__construct(
			self::OPTION_NAME,
			 'Additional WooCommerce Cart Actions',
			'Enable WooCommerce cart-related actions if needed (e.g. for custom integration).'
		);
	}
}
