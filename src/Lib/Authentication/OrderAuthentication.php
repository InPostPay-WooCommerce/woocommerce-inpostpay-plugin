<?php

namespace Ilabs\Inpost_Pay\Lib\Authentication;

use Automattic\WooCommerce\Utilities\OrderUtil;
use Ilabs\Inpost_Pay\Lib\exception\EmptyCredentialsForOrderAuthenticationException;
use Ilabs\Inpost_Pay\Lib\exception\UserNotFoundException;
use Ilabs\Inpost_Pay\Lib\helpers\HPOSHelper;

class OrderAuthentication implements AuthenticationInterface {


	public function __construct() {
	}

	/**
	 * @throws EmptyCredentialsForOrderAuthenticationException
	 * @throws UserNotFoundException
	 */
	public function authenticate( Credentials $credentials ): ?\WP_User {
		if ( $credentials->get_email() === null && $credentials->get_phone_number() === null ) {
			throw new EmptyCredentialsForOrderAuthenticationException();
		}

		$user = $this->find_user_by_email_or_phone( $credentials );

		if ( $user === null || $user === false ) {
			throw new UserNotFoundException();
		}

		return $user;
	}


	protected function find_user_by_email_or_phone( Credentials $credentials ) {
		$user = null;
		// Try to find user by email
		if ( is_email( $credentials->get_email() ) ) {
			$user = get_user_by( 'email', $credentials->get_email() );
		}

		if ( $user === null || $user === false ) {
			// Try to find user by phone number
			$user = $this->get_user_by_phone( $credentials->get_phone_number() );
		}

		return $user;
	}

	protected function get_user_by_phone( $phone ) {
		// Assuming phone numbers are stored as a user meta field called 'phone'
		$users = get_users(
			array(
				'meta_key'   => 'billing_phone',
				'meta_value' => $phone,
				'number'     => 1,  // We only need the first match
			)
		);

		if ( ! empty( $users ) ) {
			return $users[0];
		}

		return $this->find_user_by_phone_sql( $phone );
	}


	protected function find_user_by_phone_sql( $phone ): ?\WP_User {
		global $wpdb;

		// Sanitize and normalize the phone number (remove spaces, dashes, etc.)
		$normalized_phone = preg_replace( '/\D+/', '', $phone );

		if ( OrderUtil::custom_orders_table_usage_is_enabled() ) {
			// Prepare query when HPOS is enabled
			$query = $wpdb->prepare(
				"
                SELECT DISTINCT om.meta_value AS customer_id
                FROM {$wpdb->prefix}wc_orders o
                JOIN {$wpdb->prefix}wc_orders_meta om ON o.id = om.order_id
                JOIN {$wpdb->prefix}wc_orders_meta bm ON o.id = bm.order_id
                WHERE o.status IN ('wc-completed', 'wc-processing', 'wc-on-hold')
                AND om.meta_key = '_customer_user'
                AND bm.meta_key = '_billing_phone'
                AND REPLACE(REPLACE(bm.meta_value, '-', ''), ' ', '') LIKE %s
            ",
				'%' . $wpdb->esc_like( $normalized_phone ) . '%'
			);
		} else {
			// Prepare the query
			$query = $wpdb->prepare(
				"
				SELECT pm1.meta_value AS customer_id
				FROM {$wpdb->prefix}postmeta pm1
				JOIN {$wpdb->prefix}posts p ON p.ID = pm1.post_id
				WHERE p.post_type = 'shop_order'
				AND p.post_status IN ('wc-completed', 'wc-processing', 'wc-on-hold')
				AND pm1.meta_key = '_customer_user'
				AND p.ID IN (
					SELECT post_id
					FROM {$wpdb->prefix}postmeta
					WHERE meta_key = '_billing_phone'
					AND REPLACE(REPLACE(meta_value, '-', ''), ' ', '') LIKE %s
				)
			",
				'%' . $wpdb->esc_like( $normalized_phone ) . '%'
			);
		}

		// Execute the query
		$customer_ids = $wpdb->get_col( $query );

		$customer_ids = array_unique( array_filter( $customer_ids ) );

		if ( $customer_ids ) {
			return $this->get_user_by_id( reset( $customer_ids ) );
		}

		return null;
	}

	function get_user_by_id( $user_id ): ?\WP_User {
		// Retrieve the user data by ID
		$user = get_userdata( $user_id );

		// Check if the user exists
		if ( $user ) {
			return $user;
		} else {
			return null;
		}
	}
}
