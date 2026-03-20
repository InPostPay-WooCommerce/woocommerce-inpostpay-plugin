<?php
/**
 * Base repository for entity persistence.
 *
 * @package Ilabs\Inpost_Pay\EntityLayer\Repository
 */

declare( strict_types=1 );

namespace Ilabs\Inpost_Pay\EntityLayer\Repository;

use Ilabs\Inpost_Pay\EntityLayer\Cache\CacheInterface;
use Ilabs\Inpost_Pay\EntityLayer\Database\Connection;
use Ilabs\Inpost_Pay\EntityLayer\Entity\BaseEntity;
use Ilabs\Inpost_Pay\EntityLayer\Entity\EntityInterface;
use Ilabs\Inpost_Pay\EntityLayer\Helpers\ArrayUtils;
use Ilabs\Inpost_Pay\EntityLayer\Tracker\ChangeTracker;
use Ilabs\Inpost_Pay\Exception\EntityNotFoundException;
use Ilabs\Inpost_Pay\Exception\InvalidEntityException;
use Ilabs\Inpost_Pay\Exception\RepositoryException;
use Ilabs\Inpost_Pay\Lib\helpers\CacheHelper;

/**
 * Abstract base repository providing CRUD operations.
 */
abstract class BaseRepository {

	/**
	 * Database connection.
	 *
	 * @var Connection
	 */
	protected Connection $connection;

	/**
	 * Change tracker instance.
	 *
	 * @var ChangeTracker
	 */
	protected ChangeTracker $tracker;

	/**
	 * Cache handler.
	 *
	 * @var CacheInterface|null
	 */
	protected ?CacheInterface $cache = null;

	/**
	 * Entity class name.
	 *
	 * Must be defined in child classes.
	 *
	 * @var string
	 */
	protected string $entity_class = '';

	/**
	 * Constructor.
	 *
	 * @param Connection          $connection Database connection.
	 * @param ChangeTracker|null  $tracker    Optional change tracker instance.
	 * @param CacheInterface|null $cache      Optional cache handler.
	 *
	 * @throws InvalidEntityException If entity class is not defined, does not exist, or is invalid.
	 */
	public function __construct(
		Connection $connection,
		?ChangeTracker $tracker = null,
		?CacheInterface $cache = null
	) {
		$this->connection = $connection;
		$this->tracker    = $tracker ?? new ChangeTracker();
		$this->cache      = $cache;

		if ( empty( $this->entity_class ) ) {
			throw new InvalidEntityException(
				sprintf( 'Repository "%s" must define $entity_class property.', static::class )
			);
		}

		if ( ! class_exists( $this->entity_class ) ) {
			throw new InvalidEntityException(
				sprintf( 'Entity class "%s" does not exist.', $this->entity_class )
			);
		}

		if ( ! is_subclass_of( $this->entity_class, EntityInterface::class ) ) {
			throw new InvalidEntityException(
				sprintf( 'Entity class "%s" must implement EntityInterface.', $this->entity_class )
			);
		}
	}

	/**
	 * Find entity by primary key.
	 *
	 * @param mixed $id Primary key value.
	 *
	 * @return BaseEntity|null The found entity or null if not found.
	 *
	 * @throws RepositoryException When a database error occurs.
	 */
	public function find( $id ): ?BaseEntity {
		$cache_key = $this->get_cache_key( $id );

		if ( $this->cache && $this->cache->has( $cache_key ) ) {
			return $this->cache->get( $cache_key );
		}
		CacheHelper::disableWPCache();

		$table       = esc_sql( $this->get_table_name() );
		$primary_key = esc_sql( $this->get_primary_key() );

		$sql = $this->connection->prepare(
			"SELECT * FROM {$table} WHERE {$primary_key} = %s LIMIT 1",
			$id
		);

		$result = $this->connection->select_one( $sql );

		if ( ! $result ) {
			return null;
		}

		$entity = $this->hydrate( $result );

		if ( $this->cache ) {
			$this->cache->set( $cache_key, $entity );
		}

		return $entity;
	}

