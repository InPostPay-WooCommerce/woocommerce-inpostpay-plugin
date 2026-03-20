<?php
/**
 * Cron handler for InPost Pay plugin.
 *
 * This file contains the Cron class responsible for managing scheduled tasks
 * such as cleaning up expired cart sessions and updating inactive hot products.
 *
 * @package Ilabs\Inpost_Pay\Lib
 */

declare( strict_types=1 );

namespace Ilabs\Inpost_Pay\Lib;

use Ilabs\Inpost_Pay\EntityLayer\Cache\PersistentCache;
use Ilabs\Inpost_Pay\EntityLayer\Repository\CartSessionRepository;
use Ilabs\Inpost_Pay\Lib\config\product\InactiveHotProductsConfig;
use Ilabs\Inpost_Pay\Lib\Product\HotProduct;
use Ilabs\Inpost_Pay\Logger;
use function Ilabs\Inpost_Pay\inpost_pay_container;

/**
 * Cron class for handling scheduled tasks in the WordPress environment.
 */
class Cron {

	public const JOB_NAME = 'inpost_pay_cron';

	private CartSessionRepository $cart_session_repository;

	/**
	 * Constructor for the class.
	 */
	public function __construct() {
		$container                     = inpost_pay_container();
		$this->cart_session_repository = $container->get( CartSessionRepository::SERVICE_KEY );
	}

	/**
	 *
	 * Attaches a custom action to WordPress hooks system.
	 * This method registers the specified callback with a specific hook name defined by the class constant JOB_NAME.
	 *
	 * @return void
	 */
	public function attach_hook(): void {
		add_action( self::JOB_NAME, array( $this, 'run' ) );
	}

	/**
	 * Executes necessary operations for processing and updating cart sessions and hot products.
	 *
	 * @return void
	 */
	public function run(): void {
		$this->delete_expired_cart_sessions();
		$this->get_inactive_hot_products();
		$this->delete_expired_persistent_cache();
	}

	/**
	 * Removes expired cart sessions using a time-budgeted batch loop.
	 *
	 * Instead of guessing a "safe" row count for all environments, the loop runs
	 * small fixed-size batches (BATCH_SIZE) and stops when either:
	 * - a batch returns fewer rows than requested (nothing left to delete), or
	 * - the elapsed wall-clock time exceeds the allowed budget.
	 *
	 * Normal maintenance (no backlog flag) uses a 0.5 s budget — enough to drain
	 * a handful of naturally-expired rows without touching DB for long.
	 * Backlog cleanup (flag set by migration) uses a 2 s budget so it makes
	 * meaningful progress each run on any server, regardless of hosting tier.
	 *
	 * When the last batch is smaller than BATCH_SIZE the backlog is exhausted:
	 * the flag is cleared and subsequent runs revert to the short budget.
	 *
	 * @return void
	 */
	private function delete_expired_cart_sessions(): void {
		$batch_size     = 250;
		$cleanup_needed = (bool) get_option('izi_session_cleanup_needed');
		$time_budget    = $cleanup_needed ? 2.0 : 0.5;
		$max_loops      = $cleanup_needed ? 20 : 5;

		$total_deleted = 0;
		$start         = microtime(true);
		$loops         = 0;

		do {
			$deleted = $this->cart_session_repository->delete_expired_sessions($batch_size);
			$total_deleted += $deleted;
			$loops++;

			if ($deleted < $batch_size) {
				if ($cleanup_needed) {
					delete_option('izi_session_cleanup_needed');
					Logger::log('[Cron] Expired session backlog cleared');
				}
				break;
			}
		} while (
			$loops < $max_loops &&
			(microtime(true) - $start) < $time_budget
		);

		if ( $total_deleted > 0 ) {
			Logger::log( '[Cron] Cleanup mode: ' . ( $cleanup_needed ? 'backlog' : 'normal' ) . ", deleted: {$total_deleted}" );
		}
	}

	/**
	 * Retrieves and updates inactive hot products in the system.
	 *
	 * @return void
	 */
	private function get_inactive_hot_products(): void {
		$inactive_product_ids = ( new HotProduct() )->getInactiveProductIds();

		( new InactiveHotProductsConfig() )->update( $inactive_product_ids );
	}

	/**
	 * Deletes all expired entries from the persistent cache.
	 *
	 * This method creates a new instance of PersistentCache and clears its expired entries.
	 * The operation is performed to maintain cache consistency by removing outdated data.
	 */
	private function delete_expired_persistent_cache(): void {
		( new PersistentCache() )->clear_expired();
	}

	/**
	 * Schedules a job to run hourly.
	 *
	 * @return void
	 */
	public function schedule(): void {
		if ( ! wp_next_scheduled( self::JOB_NAME ) ) {
			wp_schedule_event( time(), 'hourly', self::JOB_NAME );
		}
	}

	/**
	 * Deactivates a scheduled event for updating cart sessions and hot products by removing the scheduled job from the WordPress event queue.
	 *
	 * @return void
	 */
	public function deactivate(): void {
		wp_clear_scheduled_hook( self::JOB_NAME );
	}
}
