<?php
/**
 * Utility class for resolving the correct email recipient for WooCommerce orders.
 *
 * @package Ilabs\Inpost_Pay\Lib\Utils
 */

namespace Ilabs\Inpost_Pay\Lib\Utils;

use WC_Order;

/**
 * Class OrderRecipientResolver
 *
 * Determines which email address should receive WooCommerce order notifications,
 * taking into account InPost Pay delivery, digital products, and user data.
 */
class OrderRecipientResolver {

	/**
	 * Resolves the email recipient for the given WooCommerce order.
	 *
	 * @param WC_Order $order        WooCommerce order instance.
	 * @param bool     $downloadable Whether the order contains downloadable products.
	 *
	 * @return string Email address to use as the recipient.
	 */
	public static function resolve_recipients( WC_Order $order, bool $downloadable = false ): string {
		$inpost_mail        = $order->get_meta( '_inpost_delivery_mail' );
		$original_email     = $order->get_meta( '_original_user_email' );
		$downloadable_email = $order->get_meta( 'inpost_pay_digital_delivery_email' );

		if ( true === $downloadable && is_email( $downloadable_email ) ) {
			return $downloadable_email;
		}

		if ( is_email( $inpost_mail ) ) {
			return $inpost_mail;
		}

		if ( is_email( $original_email ) ) {
			return $original_email;
		}

		return $order->get_billing_email();
	}
}
