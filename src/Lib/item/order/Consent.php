<?php
/**
 * Order consent item.
 *
 * @package Inpost_Pay
 */

declare( strict_types=1 );

namespace Ilabs\Inpost_Pay\Lib\item\order;

use Ilabs\Inpost_Pay\Lib\helpers\JsonSerializationHelper;
use Ilabs\Inpost_Pay\Lib\Item;

/**
 * Represents order consent.
 */
class Consent extends Item implements \JsonSerializable {
	use JsonSerializationHelper;

	/**
	 * Consent ID.
	 *
	 * @var string
	 */
	protected string $consent_id;

	/**
	 * Consent link.
	 *
	 * @var string
	 */
	protected string $consent_link;

	/**
	 * Label link.
	 *
	 * @var string
	 */
	protected string $label_link;

	/**
	 * Additional consent links.
	 *
	 * @var array
	 */
	protected array $additional_consent_links;

	/**
	 * Consent description.
	 *
	 * @var string
	 */
	protected string $consent_description;

	/**
	 * Consent version.
	 *
	 * @var int
	 */
	protected int $consent_version;

	/**
	 * Requirement type.
	 *
	 * @var string
	 */
	protected string $requirement_type;

	/**
	 * Serialize item to array.
	 *
	 * @return array
	 */
	public function jsonSerialize(): array {
		return $this->auto_serialize();
	}

	/**
	 * Get consent ID.
	 *
	 * @return string
	 */
	public function get_consent_id(): string {
		return $this->consent_id;
	}

	/**
	 * Set consent ID.
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
	 * Get consent link.
	 *
	 * @return string
	 */
	public function get_consent_link(): string {
		return $this->consent_link;
	}

	/**
	 * Set consent link.
	 *
	 * @param string $consent_link Consent link.
	 *
	 * @return self
	 */
	public function set_consent_link( string $consent_link ): self {
		$this->consent_link = $consent_link;

		return $this;
	}

	/**
	 * Get label link.
	 *
	 * @return string
	 */
	public function get_label_link(): string {
		return $this->label_link;
	}

	/**
	 * Set label link.
	 *
	 * @param string $label_link Label link.
	 *
	 * @return self
	 */
	public function set_label_link( string $label_link ): self {
		$this->label_link = $label_link;

		return $this;
	}

	/**
	 * Get additional consent links.
	 *
	 * @return array
	 */
	public function get_additional_consent_links(): array {
		return $this->additional_consent_links;
	}

	/**
	 * Set additional consent links.
	 *
	 * @param array $additional_consent_links Additional consent links.
	 *
	 * @return self
	 */
	public function set_additional_consent_links( array $additional_consent_links ): self {
		$this->additional_consent_links = $additional_consent_links;

		return $this;
	}

	/**
	 * Get consent description.
	 *
	 * @return string
	 */
	public function get_consent_description(): string {
		return $this->consent_description;
	}

	/**
	 * Set consent description.
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
	 * Get consent version.
	 *
	 * @return int
	 */
	public function get_consent_version(): int {
		return $this->consent_version;
	}

	/**
	 * Set consent version.
	 *
	 * @param int $consent_version Consent version.
	 *
	 * @return self
	 */
	public function set_consent_version( int $consent_version ): self {
		$this->consent_version = $consent_version;

		return $this;
	}

	/**
	 * Get requirement type.
	 *
	 * @return string
	 */
	public function get_requirement_type(): string {
		return $this->requirement_type;
	}

	/**
	 * Set requirement type.
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
