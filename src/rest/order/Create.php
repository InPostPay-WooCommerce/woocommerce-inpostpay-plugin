<?php

namespace Ilabs\Inpost_Pay\rest\order;

use Exception;
use Ilabs\Inpost_Pay\hooks\admin\AdminOrderUpdate;
use Ilabs\Inpost_Pay\Integration\Basket\Availability\WpcBundleAvailabilityIntegration;
use Ilabs\Inpost_Pay\Integration\BLPaczka\BLPaczka_Integration;
use Ilabs\Inpost_Pay\Integration\Shipping\ShippingMethodIntegrationFactory;
use Ilabs\Inpost_Pay\Lib\Analytics\Analytics;
use Ilabs\Inpost_Pay\Lib\Attribution\OrderAttribution;
use Ilabs\Inpost_Pay\Lib\Authentication\AuthenticationFactory;
use Ilabs\Inpost_Pay\Lib\Authentication\Credentials;
use Ilabs\Inpost_Pay\Lib\config\Hooks\Executor\OrderHooksExecutor;
use Ilabs\Inpost_Pay\Lib\config\ShippingCost\GroupInterface;
use Ilabs\Inpost_Pay\Lib\exception\CantCreateAttribution;
use Ilabs\Inpost_Pay\Lib\exception\CantGetOrderObjectException;
use Ilabs\Inpost_Pay\Lib\exception\EmptyCredentialsForOrderAuthenticationException;
use Ilabs\Inpost_Pay\Lib\exception\InvalidAuthenticationType;
use Ilabs\Inpost_Pay\Lib\exception\JsonDecodeException;
use Ilabs\Inpost_Pay\Lib\exception\UserNotFoundException;
use Ilabs\Inpost_Pay\Lib\helpers\CacheHelper;
use Ilabs\Inpost_Pay\Lib\helpers\DigitalProduct;
use Ilabs\Inpost_Pay\Lib\InPostIzi;
use Ilabs\Inpost_Pay\Lib\item\Woo_Delivery_Price;
use Ilabs\Inpost_Pay\Lib\OrderAliasHelper;
use Ilabs\Inpost_Pay\Lib\Payment\Virtual_Payment_Method_Utils;
use Ilabs\Inpost_Pay\Logger;
use Ilabs\Inpost_Pay\models\CartSessionService;
use Ilabs\Inpost_Pay\models\DeliveryOptionHelper;
use Ilabs\Inpost_Pay\models\Destination;
use Ilabs\Inpost_Pay\Lib\Shipping\ProductDeliveryChecker;
use Ilabs\Inpost_Pay\rest\Base;
use Ilabs\Inpost_Pay\WooCommerce\WooCommerceBasketCache;
use Ilabs\Inpost_Pay\WooCommerce\WooCommerceOrder;
use Ilabs\Inpost_Pay\WooCommerce\WooDeliveryPrice;
use InspireLabs\WoocommerceInpost\EasyPack;
use JsonException;
use Throwable;
use WC_Data_Exception;
use WC_Order;
use WC_Tax;
use WP_Error;
use WP_REST_Response;
use WP_User;
use function Ilabs\Inpost_Pay\inpost_pay_container;
use function WC;
use function Ilabs\Inpost_Pay\inpost_pay;

class Create extends Base {

	private static bool $block_order_save_hook = false;

	private object $basket_from_cache;
	/**
	 * @var true
	 */
	private bool $is_digital_cart = false;

	private CartSessionService $cart_session;

	public function __construct() {
		/**
		 * Get form container DI.
		 *
		 * @var CartSessionService $cart_session
		 */
		$this->cart_session = inpost_pay_container()->get( CartSessionService::SERVICE_KEY );
		$this->restricted   = true;
	}

	protected function describe() {
		$this->post['/inpost/v1/izi/order'] = function ( $request ) {

			$this->check_signature( $request );
			AdminOrderUpdate::$block = true;
			$data                    = $request->get_body();
			$response_raw            = $this->handleRequest( $data );

			// if some unexpected error is occured in line 138 - set response code 409.
			if ( is_wp_error( $response_raw ) || ! empty( $response_raw['error'] ) ) {
				$response = new WP_REST_Response(
					array(
						'error'         => 'ORDER_NOT_CREATE',
						'place_in_code' => __METHOD__ . ': ' . __LINE__,
						'error_details' => ! empty( $response_raw['error'] ) ? $response_raw['error'] : '',
					)
				);
				$response->set_status( 409 );

			} else {

				$response_mb_convert = mb_convert_encoding( $response_raw, 'UTF-8' );
				$response_body       = is_array( $response_mb_convert ) ? $response_mb_convert : json_decode( $response_mb_convert );
				$response            = new WP_REST_Response( $response_body );
				$response->set_status( 201 );
			}

			$current_plugin_version = inpost_pay()->get_plugin_version();
			$response->header( 'inpay-plugin-version', $current_plugin_version );

			return $response;
		};
	}

	/**
	 * Handle incoming request to create an order.
	 *
	 * This method is responsible for handling the request to create an order.
	 * It initializes the environment, parses and validates the data, checks
	 * for existing orders, prepares the order creation, creates the new order,
	 * handles custom order id, and finalizes the order creation.
	 *
	 * @param string $data The data sent in the request body.
	 *
	 * @return array|string The response to be sent back to the client.
	 * @throws JsonException
	 */
	private function handleRequest( string $data ) {
		$this->initializeEnvironment( $data );

		try {
			$parsedData = $this->parseAndValidateData( $data );
		} catch ( JsonDecodeException $e ) {
			return array( 'error' => $e->getMessage() );
		}

		$existingOrder = $this->checkForExistingOrder( $parsedData );
		if ( $existingOrder !== null ) {
			Logger::log( 'EXISTING ORDER' );

			return $existingOrder;
		}

		$this->prepareOrderCreation( $parsedData );

		try {
			$orderCreationResult = $this->createNewOrder( $parsedData );
		} catch ( WC_Data_Exception | Exception $e ) {
			$this->logException( $e, 'Order creation failed' );

			return array( 'error' => $e->getMessage() );
		}

		if ( is_array( $orderCreationResult ) && isset( $orderCreationResult['error'] ) ) {
			return $orderCreationResult;
		}

		[ $redir, $oid, $order, $realOrderId ] = $orderCreationResult;

		$normalizedOid = $this->normalizeOrderId( $oid );
		if ( $normalizedOid === null ) {
			Logger::log( '[CREATE_ORDER] handleRequest: ERROR: invalid order ID type: ' . gettype( $oid ) . ', value: ' . var_export( $oid, true ) );

			return array( 'error' => 'Invalid order ID returned from WooCommerce: ' . var_export( $oid, true ) );
		}

		$finalOrderId = $this->handleCustomOrderId( $parsedData, $normalizedOid, $order );

		if ( $order === null ) {
			Logger::log( '[CREATE_ORDER] handleRequest: ERROR: order not created' );

			return array( 'error' => 'Order not created' );
		}

		return $this->finalizeOrder( $finalOrderId, $order, $realOrderId, $redir, $parsedData );
	}

