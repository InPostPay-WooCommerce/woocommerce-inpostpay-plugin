<?php
/**
 * Front display widget.
 *
 * @package Ilabs\Inpost_Pay
 */

namespace Ilabs\Inpost_Pay\hooks\front;

use Ilabs\Inpost_Pay\Integration\Basket\Availability\UnavailabilityService;
use Ilabs\Inpost_Pay\Integration\Currency\CurrencyHelper;
use Ilabs\Inpost_Pay\Lib\helpers\WooProductHelper;
use Ilabs\Inpost_Pay\Lib\InPostIzi;
use Ilabs\Inpost_Pay\Lib\view\DisplayPlaceHolder;
use Ilabs\Inpost_Pay\Logger;
use function Ilabs\Inpost_Pay\inpost_pay_container;

/**
 * Frontend widget display handler.
 *
 * Responsible for registering hooks and rendering InPost Pay widget placeholders
 * and widget instances on WooCommerce frontend pages.
 *
 * @package Ilabs\Inpost_Pay
 */
class FrontDisplayWidget extends FrontBase {

	/**
	 * Cache for unavailability service (singleton)
	 *
	 * @var UnavailabilityService|null
	 */
	private static ?UnavailabilityService $unavailability_service = null;
	/**
	 * Cache for cart availability check result
	 *
	 * @var bool|null
	 */
	private static ?bool $can_display_in_cart = null;

	/**
	 * Attach frontend hooks for displaying the widget and placeholders.
	 *
	 * @return void
	 */
	public function attach_hook(): void {
		if ( esc_attr( get_option( 'izi_show_order' ) ) ) {
			add_action( esc_attr( get_option( 'izi_place_order' ) ), array( $this, 'displayOrderPlaceholder' ) );
		}
		if ( esc_attr( get_option( 'izi_show_checkout' ) ) ) {
			add_action( esc_attr( get_option( 'izi_place_checkout' ) ), array( $this, 'displayCheckoutPlaceholder' ) );
		}
		if ( esc_attr( get_option( 'izi_show_login_page' ) ) ) {
			add_action(
				esc_attr( get_option( 'izi_place_login_page' ) ),
				array(
					$this,
					'displayLoginPagePlaceholder',
				),
				20
			);
		}
		if ( esc_attr( get_option( 'izi_show_minicart' ) ) ) {
			add_action( esc_attr( get_option( 'izi_place_minicart' ) ), array( $this, 'displayMinicartPlaceholder' ) );
		}
		if ( esc_attr( get_option( 'izi_show_basket' ) ) ) {
			add_action( esc_attr( get_option( 'izi_place_basket' ) ), array( $this, 'displayCartPlaceholder' ) );
		}

		if ( esc_attr( get_option( 'izi_show_list' ) ) ) {
			add_action(
				'woocommerce_after_shop_loop_item',
				function () {
					global $post;

					if ( ! $this->iziAvailableForProduct( $post->ID ) ) {
						return;
					}

					$this->display(
						$post->ID,
						'',
						esc_attr( get_option( 'izi_background', 'dark' ) ) === 'dark',
						esc_attr( get_option( 'izi_variant', 'primary' ) ) === 'primary',
						false,
						esc_attr( get_option( 'izi_align_list' ) ),
						InPostIzi::BINDING_PLACE_PRODUCT_CARD,
						esc_attr( get_option( 'izi_frame_style' ) ),
						false,
					);
				},
				10
			);
		}

		if ( esc_attr( get_option( 'izi_show_details' ) ) ) {
			add_action(
				esc_attr( get_option( 'izi_place_details', 'woocommerce_after_add_to_cart_button' ) ),
				array(
					$this,
					'displayProductPlaceholder',
				)
			);
		}
	}

	/**
	 * Check if widget is available for a specific product.
	 *
	 * @param int|string $product_id Product ID.
	 *
	 * @return bool True when widget can be displayed.
	 */
	protected function iziAvailableForProduct( $product_id ): bool {
		$product = wc_get_product( $product_id );

		if ( ! $product ) {
			Logger::log( '[DISPLAY_WIDGET] No product, product_id ' . (string) $product_id );

			return false;
		}

		$unavailability_service = self::get_unavailability_service();
		if ( $unavailability_service->is_unavailable_to_display_on_product_page( $product_id ) ) {
			Logger::log( '[DISPLAY_WIDGET] Unavailable product, product_id ' . (string) $product_id );

			return false;
		}

		return true;
	}

	/**
	 * Get unavailability service (singleton)
	 *
	 * @return UnavailabilityService
	 */
	private static function get_unavailability_service(): UnavailabilityService {
		if ( null === self::$unavailability_service ) {
			self::$unavailability_service = inpost_pay_container()->get( UnavailabilityService::SERVICE_KEY );
		}

		return self::$unavailability_service;
	}

