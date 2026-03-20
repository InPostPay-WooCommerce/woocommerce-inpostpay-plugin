<?php
/**
 * Virtual Page Handler
 *
 * Manages the creation and routing of virtual pages for InPost Pay
 *
 * @package Ilabs\Inpost_Pay\Lib\VirtualPage
 */

namespace Ilabs\Inpost_Pay\Lib\VirtualPage;

use Ilabs\Inpost_Pay\Integration\Language\LanguageHelper;

/**
 * Class VirtualPage
 *
 * Handles virtual page initialization, routing, and shortcode registration
 * for InPost Pay thank you pages.
 */
class VirtualPage {

	/**
	 * Initialize virtual page system.
	 *
	 * @return void
	 */
	public function init(): void {
		$controller = new Controller( new TemplateLoaderInpostPay() );
		add_action( 'init', array( $controller, 'init' ) );

		add_filter( 'do_parse_request', array( $controller, 'dispatch' ), PHP_INT_MAX, 2 );

		add_action(
			'loop_end',
			static function ( \WP_Query $query ) {
				if ( ! empty( $query->virtual_page ) ) {
					$query->virtual_page = null;
				}
			}
		);

		add_filter(
			'the_permalink',
			static function ( $plink ) {
				global $post, $wp_query;
				if (
					isset( $wp_query->virtual_page, $post->is_virtual ) &&
					true === $wp_query->is_page &&
					$wp_query->virtual_page instanceof Page &&
					true === $post->is_virtual
				) {
					$plink = home_url( $wp_query->virtual_page->getUrl() );
				}

				return $plink;
			}
		);

		add_filter(
			'body_class',
			static function ( $classes ) {
				global $wp_query;
				if (
					isset( $wp_query->virtual_page ) &&
					$wp_query->virtual_page instanceof Page &&
					'inpost-pay/thank-you-page' === $wp_query->virtual_page->getUrl()
				) {
					$classes[] = 'inpost-thank-you-page';
				}

				return $classes;
			}
		);

		add_action(
			'inpost_pay_virtual_pages',
			function ( $controller ) {
				$this->maybe_add_virtual_typ( $controller );
			}
		);

		$this->register_shortcode();
	}

	/**
	 * Conditionally add virtual thank you page.
	 *
	 * Registers virtual thank you page routes only if no custom WordPress page
	 * has been selected in plugin settings, or if the selected page is not published.
	 * Handles language-specific slugs when multilingual system is active.
	 *
	 * @param ControllerInterface $controller Virtual page controller instance.
	 *
	 * @return void
	 */
	private function maybe_add_virtual_typ( ControllerInterface $controller ): void {
		if ( $this->is_custom_page_available() ) {
			return;
		}

		$slugs = array();
		if ( LanguageHelper::isLanguageSystemActive() ) {
			$slugs = LanguageHelper::getAvailableSlugs();
		}

		$this->add_typ( $controller );
		foreach ( $slugs as $lang ) {
			$this->add_typ( $controller, $lang );
		}
	}

	/**
	 * Check if custom thank you page is available.
	 *
	 * @return bool True if custom page exists and is published, false otherwise.
	 */
	private function is_custom_page_available(): bool {
		$custom_page_id = (int) get_option( 'izi_thank_you_page_id', 0 );

		if ( ! $custom_page_id ) {
			return false;
		}

		return 'publish' === get_post_status( $custom_page_id );
	}

	/**
	 * Add thank you page to virtual page controller.
	 *
	 * @param ControllerInterface $controller Virtual page controller instance.
	 * @param string|null         $lang      Optional language code for URL prefix.
	 *
	 * @return void
	 */
	private function add_typ( ControllerInterface $controller, ?string $lang = null ): void {
		$path = $this->get_with_locale_if_exists( 'inpost-pay/thank-you-page', $lang );

		$controller->addPage( new Page( $path ) )
		           ->setTitle( __( 'Thank you page', 'inpost-pay' ) )
		           ->setContent( '<inpost-thank-you></inpost-thank-you>' )
		           ->setTemplate( 'thank-you-page.php' );
	}

	/**
	 * Build path with optional language prefix.
	 *
	 * @param string      $path Base path without language prefix.
	 * @param string|null $lang Optional language code to prepend.
	 *
	 * @return string Constructed path with or without language prefix.
	 */
	private function get_with_locale_if_exists( string $path, ?string $lang = null ): string {
		return $lang ? "$lang/$path" : $path;
	}

	/**
	 * Register InPost thank you shortcode.
	 *
	 * Registers [inpost_thank_you] shortcode that renders the InPost Pay
	 * thank you web component.
	 *
	 * @return void
	 */
	private function register_shortcode(): void {
		add_shortcode(
			'inpost_thank_you',
			static function () {
				return '<inpost-thank-you></inpost-thank-you>';
			}
		);
	}

	/**
	 * Get thank you page URL.
	 *
	 * Returns the appropriate thank you page URL based on plugin settings.
	 * Prioritizes custom WordPress page if selected and published, otherwise
	 * falls back to virtual page URL.
	 *
	 * @return string Full URL to thank you page.
	 */
	public static function get_thank_you_url(): string {
		$custom_page_id = (int) get_option( 'izi_thank_you_page_id', 0 );

		if ( $custom_page_id && 'publish' === get_post_status( $custom_page_id ) ) {
			return get_permalink( $custom_page_id );
		}

		return home_url( 'inpost-pay/thank-you-page' );
	}
}
