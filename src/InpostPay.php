<?php
/**
 * Main InpostPay plugin class.
 *
 * This class is responsible for initializing the InPost Pay integration,
 * handling plugin setup, and coordinating various components.
 *
 * @package Ilabs\Inpost_Pay
 * @since   1.0.0
 */

namespace Ilabs\Inpost_Pay;

use Ilabs\Inpost_Pay\EntityLayer\Cache\PersistentCache;
use Ilabs\Inpost_Pay\filters\Delivery_Full_Cost_Filter;
use Ilabs\Inpost_Pay\filters\NewOrderEmailsFilter;
use Ilabs\Inpost_Pay\hooks\admin\AdminBillingFields;
use Ilabs\Inpost_Pay\hooks\admin\AdminEANValidatorHook;
use Ilabs\Inpost_Pay\hooks\admin\AdminHotProductUpdate;
use Ilabs\Inpost_Pay\hooks\admin\AdminNotices;
use Ilabs\Inpost_Pay\hooks\admin\AdminOrderUpdate;
use Ilabs\Inpost_Pay\hooks\admin\AdminPostUpdated;
use Ilabs\Inpost_Pay\hooks\admin\AdminProductGallerySetting;
use Ilabs\Inpost_Pay\hooks\admin\AdminSupportLogsDownload;
use Ilabs\Inpost_Pay\hooks\admin\AdminTransportForZoneMigration;
use Ilabs\Inpost_Pay\hooks\Coupon;
use Ilabs\Inpost_Pay\hooks\front\FrontBasketChange;
use Ilabs\Inpost_Pay\hooks\front\FrontCartCount;
use Ilabs\Inpost_Pay\hooks\front\FrontCurrencyMonitor;
use Ilabs\Inpost_Pay\hooks\front\FrontDisplayWidget;
use Ilabs\Inpost_Pay\hooks\front\FrontLogoutMonitor;
use Ilabs\Inpost_Pay\hooks\front\FrontOrderReceived;
use Ilabs\Inpost_Pay\hooks\front\FrontSessionCleanup;
use Ilabs\Inpost_Pay\hooks\front\FrontTemplateRedirect;
use Ilabs\Inpost_Pay\hooks\front\FrontWidgetV2;
use Ilabs\Inpost_Pay\hooks\OrderEmails;
use Ilabs\Inpost_Pay\hooks\RestOrderSanitizer;
use Ilabs\Inpost_Pay\hooks\WPDeskCodAmountFix;
use Ilabs\Inpost_Pay\Integration\Blocks\BlocksManager;
use Ilabs\Inpost_Pay\Integration\Currency\CurrencyStateManager;
use Ilabs\Inpost_Pay\Integration\Elementor\ElementorInpostWidget;
use Ilabs\Inpost_Pay\Lib\Authorization;
use Ilabs\Inpost_Pay\Lib\Cron;
use Ilabs\Inpost_Pay\Lib\exception\AuthorizationException;
use Ilabs\Inpost_Pay\Lib\helpers\CookieHelper;
use Ilabs\Inpost_Pay\Lib\helpers\HPOSHelper;
use Ilabs\Inpost_Pay\Lib\InPostIzi;
use Ilabs\Inpost_Pay\Lib\VirtualPage\VirtualPage;
use Ilabs\Inpost_Pay\rest\admin\product\Categories;
use Ilabs\Inpost_Pay\rest\admin\product\HotProducts;
use Ilabs\Inpost_Pay\rest\admin\product\Products;
use Ilabs\Inpost_Pay\rest\admin\unavailable\Unavailable;
use Ilabs\Inpost_Pay\rest\basket\Confirmation;
use Ilabs\Inpost_Pay\rest\basket\Delete;
use Ilabs\Inpost_Pay\rest\merchant\basket\Add;
use Ilabs\Inpost_Pay\rest\merchant\basket\Bind;
use Ilabs\Inpost_Pay\rest\merchant\basket\Binding;
use Ilabs\Inpost_Pay\rest\merchant\BasketBindingApiKeyGet;
use Ilabs\Inpost_Pay\rest\order\Create;
use Ilabs\Inpost_Pay\rest\order\Get;
use Ilabs\Inpost_Pay\rest\order\Update;
use Ilabs\Inpost_Pay\rest\widget\block\RenderBlock;
use Ilabs\Inpost_Pay\rest\widget\get\WidgetCheckoutPage;
use Ilabs\Inpost_Pay\rest\widget\get\WidgetLoginPage;
use Ilabs\Inpost_Pay\rest\widget\get\WidgetMinicart;
use Ilabs\Inpost_Pay\rest\widget\get\WidgetOrderCreate;
use Ilabs\Inpost_Pay\rest\widget\get\WidgetPlaceBasketSummary;
use Ilabs\Inpost_Pay\rest\widget\get\WidgetPlaceProductCard;
use Ilabs\Inpost_Pay\WooCommerce\WooCommerceInPostIzi;
use WC_Order;

