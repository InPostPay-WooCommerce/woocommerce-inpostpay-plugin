<?php
/**
 * Promotions available item.
 *
 * @package Ilabs\Inpost_Pay\Lib\item
 */

declare( strict_types=1 );

namespace Ilabs\Inpost_Pay\Lib\item;

use Ilabs\Inpost_Pay\Lib\Coupons\Coupon;
use Ilabs\Inpost_Pay\Lib\helpers\JsonSerializationHelper;
use Ilabs\Inpost_Pay\Lib\Item;

/**
 * Represents a promotion available in the basket.
 */
class PromotionsAvailable extends Item {
	use JsonSerializationHelper;

	public string $type;
	public string $promo_code_value;
	public string $description;
	public string $start_date;
	public string $end_date;
	public PromotionsAvailableDetails $details;

	/**
	 * Initializes promotion with default details object.
	 */
	public function __construct() {
		$this->details = new PromotionsAvailableDetails();
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
	 * Returns promotion type.
	 *
	 * @return string
	 */
	public function get_type(): string {
		return $this->type;
	}

	/**
	 * Sets promotion type based on coupon classification.
	 *
	 * @param string $type Promotion type.
	 *
	 * @return void
	 */
	public function set_type( string $type ): void {
		if ( in_array( $type, Coupon::ONLY_IN_APP_COUPONS, true ) ) {
			$this->type = 'ONLY_IN_APP';
		} else {
			$this->type = 'MERCHANT';
		}
	}

	/**
	 * Returns promo code value.
	 *
	 * @return string
	 */
	public function get_promo_code_value(): string {
		return $this->promo_code_value;
	}

	/**
	 * Sets promo code value.
	 *
	 * @param string $promo_code_value Promo code value.
	 *
	 * @return void
	 */
	public function set_promo_code_value( string $promo_code_value ): void {
		$this->promo_code_value = $promo_code_value;
	}

	/**
	 * Returns promotion description.
	 *
	 * @return string
	 */
	public function get_description(): string {
		return $this->description;
	}

	/**
	 * Sets promotion description, truncated to 60 characters.
	 *
	 * @param string $description Promotion description.
	 *
	 * @return void
	 */
	public function set_description( string $description ): void {
		$this->description = substr( $description, 0, 60 );
	}

	/**
	 * Returns promotion start date.
	 *
	 * @return string
	 */
	public function get_start_date(): string {
		return $this->start_date;
	}

	/**
	 * Sets promotion start date.
	 *
	 * @param string $start_date Promotion start date.
	 *
	 * @return void
	 */
	public function set_start_date( string $start_date ): void {
		$this->start_date = $start_date;
	}

	/**
	 * Returns promotion end date.
	 *
	 * @return string
	 */
	public function get_end_date(): string {
		return $this->end_date;
	}

	/**
	 * Sets promotion end date.
	 *
	 * @param string $end_date Promotion end date.
	 *
	 * @return void
	 */
	public function set_end_date( string $end_date ): void {
		$this->end_date = $end_date;
	}

	/**
	 * Returns promotion priority.
	 *
	 * @return int
	 */
	public function get_priority(): int {
		return $this->priority;
	}

	/**
	 * Sets promotion priority.
	 *
	 * @param int $priority Promotion priority.
	 *
	 * @return void
	 */
	public function set_priority( int $priority ): void {
		$this->priority = $priority;
	}

	/**
	 * Returns promotion details.
	 *
	 * @return PromotionsAvailableDetails
	 */
	public function get_details(): PromotionsAvailableDetails {
		return $this->details;
	}

	/**
	 * Sets promotion details.
	 *
	 * @param PromotionsAvailableDetails $details Promotion details.
	 *
	 * @return void
	 */
	public function set_details( PromotionsAvailableDetails $details ): void {
		$this->details = $details;
	}
}
