<?php
/**
 * Coupon entity representing WooCommerce coupon data.
 *
 * @package Ilabs\Inpost_Pay\EntityLayer\Entity
 */

declare( strict_types=1 );

namespace Ilabs\Inpost_Pay\EntityLayer\Entity;

/**
 * Class CouponEntity
 *
 * Represents coupon post data with related metadata.
 */
class CouponEntity extends BaseEntity {

	/**
	 * Database table name.
	 *
	 * @var string
	 */
	protected static string $table = 'posts';

	/**
	 * Primary key field.
	 *
	 * @var string
	 */
	protected static string $primary_key = 'ID';

	/**
	 * Coupon post ID.
	 *
	 * @var int|null
	 */
	protected ?int $ID = null;

	/**
	 * Coupon title.
	 *
	 * @var string|null
	 */
	protected ?string $post_title = null;

	/**
	 * Coupon short description.
	 *
	 * @var string|null
	 */
	protected ?string $post_excerpt = null;

	/**
	 * Coupon creation date.
	 *
	 * @var string|null
	 */
	protected ?string $post_date = null;

	/**
	 * Post type (should be 'shop_coupon').
	 *
	 * @var string|null
	 */
	protected ?string $post_type = null;

	/**
	 * Coupon post status.
	 *
	 * @var string|null
	 */
	protected ?string $post_status = null;

	/**
	 * Whether the coupon is visible in the app.
	 *
	 * @var string|null
	 */
	protected ?string $meta_visible_in_app = null;

	/**
	 * Coupon promotion URL.
	 *
	 * @var string|null
	 */
	protected ?string $meta_promotion_url = null;

	/**
	 * Coupon expiration date.
	 *
	 * @var string|null
	 */
	protected ?string $meta_date_expires = null;

	/**
	 * Coupon discount type (e.g., fixed_cart, percent).
	 *
	 * @var string|null
	 */
	protected ?string $meta_discount_type = null;

	/**
	 * Coupon description.
	 *
	 * @var string|null
	 */
	protected ?string $meta_description = null;

	/**
	 * Get coupon ID.
	 *
	 * @return int|null Coupon ID.
	 */
	public function get_id(): ?int {
		return $this->ID;
	}

	/**
	 * Set coupon ID.
	 *
	 * @param int|null $value Coupon ID value.
	 *
	 * @return void
	 */
	public function set_id( $value ): void {
		$this->ID = ( null !== $value ) ? (int) $value : null;
	}

	/**
	 * Get coupon title.
	 *
	 * @return string|null Coupon title.
	 */
	public function get_post_title(): ?string {
		return $this->post_title;
	}

	/**
	 * Set coupon title.
	 *
	 * @param string|null $value Coupon title.
	 *
	 * @return void
	 */
	public function set_post_title( ?string $value ): void {
		$this->post_title = $value;
	}

	/**
	 * Get coupon excerpt.
	 *
	 * @return string|null Coupon excerpt.
	 */
	public function get_post_excerpt(): ?string {
		return $this->post_excerpt;
	}

	/**
	 * Set coupon excerpt.
	 *
	 * @param string|null $value Coupon excerpt.
	 *
	 * @return void
	 */
	public function set_post_excerpt( ?string $value ): void {
		$this->post_excerpt = $value;
	}

	/**
	 * Get coupon creation date.
	 *
	 * @return string|null Coupon creation date.
	 */
	public function get_post_date(): ?string {
		return $this->post_date;
	}

	/**
	 * Set coupon creation date.
	 *
	 * @param string|null $value Coupon creation date.
	 *
	 * @return void
	 */
	public function set_post_date( ?string $value ): void {
		$this->post_date = $value;
	}

	/**
	 * Get coupon post type.
	 *
	 * @return string|null Post type.
	 */
	public function get_post_type(): ?string {
		return $this->post_type;
	}

	/**
	 * Set coupon post type.
	 *
	 * @param string|null $value Post type.
	 *
	 * @return void
	 */
	public function set_post_type( ?string $value ): void {
		$this->post_type = $value;
	}

	/**
	 * Get coupon post status.
	 *
	 * @return string|null Post status.
	 */
	public function get_post_status(): ?string {
		return $this->post_status;
	}

	/**
	 * Set coupon post status.
	 *
	 * @param string|null $value Post status.
	 *
	 * @return void
	 */
	public function set_post_status( ?string $value ): void {
		$this->post_status = $value;
	}

	/**
	 * Get visibility status in app.
	 *
	 * @return string|null Visibility flag.
	 */
	public function get_meta_visible_in_app(): ?string {
		return $this->meta_visible_in_app;
	}

	/**
	 * Set visibility status in app.
	 *
	 * @param string|null $value Visibility flag.
	 *
	 * @return void
	 */
	public function set_meta_visible_in_app( ?string $value ): void {
		$this->meta_visible_in_app = $value;
	}

	/**
	 * Get coupon promotion URL.
	 *
	 * @return string|null Promotion URL.
	 */
	public function get_meta_promotion_url(): ?string {
		return $this->meta_promotion_url;
	}

	/**
	 * Set coupon promotion URL.
	 *
	 * @param string|null $value Promotion URL.
	 *
	 * @return void
	 */
	public function set_meta_promotion_url( ?string $value ): void {
		$this->meta_promotion_url = $value;
	}

	/**
	 * Get coupon expiration date.
	 *
	 * @return string|null Expiration date.
	 */
	public function get_meta_date_expires(): ?string {
		return $this->meta_date_expires;
	}

	/**
	 * Set coupon expiration date.
	 *
	 * @param string|null $value Expiration date.
	 *
	 * @return void
	 */
	public function set_meta_date_expires( ?string $value ): void {
		$this->meta_date_expires = $value;
	}

	/**
	 * Get coupon discount type.
	 *
	 * @return string|null Discount type.
	 */
	public function get_meta_discount_type(): ?string {
		return $this->meta_discount_type;
	}

	/**
	 * Set coupon discount type.
	 *
	 * @param string|null $value Discount type.
	 *
	 * @return void
	 */
	public function set_meta_discount_type( ?string $value ): void {
		$this->meta_discount_type = $value;
	}

	/**
	 * Get coupon description.
	 *
	 * @return string|null Coupon description.
	 */
	public function get_meta_description(): ?string {
		return $this->meta_description;
	}

	/**
	 * Set coupon description.
	 *
	 * @param string|null $value Coupon description.
	 *
	 * @return void
	 */
	public function set_meta_description( ?string $value ): void {
		$this->meta_description = $value;
	}
}
