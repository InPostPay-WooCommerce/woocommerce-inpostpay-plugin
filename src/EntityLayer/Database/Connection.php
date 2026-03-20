<?php
/**
 * Database connection wrapper over WordPress $wpdb.
 *
 * @package Ilabs\Inpost_Pay\EntityLayer\Database
 */

declare( strict_types=1 );

namespace Ilabs\Inpost_Pay\EntityLayer\Database;

use Ilabs\Inpost_Pay\Exception\RepositoryException;
use wpdb;

/**
 * Wrapper around WordPress $wpdb providing transactions and query helpers.
 */
class Connection {

	public const SERVICE_KEY = 'entity_layer.database.connection';

	/**
	 * WordPress database instance.
	 *
	 * @var wpdb
	 */
	private wpdb $wpdb;

	/**
	 * Table prefix (e.g., 'wp_' or 'wp_izi_').
	 *
	 * @var string
	 */
	private string $prefix;

	/**
	 * Transaction nesting level.
	 *
	 * @var int
	 */
	private int $transaction_level = 0;

	/**
	 * Constructor.
	 *
	 * @param wpdb        $wpdb WordPress database instance.
	 * @param string|null $prefix Optional custom table prefix. If null, uses $wpdb->prefix.
	 */
	public function __construct( wpdb $wpdb, ?string $prefix = null ) {
		$this->wpdb   = $wpdb;
		$this->prefix = $prefix ?? $wpdb->prefix;
	}

	/**
	 * Returns the full table name with prefix.
	 *
	 * @param string $table_name Table name without prefix.
	 *
	 * @return string
	 */
	public function get_table_name( string $table_name ): string {
		return $this->prefix . $table_name;
	}

	/**
	 * Execute a raw SQL query.
	 *
	 * @param string $sql SQL query.
	 *
	 * @return int|bool Number of rows affected or false on error.
	 *
	 * @throws RepositoryException When a database error occurs.
	 */
	public function query( string $sql ) {
		$result = $this->wpdb->query( $sql );

		if ( false === $result && ! empty( $this->wpdb->last_error ) ) {
			throw new RepositoryException(
				sprintf( 'Database query failed: %s. SQL: %s', $this->wpdb->last_error, $sql )
			);
		}

		return $result;
	}
	/**
	 * Execute SELECT query and return results as objects.
	 *
	 * @param string $sql SQL query.
	 *
	 * @return array Array of stdClass objects.
	 *
	 * @throws RepositoryException When a database error occurs.
	 */
	public function select( string $sql ): array {
		$results = $this->wpdb->get_results( $sql, OBJECT );

		if ( ! empty( $this->wpdb->last_error ) ) {
			throw new RepositoryException(
				sprintf( 'SELECT query failed: %s. SQL: %s', $this->wpdb->last_error, $sql )
			);
		}

		return $results ?? array();
	}

	/**
	 * Execute SELECT query and return single row.
	 *
	 * @param string $sql SQL query.
	 *
	 * @return object|null stdClass object or null if not found.
	 *
	 * @throws RepositoryException When a database error occurs.
	 */
	public function select_one( string $sql ): ?object {
		$result = $this->wpdb->get_row( $sql, OBJECT );

		if ( ! empty( $this->wpdb->last_error ) ) {
			throw new RepositoryException(
				sprintf( 'SELECT query failed: %s. SQL: %s', $this->wpdb->last_error, $sql )
			);
		}

		return $result ?: null;
	}

	/**
	 * Insert a row into the database.
	 *
	 * @param string $table Table name (without prefix).
	 * @param array  $data  Associative array of column => value.
	 *
	 * @return int Insert ID.
	 *
	 * @throws RepositoryException When a database error occurs.
	 */
	public function insert( string $table, array $data ): int {
		$table_name = $this->get_table_name( $table );
		$result     = $this->wpdb->insert( $table_name, $data );

		if ( false === $result ) {
			throw new RepositoryException(
				sprintf( 'INSERT failed: %s. Table: %s', $this->wpdb->last_error, $table_name )
			);
		}

		return (int) $this->wpdb->insert_id;
	}

