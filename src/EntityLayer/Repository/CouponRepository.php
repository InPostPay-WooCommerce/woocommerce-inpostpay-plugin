<?php
/**
 * Repository handling WooCommerce Coupon entities.
 *
 * @package Ilabs\Inpost_Pay\EntityLayer\Repository
 */

declare( strict_types=1 );

namespace Ilabs\Inpost_Pay\EntityLayer\Repository;

use Ilabs\Inpost_Pay\EntityLayer\Entity\CouponEntity;
use Ilabs\Inpost_Pay\Lib\Coupons\Coupon;

/**
 * Class CouponRepository
 *
 * Provides methods for retrieving and managing WooCommerce coupons.
 */
class CouponRepository extends BaseRepository {

	public const SERVICE_KEY = 'repository.coupon';

	/**
	 * Entity class handled by this repository.
	 *
	 * @var string
	 */
	protected string $entity_class = CouponEntity::class;

	/**
	 * Find coupons marked as visible in the app.
	 *
	 * @param int $limit Maximum number of coupons to return. Default 500.
	 *
	 * @return array List of visible coupons.
	 */
	public function find_visible_in_app( int $limit = 500 ): array {
		global $wpdb;

		$sql = "
			SELECT DISTINCT p.ID, p.post_title, p.post_excerpt, p.post_date
			FROM {$wpdb->prefix}posts p
			INNER JOIN {$wpdb->prefix}postmeta m
				ON p.ID = m.post_id
			WHERE p.post_type = 'shop_coupon'
			  AND p.post_status = 'publish'
			  AND m.meta_key = %s
			  AND m.meta_value = 'yes'
			ORDER BY p.post_date DESC
			LIMIT %d
		";

		$sql = $this->connection->prepare( $sql, Coupon::META_VISIBLE_IN_APP, $limit );

		return $this->find_by_sql( $sql );
	}

	/**
	 * Find all meta data for given coupon IDs.
	 *
	 * @param array $coupon_ids List of coupon IDs.
	 *
	 * @return array List of coupon meta data arrays.
	 */
	public function find_meta_for_coupons( array $coupon_ids ): array {
		if ( empty( $coupon_ids ) ) {
			return array();
		}

		global $wpdb;

		$placeholders = implode( ',', array_fill( 0, count( $coupon_ids ), '%d' ) );

		$sql = $this->connection->prepare(
			"SELECT post_id, meta_key, meta_value
			 FROM {$wpdb->prefix}postmeta
			 WHERE post_id IN ($placeholders)
			 ORDER BY post_id",
			...$coupon_ids
		);

		return $this->connection->select( $sql );
	}
}