	/**
	 * Generate cache key for given entity ID.
	 *
	 * @param mixed $id The entity ID.
	 *
	 * @return string The cache key.
	 */
	protected function get_cache_key( $id ): string {
		$table_name = sanitize_key( $this->get_table_name_without_prefix() );
		$entity_id  = sanitize_key( (string) $id );

		return $table_name . '_' . $entity_id;
	}

	/**
	 * Find entity by primary key or throw exception.
	 *
	 * @param mixed $id Primary key value.
	 *
	 * @return BaseEntity The found entity.
	 *
	 * @throws EntityNotFoundException When entity is not found.
	 */
	public function find_or_fail( $id ): BaseEntity {
		$entity = $this->find( $id );

		if ( ! $entity ) {
			throw new EntityNotFoundException( $this->entity_class, $id );
		}

		return $entity;
	}

	/**
	 * Find single entity by criteria.
	 *
	 * @param array $criteria Associative array of column => value.
	 *
	 * @return BaseEntity|null The found entity or null if not found.
	 *
	 * @throws RepositoryException When a database error occurs.
	 */
	public function find_one_by( array $criteria ): ?BaseEntity {
		$results = $this->find_by( $criteria, 1 );

		return $results[0] ?? null;
	}

	/**
	 * Find entities by criteria.
	 *
	 * @param array    $criteria Associative array of column => value.
	 * @param int|null $limit    Optional result limit.
	 * @param int      $offset   Optional result offset.
	 *
	 * @return BaseEntity[] List of entities.
	 *
	 * @throws RepositoryException When a database error occurs.
	 */
	public function find_by( array $criteria, ?int $limit = null, int $offset = 0 ): array {
		CacheHelper::disableWPCache();
		$table = $this->get_table_name();
		$where = $this->build_where_clause( $criteria );

		$sql = "SELECT * FROM {$table} WHERE {$where}";

		if ( null !== $limit ) {
			$sql .= $this->connection->prepare( ' LIMIT %d', $limit );
		}

		if ( 0 < $offset ) {
			$sql .= $this->connection->prepare( ' OFFSET %d', $offset );
		}

		$results = $this->connection->select( $sql );

		return $this->hydrate_collection( $results );
	}

	/**
	 * Find all entities.
	 *
	 * @param int|null $limit  Optional result limit.
	 * @param int      $offset Optional result offset.
	 * @return BaseEntity[]
	 * @throws RepositoryException On database error.
	 */
	public function find_all( ?int $limit = null, int $offset = 0 ): array {
		CacheHelper::disableWPCache();
		$table = $this->get_table_name();
		$sql   = "SELECT * FROM {$table}";

		if ( null !== $limit ) {
			$sql .= $this->connection->prepare( ' LIMIT %d', $limit );
		}

		if ( $offset > 0 ) {
			$sql .= $this->connection->prepare( ' OFFSET %d', $offset );
		}

		$results = $this->connection->select( $sql );

		return $this->hydrate_collection( $results );
	}

	/**
	 * Save entity (INSERT or UPDATE).
	 *
	 * @param BaseEntity $entity Entity to save.
	 * @return BaseEntity Saved entity with updated ID.
	 * @throws RepositoryException On database error.
	 */
	public function save( BaseEntity $entity ): BaseEntity {
		if ( $entity->is_persisted() ) {
			return $this->update( $entity );
		}

		if ( $this->cache ) {
			$cache_key = $this->get_cache_key( $entity->get_id() );
			$this->cache->set( $cache_key, $entity );
		}

		return $this->insert( $entity );
	}

	/**
	 * Insert new entity.
	 *
	 * @param BaseEntity $entity Entity to insert.
	 * @return BaseEntity Entity with assigned ID.
	 * @throws RepositoryException On database error.
	 */
	protected function insert( BaseEntity $entity ): BaseEntity {
		$table       = $this->get_table_name_without_prefix();
		$primary_key = $this->get_primary_key();
		$data        = $entity->to_array( false );
		$data        = ArrayUtils::except( $data, array( $primary_key ) );

		$insert_id = $this->connection->insert( $table, $data );
		$entity->set_id( $insert_id );

		$this->tracker->track( $entity );

		return $entity;
	}