/**
 * Main InpostPay plugin class.
 *
 * This class is responsible for initializing the InPost Pay integration,
 * handling plugin setup, and coordinating various components.
 *
 * @package Ilabs\Inpost_Pay
 * @since   1.0.0
 */
class InpostPay {
	private static ?InpostPay $instance = null;
	private ?WooCommerceInPostIzi $lib  = null;

	private PersistentCache $persistent_cache;

	/**
	 * InpostPay class.
	 *
	 * Main plugin class responsible for initializing the InPost Pay integration
	 * with WooCommerce, including REST API endpoints, hooks, and widgets.
	 *
	 * @package Ilabs\Inpost_Pay
	 * @since   1.0.0
	 */
	private function __construct() {
		if ( ! class_exists( 'woocommerce' ) ) {
			return;
		}

		InPostIzi::setEnvironment( InPostIzi::sanitize_environment( get_option( 'izi_environment' ) ) );
		InPostIzi::set_client_secret( esc_attr( get_option( 'izi_client_secret' ) ) );
		InPostIzi::set_client_id( esc_attr( get_option( 'izi_client_id' ) ) );
		InPostIzi::setLoggerClass( Logger::class );

		$this->persistent_cache = new PersistentCache();
		$token_cache            = new TokenCache();
		InPostIzi::setTokenCacheObject( $token_cache );
		$this->lib = WooCommerceInPostIzi::get_instance();

		( new Settings() )->register();

		$this->defineAssetsPublicPath();
		$this->init_admin();
		$this->init_frontend();

		if ( $this->persistent_cache->get( 'izi_is_authorized' ) ) {
			// phpcs:ignore WordPress.Security.NonceVerification.Missing -- This is not a form submission.
			if ( ! isset( $_COOKIE['izi_show'] ) && isset( $_GET['showIzi'] ) && 'true' === $_GET['showIzi'] ) {
				$_COOKIE['izi_show'] = 'true';
				setcookie( 'izi_show', 'true', time() + 3600 * 24, '/' );
			}

			$this->hide_functionality = esc_attr( get_option( 'izi_hide_functionality' ) ) ?? 'hidden';

			$this->attach_hooks();
			$this->initiateRestApi();
			( new Cron() )->schedule();

			if ( ! is_admin() && ! $this->canShow() ) {
				return;
			}

			if ( isset( $_SERVER['HTTP_ACCEPT'] ) && 'application/json' !== $_SERVER['HTTP_ACCEPT'] ) {
				if ( defined( 'REST_REQUEST' ) && REST_REQUEST ) {
					return;
				}

				add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_frontend_scripts' ) );

				( new VirtualPage() )->init();
			}

			if ( did_action( 'elementor/loaded' ) ) {
				add_action(
					'elementor/widgets/register',
					array( $this, 'register_elementor_widget' )
				);
			}
			$this->register_blocks_widget();
		}
	}

	/**
	 * Defines the public path to the plugin's assets folder.
	 *
	 * The `INPOST_PAY_ASSETS_PUBLIC_PATH` constant is used by the plugin to generate URLs to its assets.
	 *
	 * @since 2.0.4
	 */
	public function defineAssetsPublicPath() {
		define( 'INPOST_PAY_ASSETS_PUBLIC_PATH', plugin_dir_url( WOOCOMMERCE_INPOST_PAY_PLUGIN_FILE ) . 'assets/' );
	}

	private function init_admin() {
		$this->filterVisibleMetaAtBackoffice();
		$this->addOrderMetaBox();
	}

