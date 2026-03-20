<?php
/**
 * Entity representing basket binding data.
 *
 * @package Ilabs\Inpost_Pay\EntityLayer\Entity
 */

declare( strict_types=1 );

namespace Ilabs\Inpost_Pay\EntityLayer\Entity;

/**
 * Class BasketBindingEntity
 *
 * Represents a mapping between basket ID and its API binding key.
 */
class BasketBindingEntity extends BaseEntity {

	/**
	 * Table name for basket bindings.
	 *
	 * @var string
	 */
	protected static string $table = 'izi_basket_bindings';

	/**
	 * Primary key column name.
	 *
	 * @var string
	 */
	protected static string $primary_key = 'id';

	/**
	 * Basket binding identifier.
	 *
	 * @var int|null
	 */
	protected ?int $id = null;

	/**
	 * Unique basket ID (UUID).
	 *
	 * @var string|null
	 */
	protected ?string $basket_id = null;

	/**
	 * API key associated with basket binding.
	 *
	 * @var string|null
	 */
	protected ?string $basket_binding_api_key = null;

	/**
	 * Creation timestamp.
	 *
	 * @var string|null
	 */
	protected ?string $created_at = null;

	/**
	 * Last update timestamp.
	 *
	 * @var string|null
	 */
	protected ?string $updated_at = null;

	/**
	 * Get binding ID.
	 *
	 * @return int|null The binding ID.
	 */
	public function get_id(): ?int {
		return $this->id;
	}

	/**
	 * Set binding ID.
	 *
	 * @param int|null $value The binding ID.
	 *
	 * @return void
	 */
	public function set_id( $value ): void {
		$this->id = ( null !== $value ) ? (int) $value : null;
	}

	/**
	 * Get basket ID.
	 *
	 * @return string|null Basket identifier.
	 */
	public function get_basket_id(): ?string {
		return $this->basket_id;
	}

	/**
	 * Set basket ID.
	 *
	 * @param string|null $value Basket identifier.
	 *
	 * @return void
	 */
	public function set_basket_id( ?string $value ): void {
		$this->basket_id = $value;
	}

	/**
	 * Get basket binding API key.
	 *
	 * @return string|null Basket API key.
	 */
	public function get_basket_binding_api_key(): ?string {
		return $this->basket_binding_api_key;
	}

	/**
	 * Set basket binding API key.
	 *
	 * @param string|null $value Basket API key.
	 *
	 * @return void
	 */
	public function set_basket_binding_api_key( ?string $value ): void {
		$this->basket_binding_api_key = $value;
	}

	/**
	 * Get creation timestamp.
	 *
	 * @return string|null Creation date.
	 */
	public function get_created_at(): ?string {
		return $this->created_at;
	}

	/**
	 * Set creation timestamp.
	 *
	 * @param string|null $value Creation date.
	 *
	 * @return void
	 */
	public function set_created_at( ?string $value ): void {
		$this->created_at = $value;
	}

	/**
	 * Get last update timestamp.
	 *
	 * @return string|null Update date.
	 */
	public function get_updated_at(): ?string {
		return $this->updated_at;
	}

	/**
	 * Set last update timestamp.
	 *
	 * @param string|null $value Update date.
	 *
	 * @return void
	 */
	public function set_updated_at( ?string $value ): void {
		$this->updated_at = $value;
	}
}
