<?php
/**
 * Cart hooks executor.
 *
 * @package Ilabs\Inpost_Pay\Lib\config\Hooks\Executor
 */

declare( strict_types=1 );

namespace Ilabs\Inpost_Pay\Lib\config\Hooks\Executor;

use Ilabs\Inpost_Pay\Lib\config\Hooks\CartHooksConfig;
use Ilabs\Inpost_Pay\Logger;

/**
 * Class CartHooksExecutor
 *
 * Responsible for conditionally registering WooCommerce cart-related actions
 * based on settings saved in the admin panel.
 *
 * @package Ilabs\Inpost_Pay\Lib\config\Hooks\Executor
 */
final class CartHooksExecutor {
	/**
	 * Cart hooks configuration instance.
	 *
	 * @var CartHooksConfig
	 */
	private CartHooksConfig $config;

	/**
	 * Enabled hook keys loaded from the configuration option.
	 *
	 * @var array
	 */
	private array $enabled_hooks;

	/**
	 * Constructor.
	 *
	 * Loads the enabled hooks from the settings.
	 */
	public function __construct() {
		$this->config        = new CartHooksConfig();
		$this->enabled_hooks = (array) $this->config->get( array() );
	}

	/**
	 * Registers a callback on the given hook key if it is enabled.
	 *
	 * @param string   $hook_key Hook key as defined in CartHooksConfig::$available_hooks.
	 * @param callable $callback Callback to attach via add_action().
	 *
	 * @return void
	 */
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
