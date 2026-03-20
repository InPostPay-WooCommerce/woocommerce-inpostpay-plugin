<?php
/**
 * Registers plugin-level services into the DI container.
 *
 * @package Ilabs\Inpost_Pay\Container\Provider
 */

declare( strict_types=1 );

namespace Ilabs\Inpost_Pay\Container\Provider;

use Ilabs\Inpost_Pay\Container\ServiceContainer;
use Ilabs\Inpost_Pay\Lib\Basket\BasketPutService;
use Ilabs\Inpost_Pay\Integration\Basket\Availability\UnavailabilityService;
use Ilabs\Inpost_Pay\models\CartSessionService;
use Ilabs\Inpost_Pay\Lib\helpers\WooProductHelper;

/**
 * Class PluginServiceProvider.
 *
 * Handles registration of core plugin services in the container.
 */
class PluginServiceProvider {

	/**
	 * Register plugin services in the container.
	 *
	 * @param ServiceContainer $container The service container instance.
	 *
	 * @return void
	 */
	public static function register( ServiceContainer $container ): void {
		$container->set(
			UnavailabilityService::SERVICE_KEY,
			new UnavailabilityService( $container )
		);

		$container->set(
			CartSessionService::SERVICE_KEY,
			new CartSessionService( $container )
		);

		$container->set(
			BasketPutService::SERVICE_KEY,
			new BasketPutService( $container->get( CartSessionService::SERVICE_KEY ) )
		);

		$container->set(
			WooProductHelper::SERVICE_KEY,
			new WooProductHelper()
		);
	}
}
