<?php
/**
 * InPost Izi main integration class.
 *
 * @package Ilabs\Inpost_Pay\Lib
 */

namespace Ilabs\Inpost_Pay\Lib;

use Ilabs\Inpost_Pay\hooks\LogInMonitor;
use Ilabs\Inpost_Pay\Lib\config\widget_v2\WidgetV2SizeConfig;
use Ilabs\Inpost_Pay\Lib\helpers\LangHelper;
use Ilabs\Inpost_Pay\TokenCache;

/**
 * Class InPostIzi
 *
 * @package Ilabs\Inpost_Pay
 *
 * @since 1.0.0
 */
class InPostIzi {

	/**
	 * Environment type.
	 *
	 * @var int
	 */
	const ENVIRONMENT_DEVELOP    = 1;
	const ENVIRONMENT_PRODUCTION = 2;
	const ENVIRONMENT_SANDBOX    = 3;

	/**
	 * Binding place for the product card.
	 *
	 * @var string
	 */
	const BINDING_PLACE_PRODUCT_CARD = 'PRODUCT_CARD';

	/**
	 * Binding place for basket summary.
	 *
	 * @var string
	 */
	const BINDING_PLACE_BASKET_SUMMARY = 'BASKET_SUMMARY';

	/**
	 * Binding place for order create.
	 *
	 * @var string
	 */
	const BINDING_PLACE_ORDER_CREATE = 'ORDER_CREATE';

	/**
	 * Binding place for checkout page.
	 *
	 * @var string
	 */
	const BINDING_PLACE_CHECKOUT_PAGE = 'CHECKOUT_PAGE';

	/**
	 * Binding place for login page.
	 *
	 * @var string
	 */
	const BINDING_PLACE_LOGIN_PAGE = 'LOGIN_PAGE';

	/**
	 * Binding place for basket popup.
	 *
	 * @var string
	 */
	const BINDING_PLACE_BASKET_POPUP = 'BASKET_POPUP';

	/**
	 * Binding place for thank you page.
	 *
	 * @var string
	 */
	const BINDING_PLACE_THANK_YOU_PAGE = 'THANK_YOU_PAGE';

	/**
	 * Binding place for minicart page.
	 *
	 * @var string
	 */
	const BINDING_PLACE_MINICART_PAGE = 'MINICART_PAGE';

	/**
	 * Translations domain.
	 *
	 * @var string
	 */
	public const TRANSLATION_DOMAIN = 'inpost-pay';

	/**
	 * Basket ID.
	 *
	 * @var null|string
	 */
	public static ?string $inpostIziBasketId = null;

	/**
	 * Instance of the class (singleton).
	 *
	 * @var ?InPostIzi
	 */
	protected static ?InPostIzi $instance = null;

	/**
	 * Storage for the instance.
	 *
	 * @var Storage
	 */
	private static Storage $storage;

	/**
	 * Block put functionality.
	 *
	 * @var bool
	 */
	private static bool $block_put = false;

	/**
	 * Client ID for authentication.
	 *
	 * @var string|null
	 */
	private static ?string $client_id = null;

	/**
	 * Client secret for authentication.
	 *
	 * @var string|null
	 */
	private static ?string $client_secret = null;

	/**
	 * Current environment type.
	 *
	 * @var int|null
	 */
	private static ?int $environment = null;

	/**
	 * Logger class name.
	 *
	 * @var string|null
	 */
	private static ?string $logger_class = null;

	/**
	 * Token cache object (for token storage).
	 *
	 * @var TokenCache|null
	 */
	private static ?TokenCache $token_cache = null;

	/**
	 * Controller instance for event handling.
	 *
	 * @var Controller
	 */
	protected Controller $controller;

	/**
	 * Constructor.
	 *
	 * Initializes the controller and attaches hook for login monitoring.
	 */
	public function __construct() {
		$this->controller = new Controller();
		( new LogInMonitor() )->attach_hook();
	}

	/**
	 * Get logger class name.
	 *
	 * @return string The name of the logger class as a string.
	 */
	public static function getLoggerClass(): string {
		return self::$logger_class;
	}

	/**
	 * Set logger class name.
	 *
	 * @param string $class Logger class to set.
	 */
	public static function setLoggerClass( string $class ): void {
		self::$logger_class = $class;
	}