	/**
	 * Initialize environment for handling incoming request.
	 *
	 * This method is called at the beginning of the request handling process.
	 * It sets up the environment by defining the constant DOING_AJAX,
	 * logging the incoming response and blocking any PUT requests.
	 *
	 * @param string $data Incoming JSON data.
	 */
	private function initializeEnvironment( $data ): void {
		define( 'DOING_AJAX', true );
		Logger::response( $data );
		InPostIzi::blockPut();
		// Logger::log( '[CREATE_ORDER] handleRequest: InPostIzi::blockPut() done' );
	}

	/**
	 * Parse and validate incoming data.
	 *
	 * Method is responsible for parsing and validating incoming JSON data.
	 * If data is not valid JSON, it throws JsonDecodeException.
	 * If data is valid JSON, it prepares EasyPack integration and returns parsed data.
	 *
	 * @param string $data
	 *
	 * @return object
	 * @throws JsonDecodeException
	 */
	private function parseAndValidateData( string $data ): object {
		try {
			$parsedData = json_decode( $data, false, 512, JSON_THROW_ON_ERROR );
			$this->setupEasyPackIntegration();

			return $parsedData;
		} catch ( JsonException $e ) {
			Logger::log( 'JSON parsing error: ' . $e->getMessage() );

			throw new JsonDecodeException( $e->getMessage() );
		}
	}

	/**
	 * Handle incoming request to create an order.
	 *
	 * This method is responsible for handling the request to create an order.
	 * It initializes the environment, parses and validates the data, checks
	 * for existing orders, prepares the order creation, creates the new order,
	 * handles custom order id, and finalizes the order creation.
	 *
	 * @param string $data The data sent in the request body.
	 *
	 * @return array|string The response to be sent back to the client.
	 * @throws JsonException
	 */

	/**
	 * Initializes EasyPack integration.
	 *
	 * This method is responsible for preparing environment for EasyPack integration.
	 * It removes 'woocommerce_cart_loaded_from_session' action and adds filter
	 * for 'woocommerce_shipping_packages' action.
	 *
	 * The filter is added to prevent EasyPack from adding its shipping methods
	 * again when the cart is loaded from session. The filter is removed after
	 * the first execution to prevent any other issues.
	 */
	private function setupEasyPackIntegration(): void {
		remove_all_actions( 'woocommerce_cart_loaded_from_session' );
		// Logger::log( '[CREATE_ORDER] handleRequest: remove_all_actions(woocommerce_cart_loaded_from_session) done' );

		if ( class_exists( EasyPack::class ) ) {
			// Logger::log( '[CREATE_ORDER] handleRequest: EasyPack – add filter woocommerce_shipping_packages' );
			add_filter(
				'woocommerce_shipping_packages',
				static function ( $packages ) {
					// Logger::log( '[CREATE_ORDER] EasyPack - filter woocommerce_shipping_packages has been executed' );
					remove_filter(
						'woocommerce_shipping_packages',
						array(
							EasyPack::EasyPack(),
							'woocommerce_shipping_packages',
						),
						1000
					);

					return $packages;
				},
				900
			);
		}
	}

	/**
	 * Check if order with given cart_id already exists in WooCommerce.
	 * If it does, return the order ID.
	 *
	 * @param object $parsedData Parsed data from Inpost Pay API.
	 *
	 * @return string|array|null Order ID if order exists, null otherwise.
	 */
	private function checkForExistingOrder( object $parsedData ) {
		// Logger::log( '[CREATE_ORDER] handleRequest: get id of an existing order from CartSession' );
		$cart_order_id = $this->cart_session->get_order_id_by_cart_id( $parsedData->order_details->basket_id );

		if ( $cart_order_id !== null ) {
			// Logger::log( '[CREATE_ORDER] handleRequest: order already exists, ID: ' . $cart_order_id );
			try {
				return WooCommerceOrder::getOrder( $cart_order_id )->encode();
			} catch ( CantGetOrderObjectException $e ) {
				$this->logException( $e, 'Existing order retrieval failed' );

				return array( 'error' => $e->getMessage() );
			}
		}

		return null;
	}

	private function logException( $exception, $context = '' ): void {
		$message = $context ? $context . ': ' . $exception->getMessage() : $exception->getMessage();
		Logger::log( $message );
		Logger::log( $exception->getTraceAsString() );
		Logger::log( $exception->getFile() . ':' . $exception->getLine() );
	}

	/**
	 * Prepare the environment for creating new order by restoring cart session.
	 *
	 * @param object $parsedData Parsed data from Inpost Pay API.
	 *
	 * @return void
	 */
	private function prepareOrderCreation( object $parsedData ): void {
		WooCommerceBasketCache::restore( $parsedData->order_details->basket_id, true );
		WpcBundleAvailabilityIntegration::maybe_delete_wpc_products();
	}

	/**
	 * Creates a new order in WooCommerce.
	 *
	 * @param object $parsedData Parsed data from Inpost Pay API.
	 *
	 * @return array|null
	 * @throws Exception
	 */
	private function createNewOrder( object $parsedData ): ?array {
		try {
			[ $redir, $oid, $order ] = $this->createOrder( $parsedData );

			return array( $redir, $oid, $order, $oid );
		} catch ( WC_Data_Exception | Exception $e ) {
			$this->logException( $e, 'Order creation failed' );

			return array( 'error' => $e->getMessage() );
		}
	}

