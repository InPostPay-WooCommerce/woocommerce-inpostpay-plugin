<?php
/**
 * Service container for dependency management.
 *
 * @package Ilabs\Inpost_Pay\Container
 */

declare( strict_types=1 );

namespace Ilabs\Inpost_Pay\Container;

use Ilabs\Inpost_Pay\Container\Provider\EntityLayerServiceProvider;
use Ilabs\Inpost_Pay\Container\Provider\PluginServiceProvider;
use Isolated\Inpost_Pay\Isolated_Pimple\Pimple\Container;

/**
 * Class ServiceContainer
 *
 * Provides singleton access to the plugin service container.
 */
class ServiceContainer extends Container {
	/**
	 * Retrieve a service from the container.
	 *
	 * @param string $id Service identifier.
	 *
	 * @return mixed
	 */
	public function get( string $id ) {
		return $this->offsetGet( $id );
	}

	/**
	 * Register a service in the container.
	 *
	 * @param string $id Service identifier.
	 * @param mixed  $value Service value or factory.
	 *
	 * @return void
	 */
	public function set( string $id, $value ): void {
		$this->offsetSet( $id, $value );
	}

	/**
	 * Check if a service exists in the container.
	 *
	 * @param string $id Service identifier.
	 *
	 * @return bool
	 */
	public function has( string $id ): bool {
		return $this->offsetExists( $id );
	}

	/**
	 * Remove a service from the container.
	 *
	 * @param string $id Service identifier.
	 *
	 * @return void
	 */
	public function remove( string $id ): void {
		if ( $this->offsetExists( $id ) ) {
			$this->offsetUnset( $id );
		}
	}

	/**
	 * Initializes core default services.
	 *
	 * @param array $config Plugin configuration.
	 * @param array $headers Plugin headers.
	 *
	 * @return void
	 */
	public function initialize_defaults( array $config, array $headers ): void {
		$this->set( 'config', $config );
		$this->set( 'headers', $headers );

		( new EntityLayerServiceProvider() )::register( $this );
		( new PluginServiceProvider() )::register( $this );
	}
}
