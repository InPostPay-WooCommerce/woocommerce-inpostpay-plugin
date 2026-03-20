<?php

namespace Ilabs\Inpost_Pay\Integration\Blocks;

use Ilabs\Inpost_Pay\Lib\InPostIzi;
use Ilabs\Inpost_Pay\hooks\front\FrontWidgetV2;

class BlocksManager {

	/**
	 * Initialize the class.
	 *
	 * This method registers actions for the following hooks:
	 *  - `init`: calls `register_blocks`
	 *  - `wp_enqueue_scripts`: calls `enqueue_frontend_assets`
	 *
	 * @return void
	 * @since 2.0.4
	 *
	 */
	public function __construct() {
		add_action( 'init', [ $this, 'register_gutenberg_blocks' ] );
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_frontend_assets' ] );
		add_action( 'enqueue_block_editor_assets', [ $this, 'enqueue_editor_assets' ] );
	}

	/**
	 * Register Gutenberg blocks.
	 *
	 * This method registers the InPost Pay Gutenberg block.
	 *
	 * @return void
	 * @since 2.0.4
	 *
	 */
	public function register_gutenberg_blocks(): void {
		register_block_type(
			plugin_dir_path( WOOCOMMERCE_INPOST_PAY_PLUGIN_FILE ) . 'src/Integration/Blocks/Gutenberg/Button',
			[
				'attributes'      => $this->get_block_attributes()
			]
		);
	}

	/**
	 * Get the block attributes.
	 *
	 * @return array The block attributes.
	 */
	private function get_block_attributes(): array {
		return [
			'bindingPlace' => [
				'type'    => 'string',
				'default' => 'PRODUCT_CARD',
				'enum'    => [
					'PRODUCT_CARD',
					'BASKET_SUMMARY',
					'ORDER_CREATE',
					'CHECKOUT_PAGE',
					'LOGIN_PAGE',
					'BASKET_POPUP',
					'THANK_YOU_PAGE',
					'MINICART_PAGE'
				]
			],
			'variant'      => [
				'type'    => 'string',
				'default' => 'primary',
				'enum'    => [ 'primary', 'dark' ]
			],
			'background'   => [
				'type'    => 'string',
				'default' => 'bright',
				'enum'    => [ 'bright', 'dark' ]
			],
			'frameStyle'        => [
				'type'    => 'string',
				'default' => 'none',
				'enum'    => [ 'none', 'round', 'rounded' ]
			],
			'size'        => [
				'type'    => 'string',
				'default' => 'size-sm',
				'enum'    => [ 'size-xs', 'size-sm', 'size-md', 'size-lg', 'size-xl' ]
			]
		];
	}

	public function enqueue_editor_assets(): void {

		wp_enqueue_script(
			'inpost-pay-gutenberg-button',
			INPOST_PAY_ASSETS_PUBLIC_PATH . 'js/blocks/gutenberg/inpost-pay-button/index.js',
			[
				'wp-blocks',
				'wp-block-editor',
				'wp-components',
				'wp-element',
				'wp-i18n'
			],
			'1.0.0',
			true
		);

		wp_localize_script('inpost-pay-gutenberg-button', 'inpostPayAdmin', [
			'jsUrl' => InPostIzi::getJsUrl(),
			'merchantId' => FrontWidgetV2::get_merchant_id(),
			'ajaxUrl' => admin_url('admin-ajax.php'),
			'nonce' => wp_create_nonce('inpost_pay_nonce'),
			'language' => \Ilabs\Inpost_Pay\Lib\helpers\LangHelper::getWidgetLangAttr()
		]);

		wp_enqueue_script(
			'inpost-pay-woocommerce-checkout-button',
			INPOST_PAY_ASSETS_PUBLIC_PATH . 'js/blocks/woocommerce/checkout-button/filter.js',
			[
				'wc-blocks-checkout'
			],
			'1.0.1',
			true
		);

		wp_set_script_translations(
			'inpost-pay-gutenberg-button',
			'inpost-pay',
			plugin_dir_path( WOOCOMMERCE_INPOST_PAY_PLUGIN_FILE ) . 'lang'
		);
	}


	public function enqueue_frontend_assets(): void {
		wp_localize_script('inpost-pay-blocks-frontend', 'inpostPayAdmin', [
			'jsUrl' => InPostIzi::getJsUrl(),
			'merchantId' => FrontWidgetV2::get_merchant_id(),
			'ajaxUrl' => admin_url('admin-ajax.php'),
			'nonce' => wp_create_nonce('inpost_pay_nonce')
		]);

		wp_set_script_translations(
			'inpost-pay-blocks-frontend',
			'inpost-pay',
			plugin_dir_path( WOOCOMMERCE_INPOST_PAY_PLUGIN_FILE ) . 'lang'
		);
	}
}