	/**
	 * Render the widget.
	 *
	 * @param int|string|null $id         Product ID or null.
	 * @param string          $styles     Inline styles for wrapper.
	 * @param bool            $dark       Whether dark background is enabled.
	 * @param bool            $yellow     Whether primary (yellow) variant is enabled.
	 * @param bool            $cart       Whether widget is rendered in cart context.
	 * @param string          $align      Widget alignment.
	 * @param string          $place      Binding place.
	 * @param string          $frame_style Frame style.
	 * @param string|bool     $size       Widget size.
	 *
	 * @return void
	 */
	public function display(
		$id,
		$styles,
		$dark,
		$yellow,
		$cart,
		$align,
		$place,
		$frame_style,
		$size
	): void {
		if ( ! is_numeric( $id ) ) {
			$id = null;
		} elseif ( ! $this->iziAvailableForProduct( $id ) ) {
			return;
		}

		if ( ! CurrencyHelper::isCurrencyAllowed() ) {
			return;
		}

		$styles .= 'clear:both;';

		if ( ! empty( $styles ) ) {
			echo '<div style="' . esc_attr( $styles ) . '">';
		}

		InPostIzi::render(
			$id,
			true,
			$dark,
			$yellow,
			$place,
			$frame_style,
			$size,
		);
		if ( ! empty( $styles ) ) {
			echo '</div>';
		}
	}

	/**
	 * Display product placeholder.
	 *
	 * @param int|string|null $id     Unused.
	 * @param string          $styles Unused.
	 *
	 * @return void
	 */
	public function displayProductPlaceholder( $id = null, $styles = '' ): void {
		DisplayPlaceHolder::displayProductPlaceholder();
	}

	/**
	 * Display cart placeholder.
	 *
	 * @param int|string|null $id     Unused.
	 * @param string          $styles Unused.
	 *
	 * @return void
	 */
	public function displayCartPlaceholder( $id = null, $styles = '' ): void {
		DisplayPlaceHolder::displayCartPlaceholder();
	}

	/**
	 * Display order placeholder.
	 *
	 * @param int|string|null $id     Unused.
	 * @param string          $styles Unused.
	 *
	 * @return void
	 */
	public function displayOrderPlaceholder( $id = null, $styles = '' ): void {
		DisplayPlaceHolder::displayOrderPlaceHolder();
	}

	/**
	 * Display checkout placeholder.
	 *
	 * @param int|string|null $id     Unused.
	 * @param string          $styles Unused.
	 *
	 * @return void
	 */
	public function displayCheckoutPlaceholder( $id = null, $styles = '' ): void {
		DisplayPlaceHolder::displayCheckoutPlaceholder();
	}

	/**
	 * Display login page placeholder.
	 *
	 * @param int|string|null $id     Unused.
	 * @param string          $styles Unused.
	 *
	 * @return void
	 */
	public function displayLoginPagePlaceholder( $id = null, $styles = '' ): void {
		DisplayPlaceHolder::displayLoginPagePlaceholder();
	}

	/**
	 * Display mini cart placeholder.
	 *
	 * @param int|string|null $id     Unused.
	 * @param string          $styles Unused.
	 *
	 * @return void
	 */
	public function displayMinicartPlaceholder( $id = null, $styles = '' ): void {
		DisplayPlaceHolder::displayMinicartPlaceholder();
	}

	/**
	 * Display widget on product page.
	 *
	 * @param int|string|null $id     Product ID.
	 * @param string          $styles Inline styles for wrapper.
	 *
	 * @return void
	 */
	public function displayProduct( $id = null, $styles = '' ): void {
		if ( empty( $id ) && is_product() ) {
			global $post;
			$id = $post->ID;
		}

		$button_details_margin = get_option( 'izi_button_details_margin' );
		if ( is_array( $button_details_margin ) ) {
			foreach ( $button_details_margin as $margin => $value ) {
				if ( (int) $value > 0 ) {
					$styles .= ' margin-' . $margin . ': ' . (int) $value . 'px !important;';
				}
			}
		}

		$button_details_padding = get_option( 'izi_button_details_padding' );
		if ( is_array( $button_details_padding ) ) {
			foreach ( $button_details_padding as $padding => $value ) {
				if ( (int) $value > 0 ) {
					$styles .= ' padding-' . $padding . ': ' . (int) $value . 'px !important;';
				}
			}
		}

		$this->display(
			$id,
			$styles,
			esc_attr( get_option( 'izi_background', 'dark' ) ) === 'dark',
			esc_attr( get_option( 'izi_variant', 'primary' ) ) === 'primary',
			true,
			esc_attr( get_option( 'izi_align_details' ) ),
			InPostIzi::BINDING_PLACE_PRODUCT_CARD,
			esc_attr( get_option( 'izi_frame_style' ) ),
			false,
		);
	}

