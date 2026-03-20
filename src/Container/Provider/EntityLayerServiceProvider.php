<?php
/**
 * Registers Entity Layer related services into the DI container.
 *
 * @package Ilabs\Inpost_Pay\Container\Provider
 */

declare( strict_types=1 );

namespace Ilabs\Inpost_Pay\Container\Provider;

use Ilabs\Inpost_Pay\Container\ServiceContainer;
use Ilabs\Inpost_Pay\EntityLayer\Cache\WordPressCache;
use Ilabs\Inpost_Pay\EntityLayer\Database\Connection;
use Ilabs\Inpost_Pay\EntityLayer\Repository\BasketBindingRepository;
use Ilabs\Inpost_Pay\EntityLayer\Repository\CartSessionRepository;
use Ilabs\Inpost_Pay\EntityLayer\Repository\CouponRepository;
use Ilabs\Inpost_Pay\EntityLayer\Repository\OrderAliasRepository;
use Ilabs\Inpost_Pay\EntityLayer\Repository\UnavailableRepository;
use Ilabs\Inpost_Pay\EntityLayer\Tracker\ChangeTracker;

/**
 * Class EntityLayerServiceProvider.
 *
 * Handles registration of entity layer services in the container.
 */
class EntityLayerServiceProvider {

	/**
	 * Register all entity layer services in the container.
	 *
	 * @param ServiceContainer $container The service container instance.
	 *
	 * @return void
	 */
	public static function register( ServiceContainer $container ): void {
		$container->set(
			Connection::SERVICE_KEY,
			function () {
				global $wpdb;
				return new Connection( $wpdb );
			}
		);

		$container->set(
			WordPressCache::SERVICE_KEY,
			function () {
				return new WordPressCache();
			}
		);

		$container->set(
			ChangeTracker::SERVICE_KEY,
			function () {
				return new ChangeTracker();
			}
		);

		$container->set(
			BasketBindingRepository::SERVICE_KEY,
			function ( $c ) {
				return new BasketBindingRepository(
					$c->get( Connection::SERVICE_KEY ),
					$c->get( ChangeTracker::SERVICE_KEY ),
					$c->get( WordPressCache::SERVICE_KEY )
				);
			}
		);

		$container->set(
			CartSessionRepository::SERVICE_KEY,
			function ( $c ) {
				return new CartSessionRepository(
					$c->get( Connection::SERVICE_KEY ),
					$c->get( ChangeTracker::SERVICE_KEY ),
					$c->get( WordPressCache::SERVICE_KEY )
				);
			}
		);

		$container->set(
			OrderAliasRepository::SERVICE_KEY,
			function ( $c ) {
				return new OrderAliasRepository(
					$c->get( Connection::SERVICE_KEY ),
					$c->get( ChangeTracker::SERVICE_KEY ),
					$c->get( WordPressCache::SERVICE_KEY )
				);
			}
		);

		$container->set(
			UnavailableRepository::SERVICE_KEY,
			function ( $c ) {
				return new UnavailableRepository(
					$c->get( Connection::SERVICE_KEY ),
					$c->get( ChangeTracker::SERVICE_KEY ),
					$c->get( WordPressCache::SERVICE_KEY )
				);
			}
		);

		$container->set(
			CouponRepository::SERVICE_KEY,
			function ( $c ) {
				return new CouponRepository(
					$c->get( Connection::SERVICE_KEY ),
					$c->get( ChangeTracker::SERVICE_KEY ),
					$c->get( WordPressCache::SERVICE_KEY )
				);
			}
		);
	}
}