	/**
	 * Creates a new order in WooCommerce.
	 *
	 * This function is responsible for creating new order in WooCommerce, setting
	 * all required data, including shipping and billing address, shipping method
	 * and delivery options.
	 *
	 * @param object $data Parsed data from Inpost Pay API.
	 *
	 * @return array|null
	 * @throws InvalidAuthenticationType
	 * @throws JsonException
	 * @throws WC_Data_Exception
	 */
	private function createOrder( object $data ): array {
		wp_suspend_cache_invalidation( true );
		wp_defer_term_counting( true );
		add_filter( 'action_scheduler_queue_runner_concurrent_batches', '__return_zero' );

		// WC Admin spam
		add_filter( 'woocommerce_analytics_report_menu_items', '__return_empty_array' );
		add_filter( 'woocommerce_admin_disabled', '__return_true' );

		// Defer user meta updates
		$defer_user_meta = has_action(
			'woocommerce_order_status_processing',
			array(
				'WC_Customer',
				'update_customer_from_order',
			)
		);
		if ( $defer_user_meta ) {
			remove_action(
				'woocommerce_order_status_processing',
				array(
					'WC_Customer',
					'update_customer_from_order',
				),
				10
			);
			remove_action(
				'woocommerce_order_status_completed',
				array(
					'WC_Customer',
					'update_customer_from_order',
				),
				10
			);
		}

		try {
			$user_zone = ProductDeliveryChecker::prepare_user_zone_context();
			$zone_id   = $user_zone->get_id();
			$dest      = Destination::get();

			$this->getBasketFromCache( $data );
			[
				$base_group,
				$delivery_tax_statuses,
				$order_group,
				$delivery_cost,
				$delivery_method,
				$shipping_method_integration
			] = $this->load_shipping_method_integrator( $data, $zone_id );

			$user = $this->authenticateUser( $data );

			[ $billing_address, $shipping_address ] = $this->prepareBillingAndShippingAddress( $data );

			if ( empty( WC()->customer->get_shipping_country() ) ) {
				WC()->customer->set_shipping_country( 'PL' );
			}
			if ( empty( WC()->customer->get_shipping_postcode() ) ) {
				WC()->customer->set_shipping_postcode( $data->account_info->client_address->postal_code );
			}
			if ( empty( WC()->customer->get_shipping_city() ) ) {
				WC()->customer->set_shipping_city( $data->account_info->client_address->city );
			}

			$shipping_packages = ProductDeliveryChecker::prepare_manual_package( $dest );

			if ( ! WC()->cart->needs_shipping() ) {
				$this->is_digital_cart = true;
			}

			if ( DigitalProduct::DELIVERY_TYPE_DIGITAL !== $delivery_method ) {
				$this->set_chosen_shipping_method_to_order( $shipping_packages, $delivery_method );
			}
			$this->handle_methods_before_checkout();

			wc_clear_notices();
			WC()->cart->check_cart_items();
			$cart_errors = wc_get_notices( 'error' );
			if ( ! empty( $cart_errors ) ) {
				$error_messages = array_column( $cart_errors, 'notice' );
				$error_message  = implode( ' ', $error_messages );
				Logger::log( '[CREATE_ORDER] Stock validation failed: ' . $error_message );

				return array( 'error' => 'Stock validation failed: ' . $error_message );
			}

			$checkout = WC()->checkout();

			$order_id = $checkout->create_order(
				array(
					'billing_email'   => $data->account_info->mail,
					'payment_method'  => Virtual_Payment_Method_Utils::get_payment_method_for_order(),
					'shipping_method' => $delivery_method,
					'shipping'        => $shipping_address,
					'billing'         => $billing_address,
					'is_vat_exempt'   => 'no',
				)
			);

			if ( $order_id instanceof WP_Error ) {
				Logger::debug( 'Create order error:' );
				Logger::log( $order_id );

				return array( null, null, null );
			}

			Logger::log( '[CREATE_ORDER] Order successfully created. Order ID: ' . $order_id );

			if ( did_action( 'woocommerce_after_register_post_type' ) ) {
				$order = wc_get_order( $order_id );
			} else {
				$order = new WC_Order( $order_id );
			}

			if ( $user ) {
				$order->set_customer_id( $user->ID );
				Logger::log( '[CREATE_ORDER] User ID set on order - ' . $user->ID );
			}

			if ( version_compare( WC()->version, '8.6', '>' ) ) {
				try {
					$order_attribution = new OrderAttribution( $data->order_details->basket_id );
					$order_attribution->add_to_order( $order );
				} catch ( CantCreateAttribution | JsonException $e ) {
					Logger::log( '[CREATE_ORDER] OrderAttribution failed - ' . $e->getMessage() );
				}
			}

			$order->save();

			if ( ! ( $order instanceof WC_Order ) ) {
				Logger::log( '[CREATE_ORDER] Returning NULL, order is not an instance of WC_Order' );

				return array( null, null, null );
			}

			if ( DigitalProduct::DELIVERY_TYPE_DIGITAL !== $delivery_method ) {
				$shipping_method_integration->setWcOrder( $order );
				/**
				 * @var $is_base_group_taxed bool
				 * @var $delivery_cost array
				 * @var $delivery_price Woo_Delivery_Price
				 */
				[
					$is_base_group_taxed,
					$delivery_cost,
					$delivery_price
				] = $this->calculate_delivery_cost( $delivery_cost, $delivery_tax_statuses, $base_group, $order_group );
			} else {
				[ $delivery_price, $is_base_group_taxed ] = $this->setDeliveryCostForDigitalProduct();
			}

			$hook_applied  = false;
			$hook_callback = function ( WC_Order $order, $data ) use ( $delivery_price, $is_base_group_taxed, &$hook_applied ) {
				if ( self::$block_order_save_hook || $hook_applied ) {

					return;
				}

				self::$block_order_save_hook = true;
				$hook_applied                = true;

				Logger::log( '[ORDER_SAVE] DELIVERY PRICE: ' . var_export( $delivery_price, true ) );

				$custom_shipping_cost_net   = $delivery_price->get_base_total() + $delivery_price->get_options_total();
				$custom_shipping_tax        = WooDeliveryPrice::normalizePrice( $delivery_price->get_base_total_vat() + $delivery_price->get_options_total_vat() );
				$custom_shipping_cost_gross = $custom_shipping_cost_net + $custom_shipping_tax;

				Logger::log( '[ORDER_SAVE] Shipping NET: ' . $custom_shipping_cost_net );
				Logger::log( '[ORDER_SAVE] Shipping VAT: ' . $custom_shipping_tax );
				Logger::log( '[ORDER_SAVE] Shipping GROSS: ' . $custom_shipping_cost_gross );

				if ( ! $is_base_group_taxed ) {
					Logger::log( '[SHIPPING] Skipping tax calculation for non-taxable delivery' );

					foreach ( $order->get_items( 'shipping' ) as $shipping_item ) {
						$shipping_item->set_taxes( array() );
						$shipping_item->set_total( $custom_shipping_cost_gross );
						if ( method_exists( $shipping_item, 'set_tax_status' ) ) {
							$shipping_item->set_tax_status( 'none' );
						}
						$shipping_item->save();
					}

					$zero_rate_filter = static function ( $tax_class, $shipping = null, $order_ctx = null ) {
						return 'zero-rate';
					};
					add_filter( 'woocommerce_shipping_tax_class', $zero_rate_filter, 9999, 3 );

					$order->calculate_taxes();
					$order->calculate_totals( false );

					remove_filter( 'woocommerce_shipping_tax_class', $zero_rate_filter, 9999 );

					Logger::log( '[ORDER_SAVE] Non-taxable shipping fixed - no recalculation needed' );

					return;
				}

				foreach ( $order->get_items( 'shipping' ) as $shipping_item ) {
					$shipping_item->set_taxes( array() );
					$shipping_item->set_total( $custom_shipping_cost_net );

					$normalized_cost_net = WooDeliveryPrice::normalizePrice( $custom_shipping_cost_net );

					$vat_rate = $normalized_cost_net > 0
						? wc_format_decimal( 100 * $custom_shipping_tax / $normalized_cost_net, 2 )
						: 0.0;

					Logger::log( '[ORDER TAX] VAT rate: ' . $vat_rate );

					$tax_rate_id = $this->get_matching_shipping_tax_rate_id(
						$order->get_shipping_country(),
						$order->get_shipping_state(),
						$vat_rate
					);

					if ( $tax_rate_id ) {
							Logger::log( '[ORDER TAX] Matched tax rate ID: ' . $tax_rate_id );
						$tax_rates = WC_Tax::get_rates( $tax_rate_id );

						if ( ! empty( $tax_rates ) ) {
							$shipping_tax_data = WC_Tax::calc_shipping_tax( $custom_shipping_cost_net, $tax_rates );

							if ( ! empty( $shipping_tax_data ) ) {
								$shipping_item->set_taxes( array( $tax_rate_id => reset( $shipping_tax_data ) ) );
							}

							$shipping_item->save();
						}
					} else {
						Logger::log( '[ORDER TAX] No tax rate matched – fallback to gross value' );
						$shipping_item->set_total( $custom_shipping_cost_gross );
						$shipping_item->save();
					}
				}

				$order->calculate_taxes();
				$order->calculate_totals( false );
			};

			add_action( 'woocommerce_before_order_object_save', $hook_callback, PHP_INT_MAX - 1, 2 );

			$billing_address = $this->setInvoiceDataToOrder( $data, $order, $billing_address );

			Logger::log( '[CREATE_ORDER] Update post_meta' );

			$this->store_order_meta( $data, $order, $delivery_cost, $base_group, $billing_address, $shipping_address );

			$cod_enabled = false;
			if ( isset( $delivery_cost['delivery_options'] ) && is_array( $delivery_cost['delivery_options'] ) ) {
				foreach ( $delivery_cost['delivery_options'] as $option ) {
					if ( $option['delivery_code_value'] === 'COD' ) {
						$cod_enabled = true;
						break;
					}
				}
			}

			if ( $cod_enabled ) {
				$order->update_meta_data( '_paczkomat_cod', 1 );
			}

			$order->update_meta_data(
				'izi_delivery_price_total',
				$this->mapDeliveryPrice( $order )
			);

			$order->save();

			remove_action( 'woocommerce_before_order_object_save', $hook_callback, PHP_INT_MAX - 1 );

			if ( $cod_enabled ) {
				$fresh_total = $order->get_total();
				$order->update_meta_data( '_paczkomat_cod_amount', $fresh_total );
				$order->save();
			}

			$order_received_url = wc_get_endpoint_url(
				'order-received',
				$order->get_id(),
				wc_get_checkout_url() . '?showIzi=true&key=' . $order->get_order_key()
			);

			Logger::log( '[CREATE_ORDER] Return data' );

			return array( $order_received_url, $order->get_id(), $order );

		} finally {
			wp_suspend_cache_invalidation( false );
			CacheHelper::disable_wp_cache();
			wp_defer_term_counting( false );
			remove_filter( 'action_scheduler_queue_runner_concurrent_batches', '__return_zero' );
			remove_filter( 'woocommerce_analytics_report_menu_items', '__return_empty_array' );
			remove_filter( 'woocommerce_admin_disabled', '__return_true' );

			if ( isset( $order, $user ) && $defer_user_meta ) {
				add_action(
					'woocommerce_order_status_processing',
					array( 'WC_Customer', 'update_customer_from_order' )
				);
				add_action(
					'woocommerce_order_status_completed',
					array( 'WC_Customer', 'update_customer_from_order' )
				);
			}
		}
	}

