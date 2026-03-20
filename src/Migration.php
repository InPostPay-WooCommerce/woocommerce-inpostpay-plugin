<?php

namespace Ilabs\Inpost_Pay;

/**
 * Migration class for handling database schema updates and version management.
 *
 * This class manages the creation and updates of database tables required
 * for the Inpost Pay plugin functionality.
 */
class Migration {
	/**
	 * Current migration version.
	 *
	 * @var string
	 */
	private const version = '2.0.2';

	/**
	 * Database table schemas.
	 *
	 * @var array
	 */
	private array $schemas;

	/**
	 * Migration constructor.
	 *
	 * Initializes the database schemas for all required tables.
	 */
	public function __construct() {
		$this->schemas = [
			'cart_session' => "CREATE TABLE {tableName} (
                id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                session_id TEXT,
                confirmation_response MEDIUMTEXT,
                cart_id VARCHAR(255),
                order_id INTEGER,
                redirect_url VARCHAR(255),
                basket_cache MEDIUMTEXT,
                basket_cached MEDIUMTEXT,
				basket_delivery_cache MEDIUMTEXT,
                coupons MEDIUMTEXT,
                redirected SMALLINT(1) DEFAULT 0,
                wc_cart_session VARCHAR(255),
                session_expiry BIGINT(20) UNSIGNED DEFAULT 0,
                INDEX idx_session_expiry (session_expiry),
				izi_basket VARCHAR(60) DEFAULT NULL,
				basket_binding_api_key VARCHAR(255) DEFAULT NULL,
				analytics MEDIUMTEXT DEFAULT NULL,
				action_type VARCHAR(255) DEFAULT NULL,
                PRIMARY KEY  (id)
                ) {charset};",

			'order_aliases' => "CREATE TABLE {tableName} (
				alias_order_id VARCHAR(64) NOT NULL PRIMARY KEY,
				order_id BIGINT UNSIGNED NOT NULL,
				created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
				) {charset};",