	/**
	 * Update existing entity.
	 *
	 * @param BaseEntity $entity Entity to update.
	 * @return BaseEntity Updated entity.
	 * @throws RepositoryException On database error.
	 */
	protected function update( BaseEntity $entity ): BaseEntity {
		if ( ! $this->tracker->is_tracked( $entity ) ) {
			$this->tracker->track( $entity );
		}

		$changes = $this->tracker->get_changed_values( $entity );

		if ( empty( $changes ) ) {
			return $entity;
		}

		$table       = $this->get_table_name_without_prefix();
		$primary_key = $this->get_primary_key();
		$id          = $entity->get_id();

		$changes = ArrayUtils::except( $changes, array( $primary_key ) );

		$this->connection->update(
			$table,
			$changes,
			array( $primary_key => $id )
		);

		$this->tracker->refresh( $entity );

		if ( $this->cache ) {
			$cache_key = $this->get_cache_key( $entity->get_id() );
			$this->cache->set( $cache_key, $entity );
		}

		return $entity;
	}

	/**
	 * Delete entity from database.
	 *
	 * @param BaseEntity $entity Entity to delete.
	 * @return bool True on success.
	 * @throws RepositoryException On database error.
	 */
	public function delete( BaseEntity $entity ): bool {
		if ( ! $entity->is_persisted() ) {
			return false;
		}

		$table       = $this->get_table_name_without_prefix();
		$primary_key = $this->get_primary_key();
		$id          = $entity->get_id();

		$this->connection->delete(
			$table,
			array( $primary_key => $id )
		);

		$this->tracker->untrack( $entity );
		$entity->set_id( null );

		if ( $this->cache ) {
			$cache_key = $this->get_cache_key( $entity->get_id() );
			$this->cache->delete( $cache_key );
		}

		return true;
	}

	/**
	 * Count entities matching criteria.
	 *
	 * @param array $criteria Optional criteria.
	 * @return int
	 * @throws RepositoryException On database error.
	 */
	public function count( array $criteria = array() ): int {
		CacheHelper::disableWPCache();
		$table = $this->get_table_name();
		$sql   = "SELECT COUNT(*) as count FROM {$table}";

		if ( ! empty( $criteria ) ) {
			$where = $this->build_where_clause( $criteria );
			$sql  .= " WHERE {$where}";
		}

		$result = $this->connection->select_one( $sql );

		return (int) ( $result->count ?? 0 );
	}

	/**
	 * Check if entity exists by criteria.
	 *
	 * @param array $criteria Criteria to check.
	 * @return bool
	 * @throws RepositoryException On database error.
	 */
	public function exists( array $criteria ): bool {
		return $this->count( $criteria ) > 0;
	}

	/**
	 * Hydrate entity from database result.
	 *
	 * @param object $data Database result row.
	 * @return BaseEntity
	 */
	protected function hydrate( object $data ): BaseEntity {
		$entity_class = $this->entity_class;
		$entity       = $entity_class::from_array( $data );

		$this->tracker->track( $entity );

		return $entity;
	}

	/**
	 * Hydrate collection of entities.
	 *
	 * @param array $results Array of database result rows.
	 * @return BaseEntity[]
	 */
	protected function hydrate_collection( array $results ): array {
		$entities = array();

		foreach ( $results as $result ) {
			$entities[] = $this->hydrate( $result );
		}

		return $entities;
	}

	/**
	 * Build WHERE clause from criteria.
	 *
	 * @param array $criteria Associative array of column => value.
	 * @return string
	 */
	protected function build_where_clause( array $criteria ): string {
		$conditions = array();

		foreach ( $criteria as $column => $value ) {
			if ( null === $value ) {
				$conditions[] = "`{$column}` IS NULL";
			} else {
				$conditions[] = $this->connection->prepare(
					"`{$column}` = %s",
					$value
				);
			}
		}

		return implode( ' AND ', $conditions );
	}