	/**
	 * Get the basket from the cache by basket_id in $data->order_details
	 *
	 * @param object $data
	 *
	 * @return void
	 * @throws JsonException
	 */
	public function getBasketFromCache( object $data ): void {
		$basket = $this->cart_session->get_cart_cache_by_id( $data->order_details->basket_id );
		$basket = str_replace(
			'\/',
			'/',
			mb_convert_encoding( $basket, 'UTF-8' )
		);
		Logger::log( '[CREATE_ORDER] createOrder: basket cache fetched' );
		$this->basket_from_cache = json_decode( $basket, false, 512, JSON_THROW_ON_ERROR );
	}

	/**
	 * Create the shipping method integrator for given order data
	 *
	 * @param object $data
	 * @param int    $zone_id
	 *
	 * @return array [
	 *     GroupInterface $baseGroup base group for delivery type,
	 *     array $deliveryTaxStatuses delivery tax statuses for this order,
	 *     GroupInterface $orderGroup resolved group for this order,
	 *     float $deliveryCost mapped delivery cost for this order,
	 *     string $deliveryMethod shipping method for this order,
	 *     ShippingMethodIntegrationInterface $shippingMethodIntegration shipping method integrator for this order
	 * ]
	 *
	 * @throws JsonException
	 */
	public function load_shipping_method_integrator( object $data, int $zone_id ): array {
		$shipping_cost_settings = inpost_pay()->shipping_cost_settings( $zone_id );
		// Logger::log( '[CREATE_ORDER] get shipping_cost_settings for zone_id=' . $zone_id );
		// Logger::log( '[CREATE_ORDER] delivery payload: ' . var_export( $data->delivery, true ) );

		$delivery_codes = array();
		if ( isset( $data->delivery->delivery_codes ) && is_array( $data->delivery->delivery_codes ) ) {
			$delivery_codes = $data->delivery->delivery_codes;
			// Logger::log( '[CREATE_ORDER] delivery codes: ' . implode( ',', $delivery_codes ) );
		}

		$base_group = $shipping_cost_settings->findGroup( $data->delivery->delivery_type );
		// Logger::log( '[CREATE_ORDER] base_group resolved: ' . var_export( $base_group, true ) );

		$option_sub_groups = array();

		$basket_delivery_from_cache = $this->basket_from_cache->delivery;
		// Logger::log( '[CREATE_ORDER] basket_delivery_from_cache: ' . var_export( $basket_delivery_from_cache, true ) );

		$delivery_tax_statuses = $this->cart_session->get_cart_delivery_cache_by_id( $data->order_details->basket_id );
		// Logger::log( '[CREATE_ORDER] deliveryTaxStatuses fetched: ' . var_export( $deliveryTaxStatuses, true ) );

		foreach ( $delivery_codes as $deliveryCode ) {
			$found = $shipping_cost_settings->findGroup( $data->delivery->delivery_type, $deliveryCode );
			if ( $found ) {
				$option_sub_groups[] = $found;
				// Logger::log( '[CREATE_ORDER] option group added for code=' . $deliveryCode . ' -> ' . var_export( $found, true ) );
			}
		}

		if ( is_null( $base_group ) ) {
			// Logger::log( '[CREATE_ORDER] base_group is NULL, falling back to digital product' );
			$delivery_method             = DigitalProduct::DELIVERY_TYPE_DIGITAL;
			$delivery_cost[]             = array(
				'delivery_name'         => $delivery_method,
				'delivery_code_value'   => $delivery_method,
				'delivery_option_price' => array(
					'net'      => WooDeliveryPrice::normalizePrice( 0 ),
					'full_net' => WooDeliveryPrice::normalizePrice( 0 ),
					'gross'    => WooDeliveryPrice::normalizePrice( 0 ),
					'vat'      => WooDeliveryPrice::normalizePrice( 0 ),
				),
			);
			$order_group                 = $delivery_method;
			$shipping_method_integration = null;
		} else {
			$order_group = DeliveryOptionHelper::getOrderFinalDeliveryGroup( $base_group, $option_sub_groups );
			// Logger::log( '[CREATE_ORDER] order_group resolved: ' . var_export( $order_group, true ) );

			$delivery_cost = $this->mapDeliveryCostBasedOnBasketCache(
				$basket_delivery_from_cache,
				$base_group,
				$delivery_codes,
				$order_group
			);
			// Logger::log( '[CREATE_ORDER] delivery_cost mapped: ' . var_export( $delivery_cost, true ) );

			$delivery_method = esc_attr( $order_group->getShippingMethodField()->get() );
			// Logger::log( '[CREATE_ORDER] delivery_method = ' . $delivery_method );

			$parcelMachineId = property_exists( $data->delivery, 'delivery_point' )
				? $data->delivery->delivery_point
				: null;
			// Logger::log( '[CREATE_ORDER] parcelMachineId = ' . ( $parcelMachineId ?? 'null' ) );

			$shipping_method_integration = ShippingMethodIntegrationFactory::create( $delivery_method, $parcelMachineId );
			// Logger::log( '[CREATE_ORDER] shipping_method_integration instance: ' . get_class( $shipping_method_integration ) );

			$shipping_method_integration->configure();
			// Logger::log( '[CREATE_ORDER] shipping_method_integration->configure() executed' );
		}

		return array(
			$base_group,
			$delivery_tax_statuses,
			$order_group,
			$delivery_cost,
			$delivery_method,
			$shipping_method_integration,
		);
	}

