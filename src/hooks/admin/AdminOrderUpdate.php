<?php
/**
 * Admin Order Update Hook
 *
 * This file contains the AdminOrderUpdate class which handles order update events
 * in the WordPress admin area for InPost Pay integration.
 *
 * @package Ilabs\Inpost_Pay\hooks\admin
 */

namespace Ilabs\Inpost_Pay\hooks\admin;

use Ilabs\Inpost_Pay\hooks\Base;
use Ilabs\Inpost_Pay\Lib\helpers\HPOSHelper;
use Ilabs\Inpost_Pay\Logger;
use Ilabs\Inpost_Pay\WooCommerce\WooCommerceInPostIzi;

/**
 * Admin Order Update Hook Class
 *
 * Handles order update events in the WordPress admin area for InPost Pay integration.
 * This class manages tracking ID updates and order status changes, sending events
 * to the InPost Izi service when appropriate.
 *
 * @package Ilabs\Inpost_Pay\hooks\admin
 */
class AdminOrderUpdate extends Base {

	/**
	 * Block flag to prevent infinite loops.
	 *
	 * @var bool $block
	 */
	public static $block = false;

	/**
	 * Attaches hooks for managing order and cart events related to Inpost payment system.
	 *
	 * @return void
	 */
	public function attach_hook(): void {
		$hook = static function ( $id, $meta_key, $tracking_id ) {
			if ( self::$block ) {
				return;
			}
			if ( '_easypack_parcel_tracking' !== $meta_key || ! $tracking_id ) {
				return;
			}
			$hpos_helper      = new HPOSHelper( $id );
			$izi_payment_type = $hpos_helper->get_meta( 'izi_payment_type', true );
			if ( ! $izi_payment_type ) {
				return;
			}

			$data = $hpos_helper->get_meta( 'inpost_consents', true );
			if ( ! $data ) {
				return;
			}

			$izi = WooCommerceInPostIzi::get_instance();

			$order         = wc_get_order( $id );
			$status        = $order->get_status();
			$status_labels = get_option( 'izi_status_map' );
			$status        = ( ! empty( $status_labels[ 'wc-' . $status ] ) ) ? $status_labels[ 'wc-' . $status ] : $status;

			$ref_list = array( $tracking_id );
			$izi->get_controller()->send_order_event( $id, $status, $ref_list );
		};

		$change_status_hook = function ( $id, $status_transition_from, $status_transition_to ) {
			if ( self::$block ) {
				return;
			}
			$hpos_helper      = new HPOSHelper( $id );
			$izi_payment_type = $hpos_helper->get_meta( 'izi_payment_type', true );
			if ( ! $izi_payment_type ) {
				return;
			}
			$izi = WooCommerceInPostIzi::get_instance();

			$status_labels = get_option( 'izi_status_map' );
			$status        = ( ! empty( $status_labels[ 'wc-' . $status_transition_to ] ) ) ? $status_labels[ 'wc-' . $status_transition_to ] : $status_transition_to;
			$order_status  = '';

			$order_payment_status = $hpos_helper->get_meta( 'izi_payment_status', true );
			$izi_order_status     = $hpos_helper->get_meta( 'izi_order_status', true );
			if ( 'AUTHORIZED' !== $order_payment_status && 'ORDER_COMPLETED' !== $izi_order_status ) {
				if ( ( 'pending' === $status_transition_from
						|| 'awaiting-payment' === $status_transition_from
						|| 'on-hold' === $status_transition_from )
					&& 'cancelled' === $status_transition_to
				) {
					$order_status = 'ORDER_REJECTED';

				}

				if ( 'completed' === $status_transition_to && 'ORDER_REJECTED' !== $izi_order_status ) {
					$order_status = 'ORDER_COMPLETED';
				}
			}

			if ( 'AUTHORIZED' === $order_payment_status && 'processing' === $status_transition_to &&
				in_array( $status_transition_from, array( 'pending', 'awaiting-payment', 'on-hold' ), true ) ) {
				$order_status = 'ORDER_PROCESSING';
			}
			$hpos_helper->update_meta( 'izi_order_status', $order_status );

			$tracking_id = $hpos_helper->get_meta( '_easypack_parcel_tracking', true ) ? $hpos_helper->get_meta( '_easypack_parcel_tracking', true ) : '';
			$ref_list    = array( $tracking_id );

			Logger::log( 'Order status changed from ' . $status_transition_from . ' to ' . $status_transition_to . ', technical order status: ' . $order_status );

			$izi->get_controller()->send_order_event( $id, $status, $ref_list, $order_status );
		};

		add_action( 'add_post_meta', $hook, 1, 4 );
		add_action( 'woocommerce_order_status_changed', $change_status_hook, 1, 4 );

		add_action(
			'woocommerce_cart_loaded_from_session',
			static function ( $cart ) {
				if ( did_action( 'inpost_pay_order_created' ) && count( (array) $cart->get_cart() ) > 0 ) {
					Logger::log( 'Cart rehydrated after order creation' );
				}
			},
			1
		);
	}
}
