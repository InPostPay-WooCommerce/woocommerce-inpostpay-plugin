<?php

namespace Ilabs\Inpost_Pay;

use Ilabs\Inpost_Pay\Container\ServiceContainer;
use Ilabs\Inpost_Pay\Exception\MissingServiceContainerException;

if ( ! function_exists( 'inpost_pay' ) ) {
	/**
	 * Returns a new instance of the main Plugin class.
	 *
	 * @return Plugin
	 */
	function inpost_pay(): Plugin {
		return new Plugin();
	}
}

if ( ! function_exists( 'inpost_pay_container' ) ) {
	/**
	 * Global accessor for the InPost Pay DI container.
	 *
	 * @param ServiceContainer|null $set Optional: set container instance.
	 * @param bool                  $throw_if_missing Whether to throw exception if container is not set.
	 *
	 * @return ServiceContainer The service container instance.
	 * @throws MissingServiceContainerException When container is not set and $throw_if_missing is true.
	 */
	function inpost_pay_container( ?ServiceContainer $set = null, bool $throw_if_missing = true ): ServiceContainer {
		static $container = null;

		if ( $set instanceof ServiceContainer ) {
			$container = $set;
		}

		if ( null === $container && $throw_if_missing ) {
			throw new MissingServiceContainerException(
				'Service Container does not exists. Please check your config and Service Container registration.'
			);
		}

		return $container;
	}
}