	/**
	 * @param array          $basketDelivery
	 * @param GroupInterface $baseGroup
	 * @param array          $selectedDeliveryCodes
	 * @param GroupInterface $orderGroup
	 *
	 * @return array
	 *
	 * $basketDelivery: created in:
	 *     \Ilabs\Inpost_Pay\WooDeliveryPrice::mapDelivery
	 */
	private function mapDeliveryCostBasedOnBasketCache(
		array $basketDelivery,
		GroupInterface $baseGroup,
		array $selectedDeliveryCodes,
		GroupInterface $orderGroup
	): array {
		$baseGroupIsOrderGroup = $baseGroup->getGroupId() === $orderGroup->getGroupId();
		$result                = array();
		$baseTotal             = array();

		foreach ( $basketDelivery as $basketDeliveryStdClass ) {
			if ( $basketDeliveryStdClass->delivery_type === $baseGroup->getDeliveryTypeCode() ) {
				$baseTotal = array(
					'net'      => $basketDeliveryStdClass->delivery_price->net,
					'full_net' => $basketDeliveryStdClass->delivery_price->full_net,
					'gross'    => WooDeliveryPrice::normalizePrice( $basketDeliveryStdClass->delivery_price->gross ),
					'vat'      => WooDeliveryPrice::normalizePrice( $basketDeliveryStdClass->delivery_price->vat ),
				);

				foreach ( $basketDeliveryStdClass->delivery_options as $basketDeliveryOptionStdClass ) {
					if ( in_array(
						$basketDeliveryOptionStdClass->delivery_code_value,
						$selectedDeliveryCodes
					) ) {
						$optionPrice = $basketDeliveryOptionStdClass->delivery_option_price;

						$result[] = array(
							'delivery_name'         => $basketDeliveryOptionStdClass->delivery_name,
							'delivery_code_value'   => $basketDeliveryOptionStdClass->delivery_code_value,
							'delivery_option_price' => array(
								'net'      => $optionPrice->net,
								'full_net' => $optionPrice->full_net,
								'gross'    => WooDeliveryPrice::normalizePrice( $optionPrice->gross ),
								'vat'      => WooDeliveryPrice::normalizePrice( $optionPrice->vat ),
							),
						);

					}
				}
				break;
			}
		}

		return array(
			'delivery_options'    => $result,
			'base_delivery_price' => $baseTotal,
		);
	}

	/**
	 * Authenticate user using Inpost Pay authentication mechanism
	 *
	 * @param object $data Order data from Inpost Pay
	 *
	 * @return WP_User|null Authenticated user or null if authentication failed
	 * @throws InvalidAuthenticationType
	 */
	public function authenticateUser( object $data ): ?WP_User {
		$authenticator = AuthenticationFactory::create( 'order' );

		$credentials = new Credentials();

		$credentials->set_email( $data->account_info->mail );
		$credentials->set_phone_number( $data->account_info->phone_number->phone );
		// Logger::log( '[CREATE_ORDER] createOrder: credentials set' );
		try {
			$user = $authenticator->authenticate( $credentials );
			// Logger::log( '[CREATE_ORDER] createOrder: user authenticated. User ID: ' . ( $user->ID ?? 'null' ) );
			if ( $user && $user->ID ) {
				WC()->session->set( 'customer_id', $user->ID );
			}
		} catch ( UserNotFoundException | EmptyCredentialsForOrderAuthenticationException $e ) {
			Logger::log( '[CREATE_ORDER] createOrder: user authentication failed - ' . $e->getMessage() );
			$user = null;
		}

		return $user;
	}

	/**
	 * Prepares billing and shipping address from API response data.
	 *
	 * @param object $data
	 *
	 * @return array
	 */
	public function prepareBillingAndShippingAddress( object $data ): array {
		$billingAddress = array(
			'first_name' => $data->account_info->name,
			'last_name'  => $data->account_info->surname,
			'email'      => $data->account_info->mail,
			'phone'      => $data->account_info->phone_number->country_prefix . ' ' . $data->account_info->phone_number->phone,
			'address_1'  => $data->account_info->client_address->address,
			'address_2'  => '',
			'city'       => $data->account_info->client_address->city,
			'state'      => '',
			'postcode'   => $data->account_info->client_address->postal_code,
			'country'    => $data->account_info->client_address->country_code,
		);

		$shippingAddress = $billingAddress;
		if ( isset( $data->delivery->delivery_address ) ) {
			$deliveryNameParts = explode(
				' ',
				$data->delivery->delivery_address->name
			);
			$deliveryName      = count( $deliveryNameParts ) > 1 ? array_shift( $deliveryNameParts ) : '';
			$deliverySurname   = implode( ' ', $deliveryNameParts );
			$shippingAddress   = array(
				'first_name' => $deliveryName,
				'last_name'  => $deliverySurname,
				'email'      => $data->delivery->mail,
				'phone'      => $data->delivery->phone_number->country_prefix . ' ' . $data->delivery->phone_number->phone,
				'address_1'  => $data->delivery->delivery_address->address,
				'address_2'  => $data->delivery->courier_note ?? '',
				'city'       => $data->delivery->delivery_address->city,
				'state'      => '',
				'postcode'   => $data->delivery->delivery_address->postal_code,
				'country'    => $data->delivery->delivery_address->country_code,
			);
		}

		return array( $billingAddress, $shippingAddress );
	}

	/**
	 * Set the chosen shipping method to order.
	 *
	 * @param array  $shipping_packages - WooCommerce shipping packages.
	 * @param string $deliveryMethod - Delivery method.
	 *
	 * @return void
	 */
	public function set_chosen_shipping_method_to_order( array $shipping_packages, $deliveryMethod ): void {

		// Logger::log('[CREATE_ORDER] Shipping packages: ' . var_export($shipping_packages, true));
		if ( ! $this->is_digital_cart ) {
			$packages_array = array();

			if ( isset( $shipping_packages['contents'] ) ) {
				$packages_array[0] = $shipping_packages;
			} else {
				$packages_array = $shipping_packages;
			}

			$calculate_shipping = WC()
				->shipping()
				->calculate_shipping( $packages_array );
			// Logger::log( '[CREATE_ORDER] createOrder: shipping calculated' );

			unset( WC()->session->chosen_shipping_methods );
			WC()->session->set( 'chosen_shipping_methods', array( $deliveryMethod ) );
			WC()->session->set(
				'shipping_method_counts',
				array( 0 => count( $calculate_shipping[0]['rates'] ) )
			);

			// Logger::log( '[CREATE_ORDER] createOrder: session shipping method set' );

			add_filter(
				'woocommerce_shipping_chosen_method',
				static function ( $default, $rates, $chosen_method ) use ( $deliveryMethod
				) {
					return $deliveryMethod;
				},
				10,
				3
			);

		}
	}

	/**
	 * Modifies WooCommerce actions and add custom integrations before checkout.
	 *
	 * @return void
	 */
	private function handle_methods_before_checkout(): void {
		remove_all_actions( 'woocommerce_order_status_pending_to_on-hold_notification' );

		// Add required nonce for plugin BLPaczka
		( new BLPaczka_Integration() )->add_nonce_to_checkout();
	}

