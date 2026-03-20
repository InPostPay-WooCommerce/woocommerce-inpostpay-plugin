<?php

namespace Ilabs\Inpost_Pay\Lib\item;

use Ilabs\Inpost_Pay\Lib\helpers\JsonSerializationHelper;

class HotProductAvailability implements \JsonSerializable {
	use JsonSerializationHelper;

	protected string $start_date;
	protected string $end_date;

	public function __construct( string $start_date, string $end_date ) {
		$this->start_date = $start_date;
		$this->end_date   = $end_date;
	}

	public function jsonSerialize() {
		return $this->autoSerialize();
	}

	public function get_end_date(): string {
		return $this->end_date;
	}

	public function set_end_date( string $end_date ): self {
		$this->end_date = $end_date;

		return $this;
	}

	public function get_start_date(): string {
		return $this->start_date;
	}

	public function set_start_date( string $start_date ): self {
		$this->start_date = $start_date;

		return $this;
	}
}
