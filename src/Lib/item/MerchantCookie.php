<?php

declare( strict_types=1 );

namespace Ilabs\Inpost_Pay\Lib\item;

use Ilabs\Inpost_Pay\Lib\exception\CookieEmptyValueException;
use Ilabs\Inpost_Pay\Lib\helpers\CookieHelper;
use Ilabs\Inpost_Pay\Lib\helpers\JsonSerializationHelper;
use Ilabs\Inpost_Pay\Lib\helpers\Woo_Commerce_Session_Helper;
use Ilabs\Inpost_Pay\Lib\Item;

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

	// protected int $max_age = 0;

	protected string $expires = '';


	/**
	 * @throws CookieEmptyValueException
	 */
	public function wp_woocommerce_session(): self {
		foreach ( $_COOKIE as $key => $value ) {
			if ( false !== strpos( $key, 'wp_woocommerce_session_' ) ) {
				$cookie    = CookieHelper::get_from_header( $key );
				$this->key = $key;
			}
		}

		if ( isset( $cookie ) ) {
			$this->value  = CookieHelper::get( $this->key );
			$this->path   = $cookie['path'] ?? '/';
			$this->domain = 'https://' . wp_parse_url( home_url(), PHP_URL_HOST );
			// $this->secure    = (bool) $cookie['secure'];
			// $this->http_only = (bool) $cookie['http_only'];
		} else {
			$cookie       = CookieHelper::get( $this->key );
			$this->value  = $cookie;
			$this->domain = 'https://' . wp_parse_url( home_url(), PHP_URL_HOST );
		}

		$this->expires = Woo_Commerce_Session_Helper::get_session_expiration_time();

		if ( empty( $this->value ) ) {
			throw new CookieEmptyValueException();
		}

		return $this;
	}

	public function jsonSerialize(): array {
		return $this->autoSerialize();
	}

	public function get_key(): string {
		return $this->key;
	}

	public function set_key( string $key ): self {
		$this->key = $key;

		return $this;
	}

	public function get_value(): string {
		return $this->value;
	}

	public function set_value( string $value ): self {
		$this->value = $value;

		return $this;
	}

	public function get_path(): string {
		return $this->path;
	}

	public function set_path( string $path ): self {
		$this->path = $path;

		return $this;
	}

	public function get_domain(): string {
		return $this->domain;
	}

	public function set_domain( string $domain ): self {
		$this->domain = $domain;

		return $this;
	}

	public function get_secure(): ?bool {
		return $this->secure;
	}

	public function set_secure( ?bool $secure ): self {
		$this->secure = $secure;

		return $this;
	}

	public function get_http_only(): ?bool {
		return $this->http_only;
	}

	public function set_http_only( ?bool $http_only ): self {
		$this->http_only = $http_only;

		return $this;
	}

	public function get_same_site(): ?string {
		return $this->same_site;
	}

	public function set_same_site( ?string $same_site ): self {
		$this->same_site = $same_site;

		return $this;
	}

	public function get_priority(): ?string {
		return $this->priority;
	}

	public function set_priority( ?string $priority ): self {
		$this->priority = $priority;

		return $this;
	}

	public function get_expires(): string {
		return $this->expires;
	}

	public function set_expires( string $expires ): self {
		$this->expires = $expires;

		return $this;
	}
}