	/**
	 * Hides specific order item meta in WooCommerce backoffice.
	 *
	 * This function adds a filter to hide particular meta fields from being displayed
	 * in the order item meta section of WooCommerce backoffice. The hidden meta fields
	 * include 'inpost_account_info', 'inpost_consents', 'is_vat_exempt', 'izi_payment_type',
	 * and 'origin_phone_number'.
	 *
	 * @return void
	 */
	private function filterVisibleMetaAtBackoffice(): void {
		add_filter(
			'woocommerce_hidden_order_itemmeta',
			static function ( $arr ) {
				$arr[] = 'inpost_account_info';
				$arr[] = 'inpost_consents';
				$arr[] = 'is_vat_exempt';
				$arr[] = 'izi_payment_type';
				$arr[] = 'origin_phone_number';

				return $arr;
			}
		);
	}

	/**
	 * Adds a custom meta box to WooCommerce order edit screens.
	 *
	 * This function hooks into the 'add_meta_boxes' action to add a meta box
	 * on order edit pages. The meta box displays additional information
	 * related to InPost Pay, specifically for orders with a defined 'izi_payment_type'.
	 * The meta box is only added if the WooCommerce custom orders table feature is enabled
	 * or if the post type is 'shop_order'.
	 *
	 * @return void
	 */
	private function addOrderMetaBox(): void {

		add_action(
			'add_meta_boxes_shop_order',
			function ( $post ) {
				$order_id = is_object( $post ) ? (int) $post->ID : 0;
				$this->maybe_add_inpostpay_metabox( $order_id, 'shop_order' );
			},
			10,
			1
		);

		add_action(
			'add_meta_boxes_woocommerce_page_wc-orders',
			function () {
				$order_id = isset( $_GET['id'] ) ? absint( $_GET['id'] ) : 0;
				$this->maybe_add_inpostpay_metabox( $order_id, 'woocommerce_page_wc-orders' );
			},
			10,
			0
		);
	}

	/**
	 * Check if an InPost Pay meta box should be added to an order edit screen.
	 *
	 * This function verifies if the order has an InPost Pay payment type and adds
	 * a meta box displaying InPost Pay specific information if applicable.
	 *
	 * @param int    $order_id WooCommerce order ID.
	 * @param string $screen Current admin screen identifier.
	 *
	 * @return void
	 */
	private function maybe_add_inpostpay_metabox( int $order_id, string $screen ): void {
		if ( ! $order_id ) {
			return;
		}

		$order = wc_get_order( $order_id );
		if ( ! $order ) {
			return;
		}

		$helper         = new HPOSHelper( $order );
		$iziPaymentType = $helper->get_meta( 'izi_payment_type', true );

		if ( empty( $iziPaymentType ) ) {
			return;
		}

		add_meta_box(
			'izi_order_fields',
			__( 'InPost Pay', 'inpost-pay' ),
			static function () use ( $order ) {
				/** @var WC_Order $order Order object */
				$HPOSHelper = new HPOSHelper( $order );
				include __DIR__ . '/views/orderMetaBox.php';
			},
			$screen,
			'side',
			'core'
		);
	}

	/**
	 * Initializes frontend functionality and checks authorization status.
	 *
	 * This method verifies if the InPost Pay service is authorized by checking
	 * a persistent cache. If not cached, it attempts to obtain a new token.
	 * Upon successful authorization, it updates the 'izi_is_authorized' option
	 * and caches the authorization check for 1 hour to minimize API calls.
	 * Logs any authorization exceptions for debugging purposes.
	 *
	 * @return void
	 */
	private function init_frontend(): void {
		if ( ! $this->persistent_cache->get( 'izi_is_authorized' ) ) {
			$authorization = new Authorization();
			try {
				$authorization->getToken( true );
				$this->persistent_cache->set( 'izi_is_authorized', true, 60 * 60 );
			} catch ( AuthorizationException $ex ) {
				Logger::log( 'IZI NOT AUTHORIZED: ' . $ex->getMessage() );
			}
		}
	}