	/**
	 * Display widget in cart context.
	 *
	 * @param int|string|null $id     Unused.
	 * @param string          $styles Inline styles for wrapper.
	 *
	 * @return void
	 */
	public function displayCart( $id = null, $styles = '' ): void {
		if ( ! $this->canDisplayInCart() ) {
			return;
		}
		$button_cart_margin = get_option( 'izi_button_cart_margin' );
		if ( is_array( $button_cart_margin ) ) {
			foreach ( $button_cart_margin as $margin => $value ) {
				if ( (int) $value > 0 ) {
					$styles .= ' margin-' . $margin . ': ' . (int) $value . 'px !important;';
				}
			}
		}

		$button_cart_padding = get_option( 'izi_button_cart_padding' );
		if ( is_array( $button_cart_padding ) ) {
			foreach ( $button_cart_padding as $padding => $value ) {
				if ( (int) $value > 0 ) {
					$styles .= ' padding-' . $padding . ': ' . (int) $value . 'px !important;';
				}
			}
		}

		$this->display(
			null,
			$styles,
			esc_attr( get_option( 'izi_background', 'dark' ) ) === 'dark',
			esc_attr( get_option( 'izi_variant', 'primary' ) ) === 'primary',
			true,
			esc_attr( get_option( 'izi_align_basket' ) ),
			InPostIzi::BINDING_PLACE_BASKET_SUMMARY,
			esc_attr( get_option( 'izi_frame_style' ) ),
			false,
		);
	}

	/**
	 * Determine whether the widget can be displayed in cart.
	 *
	 * @return bool True when widget can be displayed.
	 */
	protected function canDisplayInCart(): bool {
		// Don't initialize cart/session - just check if it exists.
		if ( ! WC()->cart ) {
			self::$can_display_in_cart = false;
			return false;
		}

		$cart          = WC()->cart;
		$cart_contents = $cart->get_cart_contents();

		if ( empty( $cart_contents ) ) {
			self::$can_display_in_cart = true;

			return true;
		}

		$product_ids = array_column( $cart_contents, 'product_id' );
		/**
		 * Get from container DI.
		 *
		 * @var WooProductHelper $product_helper
		 */
		$product_helper = inpost_pay_container()->get( WooProductHelper::SERVICE_KEY );
		$products       = $product_helper->load_products_safe( $product_ids );

		foreach ( $products as $product ) {
			if ( ! $product ) {
				continue;
			}

			if ( $product->is_virtual() ) {
				self::$can_display_in_cart = false;

				return false;
			}
		}

		if ( ! self::get_unavailability_service()->is_available_to_display_on_other_pages() ) {
			self::$can_display_in_cart = false;

			return false;
		}

		self::$can_display_in_cart = true;

		return true;
	}

	/**
	 * Display widget on order page.
	 *
	 * @param int|string|null $id     Unused.
	 * @param string          $styles Inline styles for wrapper.
	 *
	 * @return void
	 */
	public function displayOrder( $id = null, $styles = '' ): void {
		$this->display(
			null,
			$styles,
			esc_attr( get_option( 'izi_background', 'dark' ) ) === 'dark',
			esc_attr( get_option( 'izi_variant', 'primary' ) ) === 'primary',
			false,
			esc_attr( get_option( 'izi_align_order' ) ),
			InPostIzi::BINDING_PLACE_ORDER_CREATE,
			esc_attr( get_option( 'izi_frame_style' ) ),
			false,
		);
	}

	/**
	 * Display widget on checkout page.
	 *
	 * @param int|string|null $id     Unused.
	 * @param string          $styles Inline styles for wrapper.
	 *
	 * @return void
	 */
	public function displayCheckout( $id = null, $styles = '' ): void {
		$this->display(
			null,
			$styles,
			esc_attr( get_option( 'izi_background', 'dark' ) ) === 'dark',
			esc_attr( get_option( 'izi_variant', 'primary' ) ) === 'primary',
			false,
			esc_attr( get_option( 'izi_align_checkout' ) ),
			InPostIzi::BINDING_PLACE_CHECKOUT_PAGE,
			esc_attr( get_option( 'izi_frame_style' ) ),
			false,
		);
	}

	/**
	 * Display widget on login page.
	 *
	 * @param int|string|null $id     Unused.
	 * @param string          $styles Inline styles for wrapper.
	 *
	 * @return void
	 */
	public function displayLoginPage( $id = null, $styles = '' ): void {
		$this->display(
			null,
			$styles,
			esc_attr( get_option( 'izi_background', 'dark' ) ) === 'dark',
			esc_attr( get_option( 'izi_variant', 'primary' ) ) === 'primary',
			false,
			esc_attr( get_option( 'izi_align_login_page' ) ),
			InPostIzi::BINDING_PLACE_LOGIN_PAGE,
			esc_attr( get_option( 'izi_frame_style' ) ),
			false,
		);
	}

	/**
	 * Display widget on mini cart.
	 *
	 * @param int|string|null $id     Unused.
	 * @param string          $styles Inline styles for wrapper.
	 *
	 * @return void
	 */
	public function displayMinicart( $id = null, $styles = '' ): void {
		$this->display(
			null,
			$styles,
			esc_attr( get_option( 'izi_background', 'dark' ) ) === 'dark',
			esc_attr( get_option( 'izi_variant', 'primary' ) ) === 'primary',
			false,
			esc_attr( get_option( 'izi_align_minicart' ) ),
			InPostIzi::BINDING_PLACE_MINICART_PAGE,
			esc_attr( get_option( 'izi_frame_style' ) ),
			false,
		);
	}
}
