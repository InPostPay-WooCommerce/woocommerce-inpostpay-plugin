<?php
/**
 * Repository handling CartSession entity operations.
 *
 * @package Ilabs\Inpost_Pay\EntityLayer\Repository
 */

declare( strict_types=1 );

namespace Ilabs\Inpost_Pay\EntityLayer\Repository;

use Ilabs\Inpost_Pay\EntityLayer\Entity\BaseEntity;
use Ilabs\Inpost_Pay\EntityLayer\Entity\CartSessionEntity;

/**
 * Class CartSessionRepository
 *
 * Provides methods for managing cart sessions, including expired session cleanup.
 */
class CartSessionRepository extends BaseRepository {

	public const SERVICE_KEY = 'repository.cart_session';

	/**
	 * Entity class handled by this repository.
	 *
	 * @var string
	 */
	protected string $entity_class = CartSessionEntity::class;

	/**
	 * Delete expired cart sessions in a single batched query.
	 *
	 * Uses a direct DELETE with LIMIT to avoid loading all rows into PHP memory
	 * and to prevent long-running table locks on large tables.
	 *
	 * @param int $limit Maximum number of rows to delete per call. Default 500.
	 *
	 * @return int Number of deleted sessions.
	 */
	public function delete_expired_sessions( int $limit = 500 ): int {
		$current_time = time();
		$limit        = max( 1, min( $limit, 5000 ) );

		$sql = $this->connection->prepare(
			"DELETE FROM {$this->get_table_name()} WHERE session_expiry > 0 AND session_expiry < %d ORDER BY session_expiry ASC LIMIT %d",
			$current_time,
			$limit
		);

		return (int) $this->execute_sql( $sql );
	}
}
