<?php
/**
 * Main plugin class file.
 *
 * @package         InPost Pay
 * @author          iLabs
 * @copyright       Copyright (c) 2023 iLabs
 * @since         1.0.0
 */

declare( strict_types=1 );

namespace Ilabs\Inpost_Pay;

use Ilabs\Inpost_Pay\Lib\config\ShippingCost\ShippingMappingSettingsManager;
use Ilabs\Inpost_Pay\Lib\InPostIzi;
use Ilabs\Inpost_Pay\Lib\Payment\Virtual_Payment_Method_Utils;
use Isolated\Inpost_Pay\Ilabs\Ilabs_Plugin\Abstract_Ilabs_Plugin;
use Isolated\Inpost_Pay\Ilabs\Ilabs_Plugin\Woocommerce_Logger;
use Isolated\Inpost_Pay\Isolated_Guzzlehttp\GuzzleHttp\Client;
use Ilabs\Inpost_Pay\Lib\omnibus\Plugin as Omnibus_Plugin;
use Ilabs\Inpost_Pay\InpostPay;


/**
 * Main plugin class for InPost Pay functionality.
 *
 * Handles the core plugin functionality, initialization, and integration with WooCommerce.
 *
 * @package Ilabs\Inpost_Pay
 * @since   1.0.0
 */
class Plugin extends Abstract_Ilabs_Plugin {

	public const SHORT_SLUG          = 'inpost_pay';
	public static ?string $logger_id = null;
	private ?Omnibus_Plugin $omnibus = null;

	/**
	 * Returns an instance of the Guzzle HTTP client.
	 *
	 * This function returns a new instance of the Guzzle HTTP client, which can be used to make HTTP requests.
	 *
	 * @return Client The Guzzle HTTP client instance.
	 * @since 1.0.0
	 */
	public function get_guzzle_client_instance(): Client {
		return new Client();
	}

	/**
	 * Returns a unique logger ID for the plugin.
	 *
	 * This function generates a unique logger ID for the plugin, which is used to identify the plugin in the logs.
	 *
	 * @return string The unique logger ID.
	 */
	public function get_logger_id(): string {
		if ( ! self::$logger_id ) {
			self::$logger_id = substr(
				str_shuffle( '0123456789abcdefghijklmnopqrstuvwxyz' ),
				0,
				6
			);
		}

		return self::$logger_id;
	}

	/**
	 * Returns a ShippingMappingSettingsManager object for the given zone ID.
	 *
	 * This function returns a ShippingMappingSettingsManager object, which is used to manage the shipping cost settings for a given zone ID.
	 *
	 * @param int|null $zone_id The zone ID for which to return the ShippingMappingSettingsManager object. If not provided, it will default to null.
	 * @return ShippingMappingSettingsManager The ShippingMappingSettingsManager object for the given zone ID.
	 * @since 1.0.0
	 */
	public function shipping_cost_settings( ?int $zone_id = null ): ShippingMappingSettingsManager {
		return new ShippingMappingSettingsManager( $zone_id );
	}

	/**
	 * Enqueue frontend scripts for the plugin.
	 *
	 * This function enqueues the frontend scripts for the plugin, which includes
	 * the thank-you page CSS and the IPP frontend CSS.
	 *
	 * @since 1.0.0
	 */
	public function enqueue_frontend_scripts() {
		wp_enqueue_style(
			'inpostpay-thank-you',
			plugins_url( '../assets/css/thank-you.css', __FILE__ ),
			array(),
			$this->get_plugin_version()
		);
		wp_enqueue_style(
			'inpostpay-ippfront',
			plugins_url( '../assets/css/ippfront.css', __FILE__ ),
			array(),
			$this->get_plugin_version()
		);
	}

	/**
	 * This function is called before the plugin is initialized.
	 *
	 * @since 1.0.0
	 */
	protected function before_init() {
		if ( $this->omnibus_enabled() ) {
			$this->get_omnibus()->before_init();
		}

		add_action(
			'plugins_loaded',
			array( InpostPay::class, 'get_instance' )
		);
		register_activation_hook(
			__FILE__,
			array( InpostPay::class, 'activate' )
		);
		register_deactivation_hook(
			__FILE__,
			array( InpostPay::class, 'deactivate' )
		);
		$migration = new Migration();
		$migration->run();
	}
	/**
	 * Enqueue dashboard scripts
	 *
	 * If the omnibus plugin is enabled, it will enqueue frontend scripts.
	 * Then it will enqueue admin style and script.
	 * If the current screen is one of the plugin's admin pages, it will enqueue additional scripts.
	 *
	 * @since 1.0.0
	 */
	public function enqueue_dashboard_scripts() {
		if ( $this->omnibus_enabled() ) {
			$this->get_omnibus()->enqueue_frontend_scripts();
		}

		$current_screen = get_current_screen();

		wp_enqueue_style(
			'inpostpay',
			plugins_url( '../assets/css/admin-style.css', __FILE__ ),
			array(),
			$this->get_plugin_version()
		);

		wp_enqueue_style(
			'inpostpay-select2',
			plugins_url( '../assets/css/select2.min.css', __FILE__ ),
			array(),
			$this->get_plugin_version()
		);

		wp_register_script(
			'inpostpay-admin-script',
			plugins_url( '../assets/js/admin-script.js', __FILE__ ),
			array(),
			$this->get_plugin_version()
		);
		wp_enqueue_script( 'inpostpay-admin-script' );

		if ( $current_screen && strpos( $current_screen->id, 'inpost-pay-hot-products' ) !== false ) {
			wp_enqueue_style(
				'inpostpay-hotproducts',
				plugins_url( '../assets/css/hotproducts.css', __FILE__ ),
				array(),
				$this->get_plugin_version()
			);
			wp_register_script(
				'inpostpay-admin-hotproducts',
				plugins_url( '../assets/js/admin-hotproducts.js', __FILE__ ),
				array(),
				$this->get_plugin_version()
			);
			wp_enqueue_script( 'inpostpay-admin-hotproducts' );
		}

		if ( $current_screen && strpos( $current_screen->id, 'inpost-pay-unavailable-products' ) !== false ) {
			wp_enqueue_style(
				'inpostpay-unavailable',
				plugins_url( '../assets/css/unavailable.css', __FILE__ ),
				array(),
				$this->get_plugin_version()
			);
			wp_register_script(
				'inpostpay-admin-unavailable',
				plugins_url( '../assets/js/admin-unavailable.js', __FILE__ ),
				array(),
				$this->get_plugin_version()
			);
			wp_enqueue_script( 'inpostpay-admin-unavailable' );
		}

		if ( $current_screen && strpos( $current_screen->id, 'inpost-pay-unavailable-categories' ) !== false ) {
			wp_enqueue_style(
				'inpostpay-unavailable',
				plugins_url( '../assets/css/unavailable.css', __FILE__ ),
				array(),
				$this->get_plugin_version()
			);
			wp_register_script(
				'inpostpay-admin-unavailable-categories',
				plugins_url( '../assets/js/admin-unavailable-categories.js', __FILE__ ),
				array(),
				$this->get_plugin_version()
			);
			wp_enqueue_script( 'inpostpay-admin-unavailable-categories' );
		}

		wp_enqueue_script(
			'InpostIziJavsscript',
			InPostIzi::getJsUrl(),
			array(),
			$this->get_plugin_version(),
			true
		);
	}