	/**
	 * Check if block put functionality is enabled.
	 *
	 * @return bool True if block put is enabled, false otherwise.
	 */
	public static function isBlockPut(): bool {
		return self::$block_put;
	}

	/**
	 * Get storage instance (singleton).
	 *
	 * @return Storage The storage instance.
	 */
	public static function getStorage(): Storage {
		if ( ! isset( self::$storage ) ) {
			self::$storage = new Storage();
		}
		return self::$storage;
	}

	/**
	 * Get current environment type.
	 *
	 * @return int The current environment type.
	 */
	public static function getEnvironment(): int {
		return self::$environment;
	}

	/**
	 * Set current environment type.
	 *
	 * @param int $environment Environment to set.
	 */
	public static function setEnvironment( int $environment ): void {
		self::$environment = $environment;
	}

	/**
	 * Sanitize and validate an environment value.
	 *
	 * Casts the input to int and returns it when it matches one of the valid
	 * environment identifiers. Falls back to ENVIRONMENT_DEVELOP otherwise.
	 * Safe to use directly as a WordPress register_setting sanitize_callback.
	 *
	 * @param mixed $value Raw value to sanitize.
	 *
	 * @return int
	 *
	 * @since 2.0.7.1
	 */
	public static function sanitize_environment( $value ): int {
		$int = (int) $value;

		return in_array( $int, array( self::ENVIRONMENT_DEVELOP, self::ENVIRONMENT_PRODUCTION, self::ENVIRONMENT_SANDBOX ), true )
			? $int
			: self::ENVIRONMENT_DEVELOP;
	}

	/**
	 * Get API URL based on environment.
	 *
	 * @return string The API URL for the current environment.
	 *
	 * @since 2.0.6
	 */
	public static function getApiUrl(): string {
		switch ( self::$environment ) {
			case self::ENVIRONMENT_PRODUCTION:
				return 'https://api.inpost.pl';
			case self::ENVIRONMENT_SANDBOX:
				return 'https://sandbox-api.inpost.pl';
			default:
				return 'https://uat-api.inpost.pl';
		}
	}

	/**
	 * Get authentication URL based on environment.
	 *
	 * @return string The authentication URL for the current environment.
	 */
	public static function getAuthUrl(): string {
		switch ( self::$environment ) {
			case self::ENVIRONMENT_PRODUCTION:
				return 'https://login.inpost.pl';
			case self::ENVIRONMENT_SANDBOX:
				return 'https://sandbox-login.inpost.pl';
			default:
				return 'https://uat-auth.easypack24.net';
		}
	}

	/**
	 * Get link URL based on environment.
	 *
	 * @return string The link URL for the current environment.
	 */
	public static function getLinkUrl(): string {
		switch ( self::$environment ) {
			case self::ENVIRONMENT_PRODUCTION:
				return 'inpost://izilink';
			case self::ENVIRONMENT_SANDBOX:
				return 'inpostsandbox://izilink';
			default:
				return 'inpostuat://izilink';
		}
	}

	/**
	 * Get JavaScript URL based on environment.
	 *
	 * @return string The JavaScript URL for the current environment.
	 */
	public static function getJsUrl(): string {
		switch ( self::$environment ) {
			case self::ENVIRONMENT_PRODUCTION:
				return 'https://inpostpay-widget-v2.inpost.pl/inpostpay.widget.v2.js';
			default:
				return 'https://sandbox-inpostpay-widget-v2.inpost.pl/inpostpay.widget.v2.js';
		}
	}

	/**
	 * Enable block put functionality.
	 */
	public static function blockPut(): void {
		self::$block_put = true;
	}

	/**
	 * Disable block put functionality.
	 */
	public static function unblockPut(): void {
		self::$block_put = false;
	}

	/**
	 * Set token cache object.
	 *
	 * @param object $object Token cache object to set.
	 */
	public static function setTokenCacheObject( object $object ): void {
		self::$token_cache = $object;
	}

	/**
	 * Get cached token (if available).
	 *
	 * @param bool $renew If true, force token renewal before returning.
	 * @return string|null The cached token or null if not available/expired.
	 */
	public static function getCachedToken( bool $renew = false ): ?string {
		if ( self::$token_cache ) {
			return self::$token_cache->getCachedToken( $renew );
		}
		return null;
	}

