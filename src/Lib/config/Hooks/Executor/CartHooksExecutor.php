<?php

namespace Ilabs\Inpost_Pay\Lib\config\Hooks\Executor;

use Ilabs\Inpost_Pay\Lib\config\Hooks\CartHooksConfig;
use Ilabs\Inpost_Pay\Logger;

/**
 * Class OrderHooksExecutor
 *
 * Responsible for triggering selected WooCommerce checkout-related actions,
 * based on settings saved in the admin panel.
 *
 * @package Ilabs\Inpost_Pay\Lib\config\Hooks\Executor
 */
final class CartHooksExecutor {
	/**
	 * @var CartHooksConfig
	 */
	private CartHooksConfig $config;

	/**
	 * @var array Enabled hook keys from the configuration (e.g. 'checkout_order_created')
	 */
	private array $enabled_hooks;

	/**
	 * OrderHooksExecutor constructor.
	 *
	 * Loads the enabled hooks from the settings.
	 */
	public function __construct() {
		$this->config        = new CartHooksConfig();
		$this->enabled_hooks = (array) $this->config->get( [] );
	}

	public function add_callable_hook( string $hook_key, callable $callback ): void {
		if ( ! in_array( $hook_key, $this->enabled_hooks, true ) ) {
			return;
		}

		$available_hooks = $this->config->get_hooks();

		if ( ! isset( $available_hooks[ $hook_key ] ) ) {
			return;
		}

		$hook_name = $available_hooks[ $hook_key ];

		add_action( $hook_name, $callback );
	}
}