	/**
	 * Check if the omnibus plugin is enabled.
	 *
	 * This function always returns true, as the omnibus plugin is required for the InPost Pay plugin to work.
	 *
	 * @return bool Always true.
	 *
	 * @since 1.5.0
	 */
	public function omnibus_enabled(): bool {
		return true;
	}
	/**
	 * Initialize the plugin.
	 *
	 * If the omnibus plugin is enabled, it will initialize the omnibus plugin.
	 * Then it will register the virtual payment method.
	 *
	 * @since 1.0.0
	 */
	public function init() {
		if ( $this->omnibus_enabled() ) {
			$this->omnibus->init();
		}

		( new Virtual_Payment_Method_Utils() )->register_virtual_payment_method();
	}

	/**
	 * Returns the Omnibus plugin instance.
	 *
	 * If the Omnibus plugin instance is not set, it will initialize a new instance.
	 * Then it will return the Omnibus plugin instance.
	 *
	 * @return Omnibus_Plugin The Omnibus plugin instance.
	 *
	 * @since 1.5.0
	 */
	public function get_omnibus(): Omnibus_Plugin {
		if ( ! $this->omnibus ) {
			$this->omnibus = new Omnibus_Plugin();
		}

		return $this->omnibus;
	}
	/**
	 * Retrieves the version of the InPost Pay plugin.
	 *
	 * This function uses the WordPress `get_plugin_data` function to retrieve the version of the InPost Pay plugin.
	 *
	 * @return string The version of the InPost Pay plugin.
	 * @since 1.0.0
	 */
	public function get_plugin_version(): string {
		$plugin_data = get_plugin_data( plugin_dir_path( __FILE__ ) . '../inpost-pay.php', true, false );

		return $plugin_data['Version'];
	}

	/**
	 * Adds hooks for when the plugins are loaded.
	 *
	 * Adds an action hook for the admin menu to register the dashboard menu.
	 * If the Omnibus plugin is enabled, it will call the `plugins_loaded_hooks` function on the Omnibus plugin instance.
	 *
	 * @since 1.0.0
	 */
	protected function plugins_loaded_hooks() {
		add_action( 'admin_menu', array( DashboardMenu::class, 'registerMenu' ), 20 );

		if ( $this->omnibus_enabled() ) {
			$this->get_omnibus()->plugins_loaded_hooks();
		}
	}

	/**
	 * Retrieves a Woocommerce_Logger instance.
	 *
	 * If the class is an instance of Omnibus_Plugin, it will prefix the log ID with the Omnibus short slug.
	 * Otherwise, it will prefix the log ID with the InPost Pay short slug.
	 * If the log ID is not provided, it will default to the plugin's slug.
	 * If the izi_debug option is not enabled, it will set the logger to null.
	 *
	 * @param string|null $log_id Optional log ID to use.
	 *
	 * @return Woocommerce_Logger The Woocommerce_Logger instance.
	 * @throws \Exception If the log ID is not provided.
	 * @since 1.0.0
	 */
	public function get_woocommerce_logger( ?string $log_id = null ): Woocommerce_Logger {
		if ( $this instanceof Omnibus_Plugin ) {
			$log_id = $log_id
				? $this->prefix_by_short_slug( 'Omnibus_' . $log_id )
				: $this->get_from_config( 'slug' );
		} else {
			$log_id = $log_id
				? $this->prefix_by_short_slug( $log_id )
				: $this->get_from_config( 'slug' );
		}

		$logger = new Woocommerce_Logger( $log_id );

		if ( ! get_option( 'izi_debug' ) ) {
			$logger->set_null_logger( true );
		}

		return $logger;
	}

	/**
	 * Prefixes a string by the plugin's short slug.
	 *
	 * @param string $slug The string to prefix.
	 * @return string The prefixed string.
	 * @since 1.0.0
	 */
	public function prefix_by_short_slug( string $slug ): string {
		return self::SHORT_SLUG . '_' . $slug;
	}
}
