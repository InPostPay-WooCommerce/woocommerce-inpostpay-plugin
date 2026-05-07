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
				if ( ! is_array( $args ) || ! array_key_exists( 'cost', $args ) ) {
					return $args;
				}

				$full_cost = is_array( $args['cost'] ) ? array_sum( $args['cost'] ) : $args['cost'];

				if ( ! is_numeric( $full_cost ) ) {
					return $args;
				}

				if ( ! isset( $args['meta_data'] ) || ! is_array( $args['meta_data'] ) ) {
					$args['meta_data'] = array();
				}

				$args['meta_data']['izi_full_cost'] = (float) $full_cost;

				return $args;
			},
			10,
			2
		);
	}
}