	/**
	 * Calculates the delivery cost and tax information for a given delivery.
	 *
	 * @param array          $delivery_cost        Delivery options and base delivery price details.
	 * @param array          $delivery_tax_statuses Tax status information for delivery groups.
	 * @param GroupInterface $base_group           Base group for delivery cost calculation.
	 * @param GroupInterface $order_group          Final order group (may differ from base if COD selected).
	 *
	 * @return array [isBaseGroupTaxed, deliveryCost, deliveryPrice]
	 */
	public function calculate_delivery_cost(
		array $delivery_cost,
		array $delivery_tax_statuses,
		GroupInterface $base_group,
		GroupInterface $order_group
	): array {
		$delivery_options = $delivery_cost['delivery_options'];

		$order_group_id = $order_group->getGroupId();
		$base_group_id  = $base_group->getGroupId();

		$has_order_group_tax_info = array_key_exists( $order_group_id, $delivery_tax_statuses );
		$has_base_group_tax_info  = array_key_exists( $base_group_id, $delivery_tax_statuses );

		$is_order_group_taxed = $has_order_group_tax_info
			? ! empty( $delivery_tax_statuses[ $order_group_id ] )
			: null;

		$is_base_group_taxed = $has_base_group_tax_info
			? ! empty( $delivery_tax_statuses[ $base_group_id ] )
			: null;

		Logger::log( '[CALCULATE_DELIVERY] delivery_tax_statuses: ' . var_export( $delivery_tax_statuses, true ) );
		Logger::log( '[CALCULATE_DELIVERY] order_group_id: ' . $order_group_id . ', base_group_id: ' . $base_group_id );
		Logger::log( '[CALCULATE_DELIVERY] has_order_info: ' . var_export( $has_order_group_tax_info, true ) . ', has_base_info: ' . var_export( $has_base_group_tax_info, true ) );

		if ( null === $is_order_group_taxed || null === $is_base_group_taxed ) {
			$basket_base_vat  = WooDeliveryPrice::normalizePrice( $delivery_cost['base_delivery_price']['vat'] ?? 0 );
			$inferred_taxable = $basket_base_vat > 0;

			Logger::log( '[CALCULATE_DELIVERY] Missing tax info for group(s) – inferred from basket VAT (' . $basket_base_vat . '): ' . ( $inferred_taxable ? 'taxable' : 'not taxable' ) );

			if ( null === $is_order_group_taxed ) {
				$is_order_group_taxed = $inferred_taxable;
			}
			if ( null === $is_base_group_taxed ) {
				$is_base_group_taxed = $inferred_taxable;
			}
		}

		Logger::log( '[CALCULATE_DELIVERY] Base group taxed: ' . var_export( $is_base_group_taxed, true ) );
		Logger::log( '[CALCULATE_DELIVERY] Order group taxed: ' . var_export( $is_order_group_taxed, true ) );

		if ( ! $is_order_group_taxed ) {
			Logger::log( '[CHECK_TOTALS] Order group not taxable – overriding VAT to 0 and net = gross' );

			$delivery_cost['base_delivery_price']['vat']      = 0.0;
			$delivery_cost['base_delivery_price']['net']      = $delivery_cost['base_delivery_price']['gross'];
			$delivery_cost['base_delivery_price']['full_net'] = $delivery_cost['base_delivery_price']['gross'];

			foreach ( $delivery_options as &$option ) {
				$option['delivery_option_price']['vat']      = 0.0;
				$option['delivery_option_price']['net']      = $option['delivery_option_price']['gross'];
				$option['delivery_option_price']['full_net'] = $option['delivery_option_price']['gross'];
			}
			unset( $option );
		}

		$base_total     = $delivery_cost['base_delivery_price']['full_net'];
		$base_total_vat = $delivery_cost['base_delivery_price']['vat'];

		$options_total     = 0.0;
		$options_total_vat = 0.0;

		foreach ( $delivery_options as $option ) {
			$options_total     += $option['delivery_option_price']['full_net'];
			$options_total_vat += $option['delivery_option_price']['vat'];
		}

		$delivery_price = new Woo_Delivery_Price();
		$delivery_price->set_base_total( $base_total );
		$delivery_price->set_base_total_vat( $base_total_vat );
		$delivery_price->set_options_total( $options_total );
		$delivery_price->set_options_total_vat( $options_total_vat );

		return array( $is_order_group_taxed, $delivery_cost, $delivery_price );
	}

	/**
	 * Set the fake delivery cost for a digital product
	 *
	 * @return array - array with two elements:
	 *                  [0] - object with properties:
	 *                          baseTotal - total base delivery price
	 *                          baseTotalVat - total base delivery VAT
	 *                          optionsTotal - total options price
	 *                          optionsTotalVat - total options VAT
	 *                  [1] - boolean indicating whether the base group is taxable
	 */
	public function setDeliveryCostForDigitalProduct(): array {
		$woo_delivery_price = new Woo_Delivery_Price();
		$woo_delivery_price->set_base_total( 0 );
		$woo_delivery_price->set_base_total_vat( 0 );
		$woo_delivery_price->set_options_total( 0 );
		$woo_delivery_price->set_options_total_vat( 0 );
		return array( $woo_delivery_price, false );
	}

	/**
	 * Gets the matching shipping tax rate ID for a given country, state, and VAT rate.
	 *
	 * @param string $country Country code.
	 * @param string $state State code.
	 * @param float  $vat_rate VAT rate.
	 *
	 * @return int|null Matching shipping tax rate ID or null if not found.
	 */
	private function get_matching_shipping_tax_rate_id( string $country, string $state = '', float $vat_rate = 23.0 ): ?int {
		$tax_class = get_option( 'woocommerce_shipping_tax_class' );

		if ( 'inherit' === $tax_class ) {
			$tax_class = '';
		}

		$rates     = WC_Tax::get_rates_for_tax_class( $tax_class );
		$tolerance = 0.5;

		// Logger::log( '[TAX MATCH] Rates: ' . var_export( $rates, true ) );

		$best_match_id  = null;
		$smallest_delta = null;

		foreach ( $rates as $rate_id => $rate ) {
			if ( is_object( $rate ) ) {
				$rate = (array) $rate;
			}

			if (
				( '' === $rate['tax_rate_country'] || strtoupper( $rate['tax_rate_country'] ) === strtoupper( $country ) ) &&
				( '' === $state || '' === $rate['tax_rate_state'] || strtoupper( $rate['tax_rate_state'] ) === strtoupper( $state ) ) &&
				1 === (int) $rate['tax_rate_shipping']
			) {
				$rate_value = (float) $rate['tax_rate'];
				$delta      = abs( $rate_value - $vat_rate );

				if ( $delta <= $tolerance && ( null === $smallest_delta || $delta < $smallest_delta ) ) {
					$best_match_id  = (int) $rate_id;
					$smallest_delta = $delta;
				}
			}
		}

		// Logger::log('[TAX MATCH] Best matched rate ID: ' . var_export($best_match_id, true));

		return $best_match_id;
	}