	/**
	 * Initialize plugin hooks and filters.
	 *
	 * Attaches all the hooks and filters required by the plugin.
	 *
	 * This method is called when the plugin is authorized and the user is not hiding the plugin's functionality.
	 *
	 * It registers:
	 * - filters for new order emails
	 * - hooks for:
	 *   - processing order received
	 *   - processing basket change
	 *   - processing cart count
	 *   - displaying widget v2
	 *   - displaying widget
	 *   - initializing session
	 *   - processing billing fields
	 *   - processing order update
	 *   - processing cron jobs
	 *   - processing template redirects
	 *   - processing coupons
	 *   - processing product updates
	 *   - processing order emails
	 *   - processing order sanitizers
	 *   - processing admin notices
	 */
	private function attach_hooks(): void {
		// rest
		( new RestOrderSanitizer() )->attach_hook();

		if ( $this->canShow() ) {
			( new FrontOrderReceived() )->attach_frontend_hook();
			( new FrontBasketChange() )->attach_frontend_hook();
			( new FrontCartCount() )->attach_frontend_hook();
			( new FrontWidgetV2() )->attach_frontend_hook();
			( new FrontDisplayWidget() )->attach_frontend_hook();
			( new FrontTemplateRedirect() )->attach_frontend_hook();
			( new FrontLogoutMonitor() )->attach_frontend_hook();
			( new FrontCurrencyMonitor() )->attach_frontend_hook();
			( new FrontSessionCleanup() )->attach_frontend_hook();
		}

		if ( ( defined( 'REST_REQUEST' ) && REST_REQUEST ) || is_admin() ) {
			( new AdminBillingFields() )->attach_hook();
			( new AdminOrderUpdate() )->attach_hook();
			( new AdminHotProductUpdate() )->attach_hook();
			( new AdminNotices() )->attach_hook();
			( new AdminEANValidatorHook() )->attach_hook();
			( new AdminPostUpdated() )->attach_hook();
			( new AdminTransportForZoneMigration() )->attach_hook();
			( new AdminProductGallerySetting() )->attach_hook();
			( new AdminSupportLogsDownload() )->attach_hook();
		}

		WooCommerceInPostIzi::get_instance();
		( new WPDeskCodAmountFix() )->attach_hook();
		( new Cron() )->attach_hook();
		( new Coupon() )->attach_hook();
		$order_emails = new NewOrderEmailsFilter();
		$order_emails->register_filters();
		( new OrderEmails( $order_emails ) )->attach_hook();
		( new Delivery_Full_Cost_Filter() )->register_filters();
	}

	/**
	 * Check if the plugin functionality can be shown on the current request.
	 *
	 * @return bool True when plugin functionality should be shown.
	 */
	private function canShow(): bool {
		$hide_functionality = esc_attr( get_option( 'izi_hide_functionality' ) ) ?? 'hidden';
		$show               = CookieHelper::get( 'izi_show' ) ?? false;

		return ! ( 'hidden' === $hide_functionality && false === $show );
	}

	/**
	 * Initiate all REST API endpoints.
	 *
	 * This function is responsible for registering all available REST API endpoints.
	 */
	private function initiateRestApi(): void {
		( new Confirmation() )->register();
		( new Create() )->register();
		( new Get() )->register();
		( new Update() )->register();
		( new rest\basket\Get() )->register();
		( new Delete() )->register();

		( new Binding() )->register();
		( new Add() )->register();
		( new Bind() )->register();
		( new rest\basket\Update() )->register();
		( new rest\basket\Product() )->register();
		( new BasketBindingApiKeyGet() )->register();

		( new WidgetOrderCreate() )->register();
		( new WidgetPlaceBasketSummary() )->register();
		( new WidgetPlaceProductCard() )->register();
		( new WidgetCheckoutPage() )->register();
		( new WidgetLoginPage() )->register();
		( new WidgetMinicart() )->register();

		( new RenderBlock() )->register();

		( new Products() )->register();
		( new Categories() )->register();
		( new HotProducts() )->register();

		( new Unavailable() )->register();

		( new rest\product\Get() )->register();
	}

	/**
	 * Registers the Inpost widget for the WordPress block editor.
	 *
	 * @Since 2.0.4
	 *
	 * @return void
	 */
	public function register_blocks_widget(): void {
		new BlocksManager();
	}