	/**
	 * Set cached token.
	 *
	 * @param string $token Token to store.
	 * @param int    $expiration Expiration time for the token in seconds.
	 */
	public static function setCachedToken( string $token, int $expiration ): void {
		if ( self::$token_cache ) {
			self::$token_cache->setCachedToken( $token, $expiration );
		}
	}

	/**
	 * Get instance of the class (singleton).
	 *
	 * @return InPostIzi The instance of the class.
	 */
	public static function get_instance(): InPostIzi {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new static();
		}
		return self::$instance;
	}

	/**
	 * Get client ID for authentication.
	 *
	 * @return string|null The client ID or null if not set.
	 */
	public static function get_client_id(): ?string {
		return self::$client_id;
	}

	/**
	 * Set client ID for authentication.
	 *
	 * @param string $client_id Client ID to set.
	 */
	public static function set_client_id( string $client_id ): void {
		self::$client_id = $client_id;
	}

	/**
	 * Get client secret for authentication.
	 *
	 * @return string|null The client secret or null if not set.
	 */
	public static function get_client_secret(): ?string {
		return self::$client_secret;
	}

	/**
	 * Set client secret for authentication.
	 *
	 * @param string $client_secret Client secret to set.
	 */
	public static function set_client_secret( string $client_secret ): void {
		self::$client_secret = $client_secret;
	}

	/**
	 * Renders the InPost Izi button with various customization options.
	 *
	 * This method generates HTML for the InPost Izi button, allowing customization of its appearance
	 * and behavior based on the provided parameters. It can optionally echo the generated HTML.
	 *
	 * @param int|null    $product_id The product ID to associate with the button, or null for no association.
	 * @param bool        $print_view Whether to echo the generated HTML. Defaults to true.
	 * @param bool        $dark Whether to apply a dark theme to the button. Defaults to false.
	 * @param bool        $yellow Whether to apply a primary theme to the button. Defaults to false.
	 * @param string      $binding_place The binding place attribute for the button. Defaults to 'BASKET_POPUP'.
	 * @param string      $frame_style The rounding style for the button. Defaults to 'none'.
	 * @param bool|string $size The size of the button, or false to use the default size. Defaults to false.
	 * @param bool        $is_elementor Whether the button is rendered within an Elementor widget. Defaults to false.
	 *
	 * @return string The generated HTML for the InPost Izi button or print it when $echo is true.
	 */
	public static function render(
		?int $product_id = null,
		bool $print_view = true,
		bool $dark = false,
		bool $yellow = false,
		string $binding_place = 'BASKET_POPUP',
		string $frame_style = 'none',
		$size = false,
		bool $is_elementor = false
	) {
		$variation_css = $frame_style;
		if ( $dark ) {
			$variation_css .= ' dark';
		}
		if ( $yellow ) {
			$variation_css .= ' primary';
		}

		if ( $is_elementor ) {
			$variation_css .= ' ' . ( new WidgetV2SizeConfig() )->getArrayAsString();
		} else {
			$variation_css .= ' ' . ( new WidgetV2SizeConfig() )->getArrayAsString();
		}

		$binding_place_attr = esc_attr( $binding_place );
		$variation_attr     = esc_attr( $variation_css );
		$elementor_flag     = $is_elementor ? '1' : '0';
		$language_attr      = esc_attr( LangHelper::getWidgetLangAttr() );

		if ( $product_id ) {
			$alert_message = esc_attr( __( 'Please complete the required fields to use InpostPay.', 'inpost-pay' ) );
			$html          = sprintf(
				'<inpost-izi-button binding_place="%s" variation="%s" data-from-elementor="%s" data-product-id="%d" language="%s" data-alert-message="%s"></inpost-izi-button>',
				$binding_place_attr,
				$variation_attr,
				$elementor_flag,
				$product_id,
				$language_attr,
				$alert_message
			);
		} else {
			$html = sprintf(
				'<inpost-izi-button binding_place="%s" variation="%s" data-from-elementor="%s" language="%s"></inpost-izi-button>',
				$binding_place_attr,
				$variation_attr,
				$elementor_flag,
				$language_attr
			);
		}

		if ( $print_view ) {
			echo $html;
		}
		return $html;
	}

	/**
	 * Get controller instance.
	 *
	 * @return Controller The controller instance.
	 */
	public function get_controller(): Controller {
		return $this->controller;
	}
}
