<?php
/**
 * Delivery full cost filter.
 *
 * @package InpostPay
 * @since 2.0.6
 */

namespace Ilabs\Inpost_Pay\filters;

class Delivery_Full_Cost_Filter extends Base {

	/**
	 * Register filters for the shipping method full cost.
	 *
	 * @since 2.0.6
	 */
	public function register_filters(): void {
		if ( wp_doing_cron() ) {
			return;
		}
		add_filter(
			'woocommerce_shipping_method_add_rate_args',
			static function ( $args, $shipping_method ) {
				$full_cost                          = $args['cost'];
				$args['meta_data']['izi_full_cost'] = $full_cost;
				return $args;
			},
			10,
			2
		);
	}
}