	/**
	 * Save invoice data to order and return updated billing address
	 *
	 * @param object   $data Data from Inpost API
	 * @param WC_Order $order Order object
	 * @param array    $billingAddress Billing address
	 *
	 * @return array Updated billing address
	 */
	public function setInvoiceDataToOrder( object $data, WC_Order $order, array $billingAddress ): array {
		if ( isset( $data->invoice_details ) ) {
			foreach ( (array) $data->invoice_details as $name => $value ) {
				$order->update_meta_data( 'impost_invoice_' . $name, $value );
			}

			$billingAddress = array(
				'company'      => $data->invoice_details->legal_form == 'PERSON' ? '' : $data->invoice_details->company_name,
				'first_name'   => $data->invoice_details->legal_form == 'PERSON' ? $data->invoice_details->name : '',
				'last_name'    => $data->invoice_details->legal_form == 'PERSON' ? $data->invoice_details->surname : '',
				'email'        => $data->invoice_details->mail,
				'phone'        => $data->account_info->phone_number->country_prefix . ' ' . $data->account_info->phone_number->phone,
				'address_1'    => $data->invoice_details->street . ' ' . ( $data->invoice_details->building ?? '' ) . ' ' . ( $data->invoice_details->flat ?? '' ),
				'address_2'    => '',
				'city'         => $data->invoice_details->city,
				'state'        => '',
				'postcode'     => $data->invoice_details->postal_code,
				'country'      => $data->invoice_details->country_code,
				'invoice_note' => $this->invoiceNote( $data->invoice_details ),
			);

			do_action(
				'inpostpay_invoice_details',
				$order,
				$data->invoice_details
			);
		}

		return $billingAddress;
	}

	/**
	 * Generates a note for the invoice based on provided invoice details.
	 *
	 * @param object $invoiceDetails The invoice details containing additional information and tax ID.
	 *
	 * @return string A formatted invoice note including additional information and tax ID if available.
	 */
	private function invoiceNote( object $invoiceDetails ): string {
		$invoiceNote = '';
		if ( isset( $invoiceDetails->additional_information ) ) {
			$invoiceNote = $invoiceDetails->additional_information . ' \n ';
		}
		if ( isset( $invoiceDetails->tax_id ) ) {
			$invoiceNote .= __( 'Tax id', 'inpost-pay' ) . ':';
			$invoiceNote .= ( $invoiceDetails->tax_id_prefix ?? ' ' ) . ' ' . $invoiceDetails->tax_id;
		}

		return $invoiceNote;
	}

	/**
	 * Stores Inpost Pay specific order meta data.
	 *
	 * @param object         $data Response from Inpost Pay API.
	 * @param WC_Order       $order Order object.
	 * @param array          $deliveryCost Delivery cost.
	 * @param GroupInterface $baseGroup Shipping cost group.
	 * @param array          $billingAddress Billing address.
	 * @param array          $shippingAddress Shipping address.
	 *
	 * @throws WC_Data_Exception
	 */
	public function store_order_meta( object $data, WC_Order $order, $deliveryCost, $baseGroup, array $billingAddress, array $shippingAddress ): void {
		if ( isset( $data->delivery->delivery_codes ) ) {
			$order->update_meta_data(
				'delivery_codes',
				implode( ',', $data->delivery->delivery_codes )
			);
		}

		if ( $baseGroup === null ) {
			$order->update_meta_data(
				'izi_delivery_type_code',
				DigitalProduct::DELIVERY_TYPE_DIGITAL
			);
			$deliveryCost['delivery_options']             = array();
			$deliveryCost['base_delivery_price']['net']   = 0;
			$deliveryCost['base_delivery_price']['gross'] = 0;
			$deliveryCost['base_delivery_price']['vat']   = 0;
		} else {
			$order->update_meta_data(
				'izi_delivery_type_code',
				$baseGroup->getDeliveryTypeCode()
			);
			$order->update_meta_data(
				'_easypack_send_method',
				( $data->delivery->delivery_type == 'APM' ? 'parcel_machine' : 'courier' )
			);
		}

		$order->update_meta_data(
			'izi_delivery_cost',
			$deliveryCost
		);

		$order->update_meta_data(
			'origin_phone_number',
			json_encode( $data->account_info->phone_number )
		);

		$order->update_meta_data(
			'inpost_account_info',
			serialize( $data->account_info )
		);
		$order->update_meta_data(
			'inpost_consents',
			serialize( $data->consents )
		);

		$order->set_address( $billingAddress, 'billing' );
		$order->set_address( $shippingAddress, 'shipping' );

		$order->set_payment_method_title( 'Inpost Pay' );

		if ( $data->order_details->payment_type === 'CASH_ON_DELIVERY' ) {
			$value = esc_attr( get_option( 'izi_event_cod_AUTHORIZED' ) );
			$order->set_status( $value, 'Zamówienie Inpost Pay' );
		} else {
			$order->set_status( 'wc-on-hold', 'Zamówienie Inpost Pay' );
		}

		$order->set_customer_note( isset( $data->order_details->order_comments ) ? $data->order_details->order_comments : '' );

		if ( isset( $data->delivery->delivery_point ) ) {
			do_action(
				'inpostpay_delivery_point_details',
				$order,
				$data->delivery->delivery_point
			);
			$order->update_meta_data(
				'delivery_point',
				$data->delivery->delivery_point
			);
			$order->update_meta_data(
				'parcel_machine_id',
				$data->delivery->delivery_point
			);
			// Added for compatibility with InPost ShipX
			$order->update_meta_data(
				'_parcel_machine_id',
				$data->delivery->delivery_point
			);
		}
		$order->update_meta_data(
			'izi_payment_type',
			$data->order_details->payment_type
		);

		if ( isset( $data->delivery->mail ) ) {
			$order->update_meta_data( '_inpost_delivery_mail', $data->delivery->mail );
		}

		( new Analytics() )->store_in_order_meta( $order );

		$this->store_digital_delivery_email( $order, $data );

		if ( is_user_logged_in() ) {
			$current_user = wp_get_current_user();
			if ( $current_user && isset( $current_user->user_email ) ) {
				$order->update_meta_data( '_original_user_email', $current_user->user_email );
			}
		}
	}

	/**
	 * Stores the digital delivery email for the order in the `inpost_pay_digital_delivery_email` meta field.
	 *
	 * @param WC_Order $order The WooCommerce order object.
	 * @param object   $data Parsed data from the request containing order details.
	 */
	private function store_digital_delivery_email( WC_Order $order, object $data ): void {
		if ( ! property_exists( $data->delivery, 'digital_delivery_email' ) ) {
			return;
		}

		$order->update_meta_data( 'inpost_pay_digital_delivery_email', $data->delivery->digital_delivery_email );
	}

	/**
	 * Maps delivery price to proper format.
	 *
	 * @param WC_Order $order Order object.
	 *
	 * @return array
	 */
	private function mapDeliveryPrice( WC_Order $order ): array {
		$price = array();

		$shipping_total_gross = $order->get_shipping_total();
		$shipping_tax         = $order->get_shipping_tax();
		$shipping_total_net   = $shipping_total_gross - $shipping_tax;

		$price['gross'] = number_format(
			$shipping_total_gross,
			2,
			'.',
			''
		);
		$price['net']   = number_format(
			$shipping_total_net,
			2,
			'.',
			''
		);
		$price['tax']   = number_format(
			$shipping_tax,
			2,
			'.',
			''
		);

		return $price;
	}

