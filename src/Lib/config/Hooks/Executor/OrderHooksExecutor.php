<?php

namespace Ilabs\Inpost_Pay\Lib\config\Hooks\Executor;

use Ilabs\Inpost_Pay\Lib\config\Hooks\OrderHooksConfig;
use WC_Order;

/**
 * Class OrderHooksExecutor
 *
 * Responsible for triggering selected WooCommerce checkout-related actions,
 * based on settings saved in the admin panel.
 *
 * @package Ilabs\Inpost_Pay\Lib\config\Hooks\Executor
 */
final class OrderHooksExecutor {
	/**
	 * @var OrderHooksConfig
	 */
	private OrderHooksConfig $config;

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
		$this->config        = new OrderHooksConfig();
		$this->enabled_hooks = (array) $this->config->get( [] );
	}

	/**
	 * Triggers the WooCommerce checkout-related actions based on user settings.
	 *
	 * @param WC_Order $order WooCommerce order object.
	 * @param int $order_id ID of the WooCommerce order.
	 * @param array $data Optional data passed to the actions (e.g. checkout data).
	 *
	 * @return void
	 */
	public function trigger_checkout_hooks( WC_Order $order, int $order_id, array $data = [] ): void {
		$available_hooks = $this->config->get_hooks();

		foreach ( $available_hooks as $key => $label ) {
			if ( ! in_array( $key, $this->enabled_hooks, true ) ) {
				continue;
			}

			switch ( $key ) {
				case 'checkout_order_processed':
					do_action( 'woocommerce_checkout_order_processed', $order_id, $_POST, $order );
					break;

				case 'checkout_create_order':
					do_action( 'woocommerce_checkout_create_order', $order, $data );
					break;

				case 'checkout_update_order_meta':
					do_action( 'woocommerce_checkout_update_order_meta', $order_id, $data );
					break;

				case 'checkout_order_created':
					do_action( 'woocommerce_checkout_order_created', $order );
					break;
			}
		}
	}
}
