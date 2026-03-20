<?php
/**
 * Analytics functionality for InPost Pay.
 *
 * This file contains the Analytics class responsible for storing and retrieving
 * analytics data from order meta and session, including client ID, fbclid, and gclid.
 *
 * @package Ilabs\Inpost_Pay\Lib\Analytics
 * @since 2.0.2
 */

namespace Ilabs\Inpost_Pay\Lib\Analytics;

use Ilabs\Inpost_Pay\Lib\BasketIdentification;
use Ilabs\Inpost_Pay\Lib\config\analytics\AnalyticsConfig;
use Ilabs\Inpost_Pay\Lib\helpers\HPOSHelper;
use Ilabs\Inpost_Pay\Lib\item\order\OrderAdditionalParameter;
use Ilabs\Inpost_Pay\models\CartSessionService;
use function Ilabs\Inpost_Pay\inpost_pay_container;

/**
 * Class responsible for storing and retrieving analytics data from order meta and session
 */
class Analytics {

	protected ?string $client_id = null;

	protected ?string $fbclid = null;

	protected ?string $gclid = null;

	private CartSessionService $cart_session;

	/**
	 * Constructor.
	 *
	 * Initializes the Analytics class by getting the CartSessionService from the dependency injection container.
	 */
	public function __construct() {
		/**
		 * Get from container DI.
		 *
		 * @var CartSessionService $cart_session
		 */
		$this->cart_session = inpost_pay_container()->get( CartSessionService::SERVICE_KEY );
	}

	/**
	 * Stores analytics data from $_POST
	 */
	public function store_from_post(): void {
		$analytics_is_enabled = ( new AnalyticsConfig() )->is_enabled();
		// phpcs:ignore WordPress.Security.NonceVerification.Missing
		if ( ! empty( $_POST ) && $analytics_is_enabled ) {
			// phpcs:ignore WordPress.Security.NonceVerification.Missing
			if ( isset( $_POST['inpostpay_client_id'] ) ) {
				// phpcs:ignore WordPress.Security.NonceVerification.Missing
				$this->client_id = sanitize_text_field( wp_unslash( $_POST['inpostpay_client_id'] ) );
			}
			// phpcs:ignore WordPress.Security.NonceVerification.Missing
			if ( isset( $_POST['inpostpay_fbclid'] ) ) {
				// phpcs:ignore WordPress.Security.NonceVerification.Missing
				$this->fbclid = sanitize_text_field( wp_unslash( $_POST['inpostpay_fbclid'] ) );
			}
			// phpcs:ignore WordPress.Security.NonceVerification.Missing
			if ( isset( $_POST['inpostpay_gclid'] ) ) {
				// phpcs:ignore WordPress.Security.NonceVerification.Missing
				$this->gclid = sanitize_text_field( wp_unslash( $_POST['inpostpay_gclid'] ) );
			}

			$this->cart_session->store_analytics( BasketIdentification::get(), $this->to_array() );
		}
	}

	/**
	 * Converts analytics data to array
	 *
	 * This function converts the analytics data to an array with the keys 'client_id', 'fbclid', and 'gclid'.
	 * The values are the corresponding analytics data.
	 *
	 * @return array The analytics data as an array.
	 */
	public function to_array(): array {
		return array(
			'client_id' => $this->client_id,
			'fbclid'    => $this->fbclid,
			'gclid'     => $this->gclid,
		);
	}

	/**
	 * Stores analytics data in the order meta.
	 *
	 * This function retrieves analytics data from the session using the provided
	 * basket ID, and then updates the order metadata with the client ID, fbclid,
	 * and gclid.
	 *
	 * @param mixed      $order The order object or order id to update with analytics data.
	 * @param mixed|null $basket_id Optional. The basket ID used to retrieve analytics data.
	 */
	public function store_in_order_meta( $order, $basket_id = null ): void {
		$this->get_from_storage( $basket_id );
		( new HPOSHelper( $order ) )->update_meta( '_inpost_pay_client_id', $this->client_id );
		( new HPOSHelper( $order ) )->update_meta( '_inpost_pay_fbclid', $this->fbclid );
		( new HPOSHelper( $order ) )->update_meta( '_inpost_pay_gclid', $this->gclid );
	}

	/**
	 * Get analytics data from session
	 *
	 * @param string|null $basket_id Optional. The basket ID used to retrieve analytics data.
	 *
	 * @return Analytics The current instance of the Analytics class with updated properties.
	 */
	public function get_from_storage( string $basket_id = null ): self {
		if ( ! $basket_id ) {
			$basket_id = BasketIdentification::get();
		}
		$analytics = $this->cart_session->get_analytics( $basket_id );
		if ( ! empty( $analytics ) ) {
			$this->client_id = $analytics['client_id'];
			$this->fbclid    = $analytics['fbclid'];
			$this->gclid     = $analytics['gclid'];
		}

		return $this;
	}

	/**
	 * Converts analytics data to order additional parameters.
	 *
	 * This function converts the analytics data to an array of OrderAdditionalParameter objects.
	 * The objects contain the parameter name and value. The parameter names are 'client_id',
	 * 'fbclid', and 'gclid'. The values are the corresponding analytics data.
	 *
	 * @param mixed $order The order object or order id from which to retrieve analytics data.
	 *
	 * @return array An array of OrderAdditionalParameter objects.
	 */
	public function get_as_order_additional_parameters( $order ): array {
		$order_additional_parameters = array();
		$this->get_from_order_meta( $order );
		if ( $this->client_id ) {
			$order_additional_parameters[] = new OrderAdditionalParameter( 'client_id', $this->client_id );
		}
		if ( $this->fbclid ) {
			$order_additional_parameters[] = new OrderAdditionalParameter( 'fbclid', $this->fbclid );
		}
		if ( $this->gclid ) {
			$order_additional_parameters[] = new OrderAdditionalParameter( 'gclid', $this->gclid );
		}

		return $order_additional_parameters;
	}

	/**
	 * Retrieves analytics data from the order meta.
	 *
	 * This function uses the HPOSHelper to retrieve stored analytics data
	 * from the order meta, including client ID, fbclid, and gclid, and updates
	 * the current object's properties with these values.
	 *
	 * @param mixed $order The order object or order id from which to retrieve analytics data.
	 *
	 * @return $this The current instance of the Analytics class with updated properties.
	 */
	public function get_from_order_meta( $order ): Analytics {
		$hpos_helper = new HPOSHelper( $order );

		$this->client_id = $hpos_helper->get_meta( '_inpost_pay_client_id' );
		$this->fbclid    = $hpos_helper->get_meta( '_inpost_pay_fbclid' );
		$this->gclid     = $hpos_helper->get_meta( '_inpost_pay_gclid' );

		return $this;
	}


	/**
	 * Retrieves and returns the client ID from the class member.
	 *
	 * @return string The client ID stored in the class member.
	 */
	public function get_client_id(): string {
		return $this->client_id;
	}

	/**
	 * Retrieves the fbclid from the class instance.
	 *
	 * @return string The fbclid value stored in the class instance.
	 */
	public function get_fbclid(): string {
		return $this->fbclid;
	}

	/**
	 * Retrieves the Google Campaign Link ID (GCLID) from the class properties.
	 *
	 * @return string The Google Campaign Link ID stored in the class properties.
	 */
	public function get_gclid(): string {
		return $this->gclid;
	}
}