	/**
	 * Update rows in the database.
	 *
	 * @param string $table Table name (without prefix).
	 * @param array  $data  Data to update (column => value).
	 * @param array  $where WHERE conditions (column => value).
	 *
	 * @return int Number of rows affected.
	 *
	 * @throws RepositoryException When a database error occurs.
	 */
	public function update( string $table, array $data, array $where ): int {
		$table_name = $this->get_table_name( $table );
		$result     = $this->wpdb->update( $table_name, $data, $where );

		if ( false === $result ) {
			throw new RepositoryException(
				sprintf( 'UPDATE failed: %s. Table: %s', $this->wpdb->last_error, $table_name )
			);
		}

		return (int) $result;
	}

	/**
	 * Delete rows from the database.
	 *
	 * @param string $table Table name (without prefix).
	 * @param array  $where WHERE conditions (column => value).
	 *
	 * @return int Number of rows deleted.
	 *
	 * @throws RepositoryException When a database error occurs.
	 */
	public function delete( string $table, array $where ): int {
		$table_name = $this->get_table_name( $table );
		$result     = $this->wpdb->delete( $table_name, $where );

		if ( false === $result ) {
			throw new RepositoryException(
				sprintf( 'DELETE failed: %s. Table: %s', $this->wpdb->last_error, $table_name )
			);
		}

		return (int) $result;
	}

	/**
	 * Prepare SQL statement with parameters.
	 *
	 * @param string $query Query with placeholders (%s, %d, %f).
	 * @param mixed  ...$args Values to bind.
	 *
	 * @return string Prepared SQL.
	 */
	public function prepare( string $query, ...$args ): string {
		return $this->wpdb->prepare( $query, ...$args );
	}

	/**
	 * Start a database transaction.
	 *
	 * @return void
	 *
	 * @throws RepositoryException If the transaction fails.
	 */
	public function begin_transaction(): void {
		if ( 0 === $this->transaction_level ) {
			$this->query( 'START TRANSACTION' );
		}

		++$this->transaction_level;
	}

	/**
	 * Commit the current transaction.
	 *
	 * @return void
	 *
	 * @throws RepositoryException If commit fails or no transaction is active.
	 */
	public function commit(): void {
		if ( 0 === $this->transaction_level ) {
			throw new RepositoryException( 'Cannot commit: no active transaction.' );
		}

		--$this->transaction_level;

		if ( 0 === $this->transaction_level ) {
			$this->query( 'COMMIT' );
		}
	}

	/**
	 * Rollback the current transaction.
	 *
	 * @return void
	 *
	 * @throws RepositoryException If rollback fails or no transaction is active.
	 */
	public function rollback(): void {
		if ( 0 === $this->transaction_level ) {
			throw new RepositoryException( 'Cannot rollback: no active transaction.' );
		}

		$this->transaction_level = 0;
		$this->query( 'ROLLBACK' );
	}

	/**
	 * Get the last insert ID.
	 *
	 * @return int
	 */
	public function get_insert_id(): int {
		return (int) $this->wpdb->insert_id;
	}

	/**
	 * Get the underlying wpdb instance.
	 *
	 * @return wpdb
	 */
	public function get_wpdb(): wpdb {
		return $this->wpdb;
	}

	/**
	 * Check if transaction is active.
	 *
	 * @return bool
	 */
	public function is_transaction_active(): bool {
		return ( 0 < $this->transaction_level );
	}

	/**
	 * Execute callback within transaction.
	 *
	 * @param callable $callback Callback to execute.
	 *
	 * @return mixed Result of callback.
	 *
	 * @throws RepositoryException If transaction fails.
	 */
	public function transaction( callable $callback ) {
		$this->begin_transaction();

		try {
			$result = $callback();
			$this->commit();

			return $result;
		} catch ( \Throwable $e ) {
			$this->rollback();
			throw new RepositoryException(
				'Transaction failed: ' . $e->getMessage(),
				0,
				$e
			);
		}
	}

	/**
	 * Execute SELECT query and return results.
	 *
	 * @param string $sql         SQL query.
	 * @param string $output_type Output type (OBJECT, ARRAY_A, ARRAY_N).
	 *
	 * @return array Array of results.
	 *
	 * @throws RepositoryException When a database error occurs.
	 */
	public function get_results( string $sql, $output_type = OBJECT ): array {
		$results = $this->wpdb->get_results( $sql, $output_type );

		if ( ! empty( $this->wpdb->last_error ) ) {
			throw new RepositoryException(
				sprintf( 'SELECT query failed: %s. SQL: %s', $this->wpdb->last_error, $sql )
			);
		}

		return $results ?? array();
	}
}
