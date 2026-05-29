<?php
/**
 * Consent item.
 *
 * @package Ilabs\Inpost_Pay\Lib\item
 */

declare( strict_types=1 );

namespace Ilabs\Inpost_Pay\Lib\item;

use Ilabs\Inpost_Pay\Lib\Item;
use Ilabs\Inpost_Pay\Lib\helpers\JsonSerializationHelper;
use JsonSerializable;

/**
 * Represents a consent required in the basket.
 */
class Consent extends Item implements JsonSerializable {
	use JsonSerializationHelper;

	protected string $consent_id;
	protected string $consent_link;
	protected string $consent_description;
	protected string $consent_version;
	protected string $requirement_type;

	/**
	 * Serializes object to JSON-compatible array.
	 *
	 * @return array
	 */
	public function jsonSerialize(): array {
		return $this->auto_serialize();
	}

	/**
	 * Returns consent ID.
	 *
	 * @return string
	 */
	public function get_consent_id(): string {
		return $this->consent_id;
	}

	/**
	 * Sets consent ID.
	 *
	 * @param string $consent_id Consent ID.
	 *
	 * @return self
	 */
	public function set_consent_id( string $consent_id ): self {
		$this->consent_id = $consent_id;

		return $this;
	}

	/**
	 * Returns consent link.
	 *
	 * @return string
	 */
	public function get_consent_link(): string {
		return $this->consent_link;
	}

	/**
	 * Sets consent link.
	 *
	 * @param string $consent_link Consent link URL.
	 *
	 * @return self
	 */
	public function set_consent_link( string $consent_link ): self {
		$this->consent_link = $consent_link;

		return $this;
	}

	/**
	 * Returns consent description.
	 *
	 * @return string
	 */
	public function get_consent_description(): string {
		return $this->consent_description;
	}

	/**
	 * Sets consent description.
	 *
	 * @param string $consent_description Consent description.
	 *
	 * @return self
	 */
	public function set_consent_description( string $consent_description ): self {
		$this->consent_description = $consent_description;

		return $this;
	}

	/**
	 * Returns consent version.
	 *
	 * @return string
	 */
	public function get_consent_version(): string {
		return $this->consent_version;
	}

	/**
	 * Sets consent version.
	 *
	 * @param string $consent_version Consent version.
	 *
	 * @return self
	 */
	public function set_consent_version( string $consent_version ): self {
		$this->consent_version = $consent_version;

		return $this;
	}

	/**
	 * Returns requirement type.
	 *
	 * @return string
	 */
	public function get_requirement_type(): string {
		return $this->requirement_type;
	}

	/**
	 * Sets requirement type.
	 *
	 * @param string $requirement_type Requirement type.
	 *
	 * @return self
	 */
	public function set_requirement_type( string $requirement_type ): self {
		$this->requirement_type = $requirement_type;

		return $this;
	}
}
