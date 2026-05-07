<?php

declare( strict_types=1 );

namespace Ilabs\Inpost_Pay\Lib\item;

use Ilabs\Inpost_Pay\Lib\Coupons\Coupon;
use Ilabs\Inpost_Pay\Lib\helpers\JsonSerializationHelper;
use Ilabs\Inpost_Pay\Lib\Item;

class PromotionsAvailable extends Item {
	use JsonSerializationHelper;

	public string $type;
	public string $promo_code_value;
	public string $description;
	public string $start_date;
	public string $end_date;
	// public int $priority = 0;
	public PromotionsAvailableDetails $details;

	public function __construct() {
		$this->details = new PromotionsAvailableDetails();
	}

	public function jsonSerialize(): array {
		return $this->autoSerialize();
	}

	public function get_type(): string {
		return $this->type;
	}

	public function set_type( string $type ): void {
		if ( in_array( $type, Coupon::ONLY_IN_APP_COUPONS ) ) {
			$this->type = 'ONLY_IN_APP';
		} else {
			$this->type = 'MERCHANT';
		}
	}

	public function get_promo_code_value(): string {
		return $this->promo_code_value;
	}

	public function set_promo_code_value( string $promo_code_value ): void {
		$this->promo_code_value = $promo_code_value;
	}

	public function get_description(): string {
		return $this->description;
	}

	public function set_description( string $description ): void {
		$this->description = substr( $description, 0, 60 );
	}

	public function get_start_date(): string {
		return $this->start_date;
	}

	public function set_start_date( string $start_date ): void {
		$this->start_date = $start_date;
	}

	public function get_end_date(): string {
		return $this->end_date;
	}

	public function set_end_date( string $end_date ): void {
		$this->end_date = $end_date;
	}

	public function get_priority(): int {
		return $this->priority;
	}

	public function set_priority( int $priority ): void {
		$this->priority = $priority;
	}

	public function get_details(): PromotionsAvailableDetails {
		return $this->details;
	}

	public function set_details( PromotionsAvailableDetails $details ): void {
		$this->details = $details;
	}
}
