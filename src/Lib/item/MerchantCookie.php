<?php
/**
 * Merchant cookie item.
 *
 * @package Ilabs\Inpost_Pay\Lib\item
 */

declare( strict_types=1 );

namespace Ilabs\Inpost_Pay\Lib\item;

use Ilabs\Inpost_Pay\Lib\exception\CookieEmptyValueException;
use Ilabs\Inpost_Pay\Lib\helpers\CookieHelper;
use Ilabs\Inpost_Pay\Lib\helpers\JsonSerializationHelper;
use Ilabs\Inpost_Pay\Lib\helpers\Woo_Commerce_Session_Helper;
use Ilabs\Inpost_Pay\Lib\Item;

/**
 * Represents a browser cookie sent to the InPost Pay API.
 */
class MerchantCookie extends Item implements \JsonSerializable {
	use JsonSerializationHelper;

	protected string $key = 'wp_woocommerce_session_';

	protected string $value;

	protected string $path = '/';

	protected string $domain;

	protected ?bool $secure = true;

	protected ?bool $http_only = false;

	protected ?string $same_site = 'NONE';

	protected ?string $priority = 'MEDIUM';

	protected string $expires = '';

	/**
	 * Initializes cookie from the WooCommerce session.
	 *
	 * @throws CookieEmptyValueException When session cookie value is empty.
	 *
	 * @return self
	 */
	public function wp_woocommerce_session(): self {
		foreach ( $_COOKIE as $key => $value ) {
			if ( false !== strpos( $key, 'wp_woocommerce_session_' ) ) {
				$cookie    = CookieHelper::get_from_header( $key );
				$this->key = $key;
			}
		}

		if ( isset( $cookie ) ) {
			$this->value = CookieHelper::get( $this->key );
			$this->path  = $cookie['path'] ?? '/';
		} else {
			$cookie      = CookieHelper::get( $this->key );
			$this->value = $cookie;
		}
		$this->domain = 'https://' . wp_parse_url( home_url(), PHP_URL_HOST );

		$this->expires = (string) Woo_Commerce_Session_Helper::get_session_expiration_time();

		if ( empty( $this->value ) ) {
			throw new CookieEmptyValueException();
		}

		return $this;
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
	 * Returns cookie key.
	 *
	 * @return string
	 */
	public function get_key(): string {
		return $this->key;
	}

	/**
	 * Sets cookie key.
	 *
	 * @param string $key Cookie key.
	 *
	 * @return self
	 */
	public function set_key( string $key ): self {
		$this->key = $key;

		return $this;
	}

	/**
	 * Returns cookie value.
	 *
	 * @return string
	 */
	public function get_value(): string {
		return $this->value;
	}

	/**
	 * Sets cookie value.
	 *
	 * @param string $value Cookie value.
	 *
	 * @return self
	 */
	public function set_value( string $value ): self {
		$this->value = $value;

		return $this;
	}

	/**
	 * Returns cookie path.
	 *
	 * @return string
	 */
	public function get_path(): string {
		return $this->path;
	}

	/**
	 * Sets cookie path.
	 *
	 * @param string $path Cookie path.
	 *
	 * @return self
	 */
	public function set_path( string $path ): self {
		$this->path = $path;

		return $this;
	}

	/**
	 * Returns cookie domain.
	 *
	 * @return string
	 */
	public function get_domain(): string {
		return $this->domain;
	}

	/**
	 * Sets cookie domain.
	 *
	 * @param string $domain Cookie domain.
	 *
	 * @return self
	 */
	public function set_domain( string $domain ): self {
		$this->domain = $domain;

		return $this;
	}

	/**
	 * Returns whether cookie is secure.
	 *
	 * @return bool|null
	 */
	public function get_secure(): ?bool {
		return $this->secure;
	}

	/**
	 * Sets whether cookie is secure.
	 *
	 * @param bool|null $secure Secure flag.
	 *
	 * @return self
	 */
	public function set_secure( ?bool $secure ): self {
		$this->secure = $secure;

		return $this;
	}

	/**
	 * Returns whether cookie is HTTP-only.
	 *
	 * @return bool|null
	 */
	public function get_http_only(): ?bool {
		return $this->http_only;
	}

	/**
	 * Sets whether cookie is HTTP-only.
	 *
	 * @param bool|null $http_only HTTP-only flag.
	 *
	 * @return self
	 */
	public function set_http_only( ?bool $http_only ): self {
		$this->http_only = $http_only;

		return $this;
	}

	/**
	 * Returns SameSite attribute value.
	 *
	 * @return string|null
	 */
	public function get_same_site(): ?string {
		return $this->same_site;
	}

	/**
	 * Sets SameSite attribute value.
	 *
	 * @param string|null $same_site SameSite attribute value.
	 *
	 * @return self
	 */
	public function set_same_site( ?string $same_site ): self {
		$this->same_site = $same_site;

		return $this;
	}

	/**
	 * Returns cookie priority.
	 *
	 * @return string|null
	 */
	public function get_priority(): ?string {
		return $this->priority;
	}

	/**
	 * Sets cookie priority.
	 *
	 * @param string|null $priority Cookie priority.
	 *
	 * @return self
	 */
	public function set_priority( ?string $priority ): self {
		$this->priority = $priority;

		return $this;
	}

	/**
	 * Returns cookie expiration time.
	 *
	 * @return string
	 */
	public function get_expires(): string {
		return $this->expires;
	}

	/**
	 * Sets cookie expiration time.
	 *
	 * @param string $expires Cookie expiration time.
	 *
	 * @return self
	 */
	public function set_expires( string $expires ): self {
		$this->expires = $expires;

		return $this;
	}
}
