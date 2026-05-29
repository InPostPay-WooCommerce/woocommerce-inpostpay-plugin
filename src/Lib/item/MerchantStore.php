<?php
/**
 * Merchant store item.
 *
 * @package Ilabs\Inpost_Pay\Lib\item
 */

declare( strict_types=1 );

namespace Ilabs\Inpost_Pay\Lib\item;

use Ilabs\Inpost_Pay\Lib\exception\CookieEmptyValueException;
use Ilabs\Inpost_Pay\Lib\helpers\JsonSerializationHelper;
use Ilabs\Inpost_Pay\Lib\Item;

/**
 * Represents the merchant store with URL and session cookies.
 */
class MerchantStore extends Item implements \JsonSerializable {
	use JsonSerializationHelper;

	protected string $url;

	protected array $cookies;

	/**
	 * Initializes merchant store with shop URL and WooCommerce session cookie.
	 *
	 * @throws CookieEmptyValueException When WooCommerce session cookie is empty.
	 */
	public function __construct() {
		$shop_url = get_permalink( wc_get_page_id( 'shop' ) );
		if ( empty( $shop_url ) ) {
			$shop_url = home_url();
		}

		$this->url = $shop_url;

		$cookie = ( new MerchantCookie() )->wp_woocommerce_session();

		$this->cookies = array( $cookie );
	}

	/**
	 * Serializes object to JSON-compatible array.
	 *
	 * @return array
	 */
	public function jsonSerialize(): array {
		return $this->auto_serialize();
	}

	/**
	 * Returns store URL.
	 *
	 * @return string
	 */
	public function get_url(): string {
		return $this->url;
	}

	/**
	 * Sets store URL.
	 *
	 * @param string $url Store URL.
	 *
	 * @return self
	 */
	public function set_url( string $url ): self {
		$this->url = $url;

		return $this;
	}

	/**
	 * Returns store cookies.
	 *
	 * @return array
	 */
	public function get_cookies(): array {
		return $this->cookies;
	}

	/**
	 * Sets store cookies.
	 *
	 * @param array $cookies Store cookies.
	 *
	 * @return self
	 */
	public function set_cookies( array $cookies ): self {
		$this->cookies = $cookies;

		return $this;
	}
}
