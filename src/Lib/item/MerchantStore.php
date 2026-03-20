<?php

declare( strict_types=1 );

namespace Ilabs\Inpost_Pay\Lib\item;

use Ilabs\Inpost_Pay\Lib\exception\CookieEmptyValueException;
use Ilabs\Inpost_Pay\Lib\helpers\JsonSerializationHelper;
use Ilabs\Inpost_Pay\Lib\Item;

class MerchantStore extends Item implements \JsonSerializable {
	use JsonSerializationHelper;

	protected string $url;

	protected array $cookies;

	/**
	 * @throws CookieEmptyValueException
	 */
	public function __construct() {
		$shop_url = get_permalink( wc_get_page_id( 'shop' ) );
		if ( empty( $shop_url ) ) {
			$shop_url = home_url();
		}

		$this->url = $shop_url;

		$cookie = ( new MerchantCookie() )->wp_woocommerce_session();

		$this->cookies = [ $cookie ];

	}

	public function jsonSerialize(): array {
		return $this->autoSerialize();
	}

	public function get_url(): string {
		return $this->url;
	}

	public function set_url( string $url ): self {
		$this->url = $url;

		return $this;
	}

	public function get_cookies(): array {
		return $this->cookies;
	}

	public function set_cookies( array $cookies ): self {
		$this->cookies = $cookies;

		return $this;
	}
}
