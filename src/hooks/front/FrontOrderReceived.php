<?php
/**
 * Front Order Received Hook.
 *
 * @package InpostPay
 * @subpackage Hooks/Front
 */

namespace Ilabs\Inpost_Pay\hooks\front;

use Ilabs\Inpost_Pay\Lib\BasketIdentification;
use Ilabs\Inpost_Pay\Lib\BindingProvider;
use Ilabs\Inpost_Pay\InpostPay;
use Ilabs\Inpost_Pay\Lib\helpers\HPOSHelper;
use Ilabs\Inpost_Pay\Lib\helpers\LSCacheHelper;
use Ilabs\Inpost_Pay\Logger;

/**
 * Class FrontOrderReceived
 *
 * Handles order received page functionality.
 */
class FrontOrderReceived extends FrontBase {
	/**
	 * Attach hooks.
	 *
	 * @return void
	 */
	public function attach_hook() {
		add_filter( 'woocommerce_thankyou_order_received_text', array( $this, 'text' ), 20, 2 );
		add_filter( 'woocommerce_locate_template', array( $this, 'custom_thankyou_page_template' ), 10, 1 );
	}

	/**
	 * Filter thank you text.
	 *
	 * @param mixed $fields Fields.
	 * @param mixed $order  Order.
	 *
	 * @return mixed Modified fields.
	 */
	public function text( $fields, $order ) {
		if ( is_order_received_page() ) {
			$basket_id = BasketIdentification::get();
			$is_bound  = BindingProvider::getBinding();

			Logger::log(
				sprintf(
					'[OrderReceived] basket_id=%s is_bound=%s',
					$basket_id ?: '(empty)',
					$is_bound ? 'true' : 'false'
				)
			);

			BasketIdentification::drop();

			if ( ! empty( $basket_id ) && $is_bound ) {
				Logger::log( '[OrderReceived] Sending DELETE binding.' );
				InpostPay::get_instance()->get_lib()->get_controller()->basket_binding_delete();
			}
		}

		return $fields;
	}

	/**
	 * Custom thank you page template.
	 *
	 * @param string $template Template path.
	 *
	 * @return string Modified template path.
	 */
	public function custom_thankyou_page_template( $template ) {
		global $wp;

		if ( is_order_received_page() && ( strpos( $template, 'order-received.php' ) || strpos( $template, 'thankyou.php' ) ) ) {
			BasketIdentification::drop();
			$order_id = absint( $wp->query_vars['order-received'] );

			$order = wc_get_order( $order_id );
			if ( $order && ! is_wp_error( $order ) ) {
				$data = ( new HPOSHelper( $order_id ) )->get_meta( 'inpost_consents' );
				if ( $data ) {

					// Add order attribution data.
					$attribution_data = $this->process_attribution_data( $_COOKIE );
					do_action( 'woocommerce_order_save_attribution_data', $order, $attribution_data );

					LSCacheHelper::set_private_cache();
					if ( isset( $_COOKIE['izi_basket_id'] ) ) {
						unset( $_COOKIE['izi_basket_id'] );
					}
					$new_template = plugin_dir_path( __FILE__ ) . '../views/thankyou-page.php';
					if ( file_exists( $new_template ) ) {
						return $new_template;
					}
				}
			}
		}

		return $template;
	}


	/**
	 * Process attribution data.
	 *
	 * @param array $input_array Input array.
	 *
	 * @return array Processed attribution data.
	 */
	public function process_attribution_data( $input_array ) {
		$result          = array();
		$result_prefixed = array();
		$required_keys   = array( 'sbjs_current_add', 'sbjs_current', 'sbjs_udata', 'sbjs_session' );

		// Based on Woo trait OrderAttributionMeta.
		$mapping = array(
			'current.typ'    => 'source_type',
			'current_add.rf' => 'referrer',
			'current.cmp'    => 'utm_campaign',
			'current.src'    => 'utm_source',
			'current.mdm'    => 'utm_medium',
			'current.cnt'    => 'utm_content',
			'current.id'     => 'utm_id',
			'current.trm'    => 'utm_term',
			'current.plt'    => 'utm_source_platform',
			'current.fmt'    => 'utm_creative_format',
			'current.tct'    => 'utm_marketing_tactic',
			'current_add.ep' => 'session_entry',
			'current_add.fd' => 'session_start_time',
			'session.pgs'    => 'session_pages',
			'udata.vst'      => 'session_count',
			'udata.uag'      => 'user_agent',
		);

		foreach ( $required_keys as $key ) {
			if ( ! isset( $input_array[ $key ] ) ) {
				return $result;
			}
		}

		foreach ( $mapping as $source => $target ) {
			list( $main_key, $sub_key ) = explode( '.', $source );
			$main_key                   = 'sbjs_' . $main_key;

			if ( isset( $input_array[ $main_key ] ) ) {
				$parts = explode( '|||', $input_array[ $main_key ] );
				foreach ( $parts as $part ) {
					list( $key, $value ) = explode( '=', $part );
					if ( $key === $sub_key ) {
						$result_prefixed[ 'wc_order_attribution_' . $target ] = $value;
						$result[ $target ]                                    = $value;
						break;
					}
				}
			}
		}

		// Rewrite some values for Inpost Pay.
		$result['utm_source']  = 'InPost Pay';
		$result['device_type'] = 'Mobile';
		$result['utm_medium']  = 'Aplikacja (InPost Mobile)';

		return $result;
	}
}
