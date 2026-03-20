<?php
/**
 * Entity representing unavailable product or category delivery restrictions.
 *
 * @package Ilabs\Inpost_Pay\EntityLayer\Entity
 */

declare( strict_types=1 );

namespace Ilabs\Inpost_Pay\EntityLayer\Entity;

/**
 * Class UnavailableEntity
 *
 * Represents unavailable products or categories for specific delivery types.
 */
class UnavailableEntity extends BaseEntity {

	/**
	 * Table name for unavailable records.
	 *
	 * @var string
	 */
	protected static string $table = 'izi_unavailable';

	/**
	 * Primary key column name.
	 *
	 * @var string
	 */
	protected static string $primary_key = 'id';

	/**
	 * Locker delivery type constant.
	 *
	 * @var string
	 */
	public const APM = '1';

	/**
	 * Courier delivery type constant.
	 *
	 * @var string
	 */
	public const COURIER = '2';

	/**
	 * Both delivery types constant.
	 *
	 * @var string
	 */
	public const BOTH = '3';

	/**
	 * Mapping of delivery type codes to names.
	 *
	 * @var array<string, string>
	 */
	public const DELIVERY_TYPES_NAMES = array(
		self::BOTH    => 'BOTH',
		self::APM     => 'APM',
		self::COURIER => 'COURIER',
	);

	/**
	 * List of all delivery types.
	 *
	 * @var array<string>
	 */
	public const DELIVERY_TYPES = array(
		self::BOTH,
		self::APM,
		self::COURIER,
	);

	/**
	 * Entity ID.
	 *
	 * @var int|null
	 */
	protected ?int $id = null;

	/**
	 * Product identifier.
	 *
	 * @var int|null
	 */
	protected ?int $product_id = null;

	/**
	 * Category identifier.
	 *
	 * @var int|null
	 */
	protected ?int $category_id = null;

	/**
	 * Delivery type restriction.
	 *
	 * @var string|null
	 */
	protected ?string $delivery_type = null;

	/**
	 * Get entity ID.
	 *
	 * @return int|null Entity ID.
	 */
	public function get_id(): ?int {
		return $this->id;
	}

	/**
	 * Set entity ID.
	 *
	 * @param int|null $value Entity ID.
	 *
	 * @return void
	 */
	public function set_id( $value ): void {
		$this->id = $value;
	}

	/**
	 * Get product ID.
	 *
	 * @return int|null Product ID.
	 */
	public function get_product_id(): ?int {
		return $this->product_id;
	}

	/**
	 * Set product ID.
	 *
	 * @param int|null $value Product ID.
	 *
	 * @return void
	 */
	public function set_product_id( ?int $value ): void {
		$this->product_id = $value;
	}

	/**
	 * Get category ID.
	 *
	 * @return int|null Category ID.
	 */
	public function get_category_id(): ?int {
		return $this->category_id;
	}

	/**
	 * Set category ID.
	 *
	 * @param int|null $value Category ID.
	 *
	 * @return void
	 */
	public function set_category_id( ?int $value ): void {
		$this->category_id = $value;
	}

	/**
	 * Get delivery type restriction.
	 *
	 * @return string|null Delivery type.
	 */
	public function get_delivery_type(): ?string {
		return $this->delivery_type;
	}

	/**
	 * Set delivery type restriction.
	 *
	 * @param string|null $value Delivery type.
	 *
	 * @return void
	 */
	public function set_delivery_type( ?string $value ): void {
		$this->delivery_type = $value;
	}
}
