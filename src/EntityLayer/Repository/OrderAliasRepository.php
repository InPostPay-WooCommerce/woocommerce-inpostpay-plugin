<?php
/**
 * Repository handling OrderAlias entity operations.
 *
 * @package Ilabs\Inpost_Pay\EntityLayer\Repository
 */

declare( strict_types=1 );

namespace Ilabs\Inpost_Pay\EntityLayer\Repository;

use Ilabs\Inpost_Pay\EntityLayer\Entity\BaseEntity;
use Ilabs\Inpost_Pay\EntityLayer\Entity\OrderAliasEntity;

/**
 * Class OrderAliasRepository
 *
 * Provides methods for fetching and checking order aliases.
 */
class OrderAliasRepository extends BaseRepository {

	public const SERVICE_KEY = 'repository.order_alias';

	/**
	 * Entity class handled by this repository.
	 *
	 * @var string
	 */
	protected string $entity_class = OrderAliasEntity::class;

	/**
	 * Find an order alias by alias order ID.
	 *
	 * @param string $alias_order_id The alias order ID.
	 *
	 * @return BaseEntity|null The matching entity or null if not found.
	 */
	public function find_by_alias( string $alias_order_id ): ?BaseEntity {
		return $this->find_one_by( array( 'alias_order_id' => $alias_order_id ) );
	}

	/**
	 * Find an order alias by order ID.
	 *
	 * @param int $order_id The order ID.
	 *
	 * @return BaseEntity|null The matching entity or null if not found.
	 */
	public function find_by_order_id( int $order_id ): ?BaseEntity {
		return $this->find_one_by( array( 'order_id' => $order_id ) );
	}

	/**
	 * Check if an alias exists.
	 *
	 * @param string $alias_order_id The alias order ID to check.
	 *
	 * @return bool True if alias exists, false otherwise.
	 */
	public function alias_exists( string $alias_order_id ): bool {
		return ( null !== $this->find_by_alias( $alias_order_id ) );
	}
}
