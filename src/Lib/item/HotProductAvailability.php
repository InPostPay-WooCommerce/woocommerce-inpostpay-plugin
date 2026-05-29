<?php
/**
 * Hot product availability item.
 *
 * @package Ilabs\Inpost_Pay\Lib\item
 */

namespace Ilabs\Inpost_Pay\Lib\item;

use Ilabs\Inpost_Pay\Lib\helpers\JsonSerializationHelper;

/**
 * Represents the availability window for a hot product.
 */
class HotProductAvailability implements \JsonSerializable {
	use JsonSerializationHelper;

	protected string $start_date;
	protected string $end_date;

	/**
	 * Initializes availability with start and end dates.
	 *
	 * @param string $start_date Start date.
	 * @param string $end_date   End date.
	 */
	public function __construct( string $start_date, string $end_date ) {
		$this->start_date = $start_date;
		$this->end_date   = $end_date;
	}

	/**
	 * Serializes object to JSON-compatible array.
	 *
	 * @return array
	 */
	public function jsonSerialize() {
		return $this->auto_serialize();
	}

	/**
	 * Returns end date.
	 *
	 * @return string
	 */
	public function get_end_date(): string {
		return $this->end_date;
	}

	/**
	 * Sets end date.
	 *
	 * @param string $end_date End date.
	 *
	 * @return self
	 */
	public function set_end_date( string $end_date ): self {
		$this->end_date = $end_date;

		return $this;
	}

	/**
	 * Returns start date.
	 *
	 * @return string
	 */
	public function get_start_date(): string {
		return $this->start_date;
	}

	/**
	 * Sets start date.
	 *
	 * @param string $start_date Start date.
	 *
	 * @return self
	 */
	public function set_start_date( string $start_date ): self {
		$this->start_date = $start_date;

		return $this;
	}
}
