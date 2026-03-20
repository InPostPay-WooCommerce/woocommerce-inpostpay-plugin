<?php
/**
 * Repository handling UnavailableEntity operations.
 *
 * @package Ilabs\Inpost_Pay\EntityLayer\Repository
 */

declare( strict_types=1 );

namespace Ilabs\Inpost_Pay\EntityLayer\Repository;

use Ilabs\Inpost_Pay\EntityLayer\Entity\UnavailableEntity;

/**
 * Class UnavailableRepository
 *
 * Provides methods to manage unavailable categories and products.
 */
class UnavailableRepository extends BaseRepository {

	public const SERVICE_KEY = 'repository.unavailable';

	/**
	 * Entity class handled by this repository.
	 *
	 * @var string
	 */
	protected string $entity_class = UnavailableEntity::class;

	/**
	 * Get all unavailable categories.
	 *
	 * @return array List of unavailable categories.
	 */
	public function get_all_categories(): array {
		$sql = "SELECT DISTINCT * FROM {$this->get_table_name()}
		        WHERE category_id IS NOT NULL AND product_id IS NULL";

		return $this->find_by_sql( $sql );
	}

	/**
	 * Get all unavailable category IDs.
	 *
	 * @return array List of category IDs.
	 */
	public function get_category_ids(): array {
		$sql = "SELECT DISTINCT category_id FROM {$this->get_table_name()}
		        WHERE category_id IS NOT NULL AND product_id IS NULL";

		$results = $this->connection->get_results( $sql, ARRAY_N );

		return $results;
	}

	/**
	 * Get all unavailable products.
	 *
	 * @return array List of unavailable products.
	 */
	public function get_all_products(): array {
		$sql = "SELECT DISTINCT * FROM {$this->get_table_name()}
		        WHERE product_id IS NOT NULL AND category_id IS NULL";

		return $this->find_by_sql( $sql );
	}

	/**
	 * Get all unavailable product IDs.
	 *
	 * @return array List of product IDs.
	 */
	public function get_product_ids(): array {
		$sql = "SELECT DISTINCT product_id FROM {$this->get_table_name()}
		        WHERE product_id IS NOT NULL AND category_id IS NULL";

		$results = $this->connection->get_results( $sql );

		return array_column( $results, 'product_id' );
	}

	/**
	 * Delete all unavailable products.
	 *
	 * @return int Number of deleted products.
	 */
	public function delete_all_products(): int {
		$sql = $this->connection->prepare(
			"DELETE FROM {$this->get_table_name()} WHERE product_id IS NOT NULL"
		);

		return (int) $this->connection->query( $sql );
	}

	/**
	 * Delete multiple entities in a single query.
	 *
	 * @param UnavailableEntity[] $entities Array of entities to delete.
	 *
	 * @return int Number of deleted rows.
	 */
	public function delete_many( array $entities ): int {
		if ( empty( $entities ) ) {
			return 0;
		}

		$ids = array_map(
			static function ( UnavailableEntity $entity ) {
				return (int) $entity->get_id();
			},
			$entities
		);

		$placeholders = implode( ',', array_fill( 0, count( $ids ), '%d' ) );

		$sql = $this->connection->prepare(
			"DELETE FROM {$this->get_table_name()} WHERE id IN ($placeholders)",
			...$ids
		);

		return (int) $this->connection->query( $sql );
	}
}
