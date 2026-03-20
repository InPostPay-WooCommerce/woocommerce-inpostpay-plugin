<?php

class __Inpost_Pay_System {

	public const TEST_PLUGIN_ACTIVE         = 1;
	public const TEST_PLUGIN_INSTALLED     = 2;
	public const TEST_PLUGIN_NOT_INSTALLED = 0;

	/**
	 * @var bool
	 */
	private $result = true;

	/**
	 * @var array
	 */
	private $plugin_config;

	/**
	 * @var string
	 */
	private $basename = '';

	/**
	 * Constructor.
	 *
	 * @param array $plugin_config Plugin configuration array.
	 */
	public function __construct( array $plugin_config ) {
		$this->plugin_config = $plugin_config;
	}

	/**
	 * Run system requirements check.
	 *
	 * @return bool
	 */
	public function evaluate_system(): bool {
		if ( $this->is_blocked() ) {
			return false;
		}

		$this->basename = basename( __DIR__ );

		$mofile = plugin_dir_path(__FILE__) . 'lang/' . $this->get('text_domain') . '-' . get_locale() . '.mo';
		load_textdomain($this->get('text_domain'), $mofile);

		$this->test_php();
		$this->test_required_plugins();
		$this->test_php_extensions();

		return $this->result;
	}

	/**
	 * Check PHP version.
	 */
	private function test_php(): void {
		if ( PHP_VERSION_ID < $this->get( 'min_php_int' ) ) {
			$this->fail_with_notice(
				sprintf(
					$this->t( 'PHP version is older than %s. This plugin requires a newer PHP version.' ),
					$this->get( 'min_php' )
				)
			);
		}
	}

	/**
	 * Check if required plugins are active.
	 */
	private function test_required_plugins(): void {
		$required_plugins = $this->get( 'required_plugins' );

		if ( empty( $required_plugins ) ) {
			return;
		}

		include_once ABSPATH . 'wp-admin/includes/plugin.php';

		foreach ( $required_plugins as $slug ) {
			$status = $this->test_plugin( $slug );

			if ( self::TEST_PLUGIN_INSTALLED === $status ) {
				$this->fail_with_notice(
					sprintf(
						$this->t( "Required plugin '%s' is installed but not activated." ),
						$slug
					)
				);
			}

			if ( self::TEST_PLUGIN_NOT_INSTALLED === $status ) {
				$this->fail_with_notice(
					sprintf(
						$this->t( "Required plugin '%s' is not installed." ),
						$slug
					)
				);
			}
		}
	}

	/**
	 * Check if required PHP extensions are loaded.
	 */
	private function test_php_extensions(): void {
		$required_extensions = $this->get( 'required_php_extensions' );

		if ( empty( $required_extensions ) ) {
			return;
		}

		$missing = [];

		foreach ( $required_extensions as $ext ) {
			if ( false === extension_loaded( $ext ) ) {
				$missing[] = $ext;
			}
		}

		if ( ! empty( $missing ) ) {
			$this->fail_with_notice(
				sprintf(
					$this->t( 'Required PHP extensions missing: %s' ),
					implode( ', ', $missing )
				)
			);
		}
	}

	/**
	 * Add admin notice and set failure flag.
	 *
	 * @param string $message Message to display.
	 */
	private function fail_with_notice( string $message ): void {
		$this->result = false;

		add_action(
			'admin_notices',
			function () use ( $message ) {
				printf(
					'<div class="notice notice-error error"><p><strong style="color:red;">%s: </strong>%s</p></div>',
					esc_html( $this->get( 'name' ) ),
					esc_html( $message )
				);
			}
		);
	}

	/**
	 * Check plugin activation status.
	 *
	 * @param string $slug Plugin slug.
	 *
	 * @return int Status constant.
	 */
	private function test_plugin( string $slug ): int {
		$path = $slug . '/' . $slug . '.php';

		if ( true === is_plugin_active( $path ) ) {
			return self::TEST_PLUGIN_ACTIVE;
		}

		if ( true === is_plugin_inactive( $path ) ) {
			return self::TEST_PLUGIN_INSTALLED;
		}

		return self::TEST_PLUGIN_NOT_INSTALLED;
	}

	/**
	 * Translate string (translation must already be loaded).
	 *
	 * @param string $text Text to translate.
	 *
	 * @return string
	 */
	private function t( string $text ): string {
		return __( $text, $this->get( 'text_domain' ) );
	}

	/**
	 * Get plugin config value.
	 *
	 * @param string $key Config key.
	 *
	 * @return mixed|null
	 */
	private function get( string $key ) {
		return $this->plugin_config[ $key ] ?? null;
	}

	/**
	 * Checks whether current request should be blocked.
	 *
	 * @return bool
	 */
	private function is_blocked(): bool {

		$is_ajax_breakdance = (
			defined( 'DOING_AJAX' )
			&& DOING_AJAX
			&& isset( $_REQUEST['action'] )
			&& strpos( (string) $_REQUEST['action'], 'breakdance' ) !== false
		);

		$is_rest_breakdance = (
			defined( 'REST_REQUEST' )
			&& REST_REQUEST
			&& isset( $_SERVER['REQUEST_URI'] )
			&& strpos( (string) $_SERVER['REQUEST_URI'], 'breakdance' ) !== false
		);

		return $is_ajax_breakdance || $is_rest_breakdance;
	}
}