	/**
	 * Normalize order ID to int.
	 *
	 * Accepts int or numeric string (e.g. '123'), rejects any other type.
	 *
	 * @param mixed $oid Order ID to normalize.
	 *
	 * @return int|null Returns int if valid, null otherwise.
	 */
	private function normalizeOrderId( $oid ): ?int {
		if ( is_int( $oid ) ) {
			return $oid > 0 ? $oid : null;
		}

		if ( is_string( $oid ) && ctype_digit( $oid ) ) {
			$normalized = (int) $oid;

			return $normalized > 0 ? $normalized : null;
		}

		return null;
	}

	/**
	 * Handles custom order ID creation using `inpost_pay_custom_order_id` filter.
	 *
	 * @param object        $parsedData Parsed data from Inpost Pay API.
	 * @param int           $oid Order ID (guaranteed to be int by normalizeOrderId).
	 * @param WC_Order|null $order Order object.
	 *
	 * @return int|string Returns custom alias (string) or original order ID (int).
	 */
	private function handleCustomOrderId( object $parsedData, int $oid, ?WC_Order $order ) {
		$customOrderId = apply_filters( 'inpost_pay_custom_order_id', null, $parsedData, $oid, $order );

		if ( $customOrderId !== null && ! is_scalar( $customOrderId ) ) {
			Logger::log( 'Custom order ID rejected: must be scalar, ' . gettype( $customOrderId ) . ' given' );
			$customOrderId = null;
		}

		if ( $customOrderId !== null && $order !== null ) {
			try {
				OrderAliasHelper::createAlias( (string) $customOrderId, $oid );
				Logger::log( "Alias order ID successfully stored: $customOrderId -> $oid" );

				return (string) $customOrderId;
			} catch ( Throwable $e ) {
				Logger::log( 'Custom alias rejected or failed: ' . $e->getMessage() );
			}
		}

		return $oid;
	}

	/**
	 * Finalizes order creation by:
	 * 1. Generating order response using WooCommerceOrder class.
	 * 2. Checking if the order should be deleted (e.g. if it's empty).
	 * 3. Completing order creation.
	 *
	 * @param int|string $finalOrderId ID of the order that was created in WooCommerce (int) or alias (string).
	 * @param WC_Order   $order WooCommerce order object.
	 * @param int        $realOrderId Real ID of the order in WooCommerce.
	 * @param string     $redir Redirect url.
	 * @param object     $parsedData Parsed data from the request.
	 *
	 * @return string|array JSON-stringified order response or an error array.
	 * @throws Exception
	 */
	private function finalizeOrder( $finalOrderId, WC_Order $order, int $realOrderId, string $redir, object $parsedData ) {
		$this->cart_session->set_order_to_cart( $parsedData->order_details->basket_id, $realOrderId, $redir );

		$wooOrderResponse = $this->getOrderResponse( $finalOrderId, $order );
		if ( is_array( $wooOrderResponse ) && isset( $wooOrderResponse['error'] ) ) {
			return $wooOrderResponse;
		}

		if ( $this->shouldDeleteEmptyOrder( $wooOrderResponse, $realOrderId ) ) {
			return array( 'error' => 'Order not created' );
		}

		$this->completeOrderCreation( $finalOrderId, $order, $realOrderId, $redir, $parsedData );

		return $wooOrderResponse;
	}

	/**
	 * Attempts to generate an order response using WooCommerceOrder class.
	 *
	 * @param string   $finalOrderId ID of the order that was created in WooCommerce.
	 * @param WC_Order $order WooCommerce order object.
	 *
	 * @return string|array JSON-stringified order response or an error array.
	 * @throws Exception If something goes wrong.
	 */
	private function getOrderResponse( string $finalOrderId, WC_Order $order ) {
		try {
			Logger::log( '[CREATE_ORDER] handleRequest: WooCommerceOrder::getOrder' );

			return WooCommerceOrder::getOrder( $finalOrderId, $order )->encode();
		} catch ( CantGetOrderObjectException $e ) {
			$this->logException( $e, 'Order response generation failed' );

			Logger::log( '[CREATE_ORDER] handleRequest: WooCommerceOrder::getOrder failed: ' . $e->getMessage() );

			return array( 'error' => $e->getMessage() );
		}
	}

	/**
	 * Checks if the created order is empty and should be deleted.
	 *
	 * This method is called after the order is created, and it checks if the order contains any products.
	 * If the order is empty, it empties the cart, deletes the order from WooCommerce and returns true.
	 * Otherwise, it returns false.
	 *
	 * @param string $wooOrderResponse The response from WooCommerceOrder::getOrder.
	 * @param int    $realOrderId The real WooCommerce order ID (not alias).
	 *
	 * @return bool If the order was deleted.
	 */
	private function shouldDeleteEmptyOrder( string $wooOrderResponse, int $realOrderId ): bool {
		$created_order_for_api = json_decode( $wooOrderResponse );

		if ( ! property_exists( $created_order_for_api, 'products' ) || empty( $created_order_for_api->products ) ) {
			Logger::log( '[CREATE_ORDER] handleRequest: no products found, delete order' );

			if ( is_object( WC()->cart ) ) {
				WC()->cart->empty_cart();
			}

			$empty_order = wc_get_order( $realOrderId );
			if ( $empty_order && is_object( $empty_order ) ) {
				$order_deleted = $empty_order->delete( true );
				if ( $order_deleted ) {
					Logger::log( '[CREATE_ORDER] handleRequest: deletion complete' );

					return true;
				}
			}
		}

		return false;
	}

	/**
	 * Completes the order creation process by emptying the cart, logging the event,
	 * and assigning the order to the CartSession. Also triggers checkout hooks.
	 *
	 * @param string   $finalOrderId The ID of the order that was finalized in WooCommerce.
	 * @param WC_Order $order The WooCommerce order object.
	 * @param int      $realOrderId The real ID of the order in WooCommerce.
	 * @param bool     $redir Determines if the customer should be redirected the post order creation.
	 * @param object   $parsedData Parsed data from the request containing order details.
	 *
	 * @return void
	 */
	private function completeOrderCreation( string $finalOrderId, WC_Order $order, int $realOrderId, bool $redir, object $parsedData ): void {
		WC()->cart->empty_cart();
		if ( is_user_logged_in() ) {
			$blog_id = get_current_blog_id();
			delete_user_meta( get_current_user_id(), '_woocommerce_persistent_cart_' . $blog_id );
		}
		if ( WC()->session ) {
			WC()->session->__unset( 'cart' );
			WC()->session->__unset( 'applied_coupons' );
			WC()->session->__unset( 'cart_totals' );
			WC()->session->set( 'cart_hash', '' );
			WC()->session->set_customer_session_cookie( true );
			WC()->session->save_data();
		}
		wc_setcookie( 'woocommerce_items_in_cart', 0 );
		wc_setcookie( 'woocommerce_cart_hash', '' );
		do_action( 'inpost_pay_order_created', $realOrderId, $parsedData );

		// Logger::debug( '[SESSION] After order: ' . var_export([
		// 'customer_id' => WC()->session->get_customer_id(),
		// 'basket_id'   => WC()->session->get( 'basket_id' ),
		// 'cart'        => WC()->cart->get_cart(),
		// ], true) );

		$executor = new OrderHooksExecutor();
		$executor->trigger_checkout_hooks( $order, $realOrderId, (array) $parsedData );
	}
}