	/**
	 * Get full table name with prefix.
	 *
	 * @return string
	 */
	protected function get_table_name(): string {
		$entity_class = $this->entity_class;
		$table        = $entity_class::get_table_name();

		return $this->connection->get_table_name( $table );
	}

	/**
	 * Get table name without prefix.
	 *
	 * @return string
	 */
	protected function get_table_name_without_prefix(): string {
		$entity_class = $this->entity_class;
		return $entity_class::get_table_name();
	}

	/**
	 * Get primary key column name.
	 *
	 * @return string
	 */
	protected function get_primary_key(): string {
		$entity_class = $this->entity_class;
		return $entity_class::get_primary_key();
	}

	/**
	 * Get the change tracker instance.
	 *
	 * @return ChangeTracker
	 */
	public function get_tracker(): ChangeTracker {
		return $this->tracker;
	}

	/**
	 * Execute custom SQL query and return entities.
	 *
	 * @param string $sql SQL query with optional placeholders {table_name}, {table_prefix}.
	 * @return BaseEntity[]
	 * @throws RepositoryException On database error.
	 */
	public function find_by_sql( string $sql ): array {
		CacheHelper::disableWPCache();
		$sql     = $this->replace_sql_placeholders( $sql );
		$results = $this->connection->select( $sql );
		return $this->hydrate_collection( $results );
	}

	/**
	 * Execute custom SQL query and return single entity.
	 *
	 * @param string $sql SQL query with optional placeholders.
	 * @return BaseEntity|null
	 * @throws RepositoryException On database error.
	 */
	public function find_one_by_sql( string $sql ): ?BaseEntity {
		CacheHelper::disableWPCache();
		$results = $this->find_by_sql( $sql );
		return $results[0] ?? null;
	}

	/**
	 * Execute raw SQL query without returning results.
	 *
	 * @param string $sql SQL query.
	 * @return int|bool Number of affected rows or false.
	 * @throws RepositoryException On database error.
	 */
	public function execute_sql( string $sql ) {
		CacheHelper::disableWPCache();
		$sql = $this->replace_sql_placeholders( $sql );
		return $this->connection->query( $sql );
	}

	/**
	 * Replace SQL placeholders with actual values.
	 *
	 * @param string $sql SQL with placeholders.
	 * @return string
	 */
	protected function replace_sql_placeholders( string $sql ): string {
		$sql = str_replace( '{table_name}', $this->get_table_name(), $sql );

		return str_replace( '{table_prefix}', $this->connection->get_wpdb()->prefix, $sql );
	}

	/**
	 * Delete entities matching criteria without loading them.
	 *
	 * @param array $criteria WHERE conditions.
	 * @return int Number of deleted rows.
	 * @throws RepositoryException On database error.
	 */
	public function delete_by( array $criteria ): int {
		$table = $this->get_table_name_without_prefix();
		return $this->connection->delete( $table, $criteria );
	}

	/**
	 * Update entities matching criteria without loading them.
	 *
	 * @param array $data Data to update.
	 * @param array $criteria WHERE conditions.
	 * @return int Number of updated rows.
	 * @throws RepositoryException On database error.
	 */
	public function update_by( array $data, array $criteria ): int {
		$table = $this->get_table_name_without_prefix();
		return $this->connection->update( $table, $data, $criteria );
	}

	/**
	 * Find entities by criteria with specific columns.
	 *
	 * @param array    $criteria Criteria.
	 * @param string[] $columns  Columns to select.
	 * @param int|null $limit    Limit.
	 * @return array Array of stdClass (not entities).
	 * @throws RepositoryException On database error.
	 */
	public function select_columns( array $criteria, array $columns, ?int $limit = null ): array {
		CacheHelper::disableWPCache();
		$table       = $this->get_table_name();
		$columns_str = implode(
			', ',
			array_map(
				function ( $col ) {
					return "`{$col}`";
				},
				$columns
			)
		);

		$where = $this->build_where_clause( $criteria );
		$sql   = "SELECT {$columns_str} FROM {$table} WHERE {$where}";

		if ( null !== $limit ) {
			$sql .= $this->connection->prepare( ' LIMIT %d', $limit );
		}

		return $this->connection->select( $sql );
	}
}
