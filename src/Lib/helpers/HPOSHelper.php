<?php

namespace Ilabs\Inpost_Pay\Lib\helpers;

use Automattic\WooCommerce\Utilities\OrderUtil;
use WC_Order;
use WC_Order_Factory;

class HPOSHelper {
	private bool $HPOSEnabled = false;

	private ?WC_Order $order = null;

	private ?int $order_id = null;

	public function __construct( $order ) {
		if ( OrderUtil::custom_orders_table_usage_is_enabled() ) {
			$this->HPOSEnabled = true;
		}

		if ( $order instanceof WC_Order ) {
			$this->order    = $order;
			$this->order_id = $order->get_id();
		} elseif ( $order && ! is_bool( $order ) ) {
			$this->order    = WC_Order_Factory::get_order( $order );
			$this->order_id = $order;
		}

	}

	public function get_meta( $key, $single = true ) {
		if ( $this->HPOSEnabled ) {
			return $this->order->get_meta( $key );
		}

		return get_post_meta( $this->order_id, $key, $single );
	}

	public function update_meta( $key, $value ): void {
		$this->order->update_meta_data( $key, $value );
	}
}