			'unavailable' => "CREATE TABLE {tableName} (
				id mediumint(9) NOT NULL AUTO_INCREMENT PRIMARY KEY,
				category_id BIGINT UNSIGNED DEFAULT NULL,
				product_id BIGINT UNSIGNED DEFAULT NULL,
				delivery_type SMALLINT UNSIGNED NOT NULL,
				INDEX idx_product_id (product_id),
				INDEX idx_category_id (category_id),
				INDEX idx_category_null_product (category_id, product_id)
				) {charset};",

			'basket_bindings' => 'CREATE TABLE {tableName} (
			    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
			    basket_id VARCHAR(60) NOT NULL UNIQUE,
			    basket_binding_api_key VARCHAR(255) NOT NULL UNIQUE,
			    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
			    updated_at DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
			    UNIQUE INDEX idx_basket_id (basket_id),
			    UNIQUE INDEX idx_api_key (basket_binding_api_key)
			) {charset};'
		];
	}

	/**
	 * Run the migration process.
	 *
	 * Checks the current database version and applies any necessary updates.
	 */
	public function run(): void {
		global $wpdb;

		$charset         = $wpdb->get_charset_collate();
		$current_version = get_option( 'izi-db-version' );

		if ( self::version === $current_version ) {
			return;
		}

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

		foreach ( $this->schemas as $schemaName => $schema ) {
			$tableName = $wpdb->prefix . 'izi_' . $schemaName;
			$sql       = str_replace( array( '{charset}', '{tableName}' ), array( $charset, $tableName ), $schema );
			dbDelta( $sql );
		}

		if ( $current_version && version_compare( $current_version, '1.3.0', '<=' ) ) {
			$this->add_unavailable_indexes();
		}

		if ( $current_version && version_compare( $current_version, '2.0.0', '<=' ) ) {
			$this->upgrade_cart_session_table();
		}

		if ( $current_version && version_compare( $current_version, '2.0.1', '<=' ) ) {
			$this->add_session_expiry_index();
			$this->schedule_expired_session_cleanup();
		}

		update_option( 'izi-db-version', self::version );
	}

	/**
	 * Upgrade the cart_session table structure.
	 *
	 * Modifies column types and converts character set for the cart session table.
	 *
	 * @return void
	 */
	private function upgrade_cart_session_table(): void {
		global $wpdb;

		$table = $wpdb->prefix . 'izi_cart_session';

		$table_exists = $wpdb->get_var(
			$wpdb->prepare( 'SHOW TABLES LIKE %s', $table )
		);

		if ( ! $table_exists ) {
			return;
		}

		$column = $wpdb->get_row( "SHOW COLUMNS FROM {$table} LIKE 'id'", ARRAY_A );

		if ( $column && isset( $column['Type'] ) ) {
			$type = strtolower( (string) $column['Type'] );

			if ( false === strpos( $type, 'bigint' ) ) {
				$wpdb->query( "ALTER TABLE {$table} MODIFY id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT" );
			}
		}

		$wpdb->query( "ALTER TABLE {$table} MODIFY basket_cache MEDIUMTEXT NULL" );
		$wpdb->query( "ALTER TABLE {$table} MODIFY basket_cached MEDIUMTEXT NULL" );
		$wpdb->query( "ALTER TABLE {$table} MODIFY basket_delivery_cache MEDIUMTEXT NULL" );
		$wpdb->query( "ALTER TABLE {$table} MODIFY confirmation_response MEDIUMTEXT NULL" );
		$wpdb->query( "ALTER TABLE {$table} MODIFY analytics MEDIUMTEXT NULL" );
		$wpdb->query( "ALTER TABLE {$table} MODIFY coupons MEDIUMTEXT NULL" );
		$wpdb->query( "ALTER TABLE {$table} CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci" );
	}

	/**
	 * Add index on session_expiry to the cart_session table.
	 *
	 * Speeds up the batch DELETE used by the cron cleanup job.
	 *
	 * @return void
	 */
	private function add_session_expiry_index(): void {
		global $wpdb;

		$table = $wpdb->prefix . 'izi_cart_session';

		$table_exists = $wpdb->get_var(
			$wpdb->prepare( 'SHOW TABLES LIKE %s', $table )
		);

		if ( ! $table_exists ) {
			return;
		}

		$indexes          = $wpdb->get_results( "SHOW INDEX FROM {$table}", ARRAY_A );
		$existing_indexes = array_column( $indexes, 'Key_name' );

		if ( ! in_array( 'idx_session_expiry', $existing_indexes, true ) ) {
			$wpdb->query( "ALTER TABLE {$table} ADD INDEX idx_session_expiry (session_expiry)" );
		}
	}

	/**
	 * Flag the plugin to perform a gradual cleanup of expired cart sessions.
	 *
	 * Called when upgrading from a version that may have accumulated a large
	 * backlog of expired sessions. The cron job reads this flag and uses a
	 * higher batch size until the backlog is gone, then clears the flag.
	 *
	 * @return void
	 */
	private function schedule_expired_session_cleanup(): void {
		global $wpdb;

		$table = $wpdb->prefix . 'izi_cart_session';

		$table_exists = $wpdb->get_var(
			$wpdb->prepare( 'SHOW TABLES LIKE %s', $table )
		);

		if ( ! $table_exists ) {
			return;
		}

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$has_expired = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT 1 FROM {$table} WHERE session_expiry > 0 AND session_expiry < %d LIMIT 1",
				time()
			)
		);

		if ( null !== $has_expired ) {
			update_option( 'izi_session_cleanup_needed', '1', false );
		}
	}

	/**
	 * Add indexes to the unavailable table.
	 *
	 * Creates performance indexes for the unavailable table if they don't exist.
	 *
	 * @return void
	 */
	private function add_unavailable_indexes(): void {
		global $wpdb;

		$table_name = $wpdb->prefix . 'izi_unavailable';

		$table_exists = $wpdb->get_var( $wpdb->prepare(
			'SHOW TABLES LIKE %s',
			$table_name
		) );

		if ( ! $table_exists ) {
			Logger::log( '[MIGRATION] Table ' . $table_name . ' does not exist - skipping index creation' );
			return;
		}

		$indexes          = $wpdb->get_results( "SHOW INDEX FROM {$table_name}", ARRAY_A );
		$existing_indexes = array_column( $indexes, 'Key_name' );

		if ( ! in_array( 'idx_product_id', $existing_indexes, true ) ) {
			$wpdb->query( "ALTER TABLE {$table_name} ADD INDEX idx_product_id (product_id)" );
		}

		if ( ! in_array( 'idx_category_id', $existing_indexes, true ) ) {
			$wpdb->query( "ALTER TABLE {$table_name} ADD INDEX idx_category_id (category_id)" );
		}

		if ( ! in_array( 'idx_category_null_product', $existing_indexes, true ) ) {
			$wpdb->query( "ALTER TABLE {$table_name} ADD INDEX idx_category_null_product (category_id, product_id)" );
		}
	}
}
