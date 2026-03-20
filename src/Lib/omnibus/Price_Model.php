<?php

namespace Ilabs\Inpost_Pay\Lib\omnibus;

use DateTime;

class Price_Model {

	const ARRAY_PRICE_KEY_0 = 0;

	const ARRAY_DATETIME_KEY_1 = 1;

	const ARRAY_IS_ON_SALE_KEY_2 = 2;

	const ARRAY_IS_PURCHASABLE_KEY_3 = 3;

	const IS_PURCHASABLE_TRUE = 1;

	const IS_PURCHASABLE_FALSE = 0;

	const IS_PURCHASABLE_UNKNOWN = 2;

	/**
	 * @var float
	 */
	private $price;

	/**
	 * @var DateTime
	 */
	private $date_time;

	/**
	 * @var bool
	 */
	private $is_on_sale;

	/**
	 * @var int
	 */
	private $is_purchasable;


	public function __construct(
		float $price_net,
		DateTime $date_time,
		bool $is_sale_price,
		int $is_purchasable
	) {
		$this->price     = $price_net;
		$this->date_time = $date_time;
		$this->is_on_sale     = $is_sale_price;
		$this->is_purchasable = $is_purchasable;
	}

	public function to_array(): array {
		return [
			self::ARRAY_PRICE_KEY_0          => $this->price,
			self::ARRAY_DATETIME_KEY_1       => $this->date_time->format( 'Y-m-d H:i:s' ),
			self::ARRAY_IS_ON_SALE_KEY_2     => $this->is_on_sale,
			self::ARRAY_IS_PURCHASABLE_KEY_3 => $this->is_purchasable,
		];
	}

	/**
	 * @return float
	 */
	public function get_price_float(): float {
		return $this->price;
	}

	/**
	 * @return DateTime
	 */
	public function get_date_time(): DateTime {
		return $this->date_time;
	}

	/**
	 * @return bool
	 */
	public function get_is_on_sale(): bool {
		return $this->is_on_sale;
	}

	public function get_is_on_sale_as_string(): string {
		return $this->is_on_sale ? 'yes' : 'no';
	}

	public function get_is_purchasable(): int {
		return $this->is_purchasable;
	}

	public function is_purchasable(): bool {
		return $this->is_purchasable === self::IS_PURCHASABLE_TRUE;
	}

	public function is_purchasable_status_unknown(): bool {
		return ( $this->is_purchasable !== self::IS_PURCHASABLE_TRUE
		         && $this->is_purchasable !== self::IS_PURCHASABLE_FALSE
		);
	}
}
