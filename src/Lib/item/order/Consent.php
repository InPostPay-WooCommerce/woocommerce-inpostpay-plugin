<?php

declare( strict_types=1 );

namespace Ilabs\Inpost_Pay\Lib\item\order;

use Ilabs\Inpost_Pay\Lib\helpers\JsonSerializationHelper;
use Ilabs\Inpost_Pay\Lib\Item;

class Consent extends Item implements \JsonSerializable {
	use JsonSerializationHelper;

	protected string $consent_id;
	protected string $consent_link;
	protected string $label_link;
	protected array $additional_consent_links;
	protected string $consent_description;
	protected int $consent_version;
	protected string $requirement_type;

	public function jsonSerialize(): array {
		return $this->autoSerialize();
	}

	public function get_consent_id(): string {
		return $this->consent_id;
	}

	public function set_consent_id( string $consent_id ): self {
		$this->consent_id = $consent_id;

		return $this;
	}

	public function get_consent_link(): string {
		return $this->consent_link;
	}

	public function set_consent_link( string $consent_link ): self {
		$this->consent_link = $consent_link;

		return $this;
	}

	public function get_label_link(): string {
		return $this->label_link;
	}

	public function set_label_link( string $label_link ): self {
		$this->label_link = $label_link;

		return $this;
	}

	public function get_additional_consent_links(): array {
		return $this->additional_consent_links;
	}

	public function set_additional_consent_links( array $additional_consent_links ): self {
		$this->additional_consent_links = $additional_consent_links;

		return $this;
	}

	public function get_consent_description(): string {
		return $this->consent_description;
	}

	public function set_consent_description( string $consent_description ): self {
		$this->consent_description = $consent_description;

		return $this;
	}

	public function get_consent_version(): int {
		return $this->consent_version;
	}

	public function set_consent_version( int $consent_version ): self {
		$this->consent_version = $consent_version;

		return $this;
	}

	public function get_requirement_type(): string {
		return $this->requirement_type;
	}

	public function set_requirement_type( string $requirement_type ): self {
		$this->requirement_type = $requirement_type;

		return $this;
	}
}