	/**
	 * Get the singleton instance of the InpostPay class.
	 *
	 * @return InpostPay The singleton instance.
	 */
	public static function get_instance() {
		if ( ! self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Activation hook for the plugin.
	 *
	 * Registers settings and checks if `izi_client_id` and `izi_client_secret` options are set.
	 * If they are, sets `izi_is_authorized` option to true.
	 */
	public static function activate(): void {
		set_transient( 'izi_inpost_pay_activating', true, 30 );
		register_setting(
			'inpost-izi',
			'izi_db_version',
			array(
				'type'    => 'string',
				'default' => '1.0',
			)
		);
		$migration = new Migration();
		$migration->run();

		register_setting(
			'inpost-izi',
			'izi_is_authorized',
			array(
				'type'    => 'bool',
				'default' => false,
			)
		);

		if ( get_option( 'izi_client_id' ) && get_option( 'izi_client_secret' ) ) {
			update_option( 'izi_is_authorized', true );
		}
	}

	/**
	 * Get the library instance.
	 *
	 * @return ?WooCommerceInPostIzi The library instance.
	 */
	public function get_lib(): ?WooCommerceInPostIzi {
		return $this->lib;
	}

	/**
	 * Deactivates the InpostPay plugin.
	 *
	 * This method is triggered when the plugin is deactivated. It stops any scheduled
	 * cron jobs related to the plugin by calling the deactivate method on the Cron class.
	 *
	 * @return void
	 */
	public function deactivate(): void {
		( new Cron() )->deactivate();
	}

	function add_sri_integrity_attribute( $html, $handle ) {

		$hashAlgorithm = 'sha256'; // The hash algorithm you used to create the SRI hash

		if ( $handle === 'InpostIziJavsscript' ) {
			$fileContents = file_get_contents( InPostIzi::getJsUrl() );
			$hashValue    = hash( $hashAlgorithm, $fileContents, true );
			$sriValue     = $hashAlgorithm . '-' . base64_encode( $hashValue );

			$html = str_replace(
				'<script',
				'<script integrity="' . $sriValue . '" crossorigin="anonymous"',
				$html
			);
		}

		return $html;
	}

	public function enqueue_frontend_scripts(): void {
		$version = random_int( 100, 100000 );

		wp_register_script( 'InpostpayWidgetV2', InPostIzi::getJsUrl(), array( 'jquery' ), $version, true );

		wp_enqueue_script( 'InpostpayWidgetV2' );

		wp_register_script( 'InpostIziJavsscriptWoocommerce', plugin_dir_url( __FILE__ ) . '../assets/js/woocommerceizi.js', array( 'jquery' ), $version, true );
		wp_enqueue_script( 'InpostIziJavsscriptWoocommerce' );

		$is_funnelkit_active = false;

		if ( function_exists( 'is_plugin_active' ) ) {
			$is_funnelkit_active = is_plugin_active( 'funnel-builder/funnel-builder.php' ) ||
									is_plugin_active( 'woofunnels-aero-checkout/woofunnels-aero-checkout.php' ) ||
									class_exists( 'WFACP_Core' );
		}

		wp_localize_script(
			'InpostIziJavsscriptWoocommerce',
			'InpostIziJavsscriptWoocommerce',
			array(
				'ajaxurl'                        => \WC_Ajax::get_endpoint( 'wc_ajax_inpost_add_product' ),
				'merchant_basket_delete_binding' => \WC_Ajax::get_endpoint( 'merchant_basket_delete_binding' ),
				'inpost_pay_bind'                => \WC_Ajax::get_endpoint( 'inpost_pay_bind' ),
				'inpost_pay_binding'             => \WC_Ajax::get_endpoint( 'inpost_pay_binding' ),
				'basket_binding_api_key'         => \WC_Ajax::get_endpoint( 'inpost_basket_binding_api_key_get' ),
				'home_url'                       => home_url( '/', 'absolute' ),
				'thank_you_url'                  => VirtualPage::get_thank_you_url(),
				'currency_restored'              => CurrencyStateManager::wasBindingRestored(),
				'refresh_after_add_to_cart'      => (bool) get_option( 'izi_refresh_after_add_to_cart', true ),
			)
		);

		add_action(
			'wp_head',
			function () {
				echo '<style>.post-type-archive-product .inpostizi-bind-button {margin: 0 auto;}</style>';
			},
			100
		);
	}

	/**
	 * Registers the Elementor Inpost widget.
	 *
	 * This method is called when Elementor is loaded and the plugin is authorized.
	 *
	 * @param \Elementor\Widgets_Manager $widgets_manager The Elementor widgets manager.
	 */
	public function register_elementor_widget( $widgets_manager ): void {
		$widgets_manager->register( new ElementorInpostWidget() );
	}

	/**
	 * Gets the path to the JavaScript assets folder.
	 *
	 * @return string The path to the JavaScript assets folder.
	 */
	public function get_js_assets_path(): string {
		return plugin_dir_url( WOOCOMMERCE_INPOST_PAY_PLUGIN_FILE ) . '/assets/js/';
	}
}
