<?php
/**
 * Repository handling BasketBinding entity operations.
 *
 * @package Ilabs\Inpost_Pay\EntityLayer\Repository
 */

declare( strict_types=1 );

namespace Ilabs\Inpost_Pay\EntityLayer\Repository;

use Ilabs\Inpost_Pay\EntityLayer\Entity\BasketBindingEntity;
use Ilabs\Inpost_Pay\Exception\RepositoryException;
use Ilabs\Inpost_Pay\Logger;

/**
 * Class BasketBindingRepository
 *
 * Provides methods for managing basket bindings and resolving conflicts.
 */
class BasketBindingRepository extends BaseRepository {

	public const SERVICE_KEY = 'repository.basket_binding';

	/**
	 * Entity class handled by this repository.
	 *
	 * @var string
	 */
	protected string $entity_class = BasketBindingEntity::class;

	/**
	 * Find a basket binding by basket ID.
	 *
	 * @param string $basket_id The basket identifier.
	 *
	 * @return BasketBindingEntity|null The matching entity or null if not found.
	 */
	public function find_by_basket_id( string $basket_id ): ?BasketBindingEntity {
		return $this->find_one_by( array( 'basket_id' => $basket_id ) );
	}

	/**
	 * Find a basket binding by API key.
	 *
	 * @param string $api_key The basket binding API key.
	 *
	 * @return BasketBindingEntity|null The matching entity or null if not found.
	 */
	public function find_by_api_key( string $api_key ): ?BasketBindingEntity {
		return $this->find_one_by( array( 'basket_binding_api_key' => $api_key ) );
	}

	/**
	 * Create or update basket binding with conflict resolution.
	 *
	 * Ensures a strict 1:1 relationship between basket_id and basket_binding_api_key.
	 * Handles insertions, updates, and conflicts where the same API key belongs to a different basket.
	 *
	 * @param string $basket_id Basket identifier (UUID).
	 * @param string $api_key   Basket binding API key from InPost.
	 *
	 * @return BasketBindingEntity The created or updated basket binding entity.
	 *
	 * @throws RepositoryException When a database error occurs.
	 */
	public function create_or_update( string $basket_id, string $api_key ): BasketBindingEntity {
		$existing_by_api_key = $this->find_by_api_key( $api_key );

		if ( $existing_by_api_key && $existing_by_api_key->get_basket_id() !== $basket_id ) {
			Logger::log(
				"[BasketBindingRepo] CONFLICT: api_key={$api_key} bound to basket={$existing_by_api_key->get_basket_id()}, " .
				"requested for basket={$basket_id}. Deleting old binding."
			);

			$this->delete( $existing_by_api_key );
		}

		$existing_by_basket = $this->find_by_basket_id( $basket_id );

		if ( $existing_by_basket ) {
			if ( $existing_by_basket->get_basket_binding_api_key() !== $api_key ) {
				Logger::log( "[BasketBindingRepo] Updating api_key for basket={$basket_id}" );

				$sql = $this->connection->prepare(
					"UPDATE {$this->get_table_name()}
					 SET basket_binding_api_key = %s, updated_at = NOW()
					 WHERE basket_id = %s",
					$api_key,
					$basket_id
				);

				$this->connection->query( $sql );

				if ( $this->cache ) {
					$cache_key = $this->get_cache_key( $existing_by_basket->get_id() );
					$this->cache->delete( $cache_key );
				}
			} else {
				Logger::log( "[BasketBindingRepo] No change - api_key already correct for basket={$basket_id}" );
			}

			return $this->find_by_basket_id( $basket_id );
		}

		$sql = $this->connection->prepare(
			"INSERT INTO {$this->get_table_name()} (basket_id, basket_binding_api_key, created_at)
			 VALUES (%s, %s, NOW())",
			$basket_id,
			$api_key
		);

		$this->connection->query( $sql );

		return $this->find_by_basket_id( $basket_id );
	}

	/**
	 * Delete binding by basket_id.
	 *
	 * @param string $basket_id Basket identifier.
	 *
	 * @return bool True if deleted, false if not found.
	 */
	public function delete_by_basket_id( string $basket_id ): bool {
		$entity = $this->find_by_basket_id( $basket_id );

		if ( ! $entity ) {
			return false;
		}

		return $this->delete( $entity );
	}

	/**
	 * Delete binding by API key.
	 *
	 * @param string $api_key Basket binding API key.
	 *
	 * @return bool True if deleted, false if not found.
	 */
	public function delete_by_api_key( string $api_key ): bool {
		$entity = $this->find_by_api_key( $api_key );

		if ( ! $entity ) {
			return false;
		}

		return $this->delete( $entity );
	}
}
