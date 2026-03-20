<?php

namespace Ilabs\Inpost_Pay\Lib\form;

use Ilabs\Inpost_Pay\Lib\form\exception\NotAllowedConfigOptionException;
use Ilabs\Inpost_Pay\Lib\form\exception\NotFoundConfigOptionException;
use Ilabs\Inpost_Pay\Lib\form\exception\RequiredConfigOptionException;

abstract class AbstractConfig {
	private $config = [];

	public function __construct( $config ) {
		$this->config = $this->prepare_config( $config );
	}

	public final function prepare_config( $config ) {
		foreach ( $config as $key => $value ) {
			if (is_array($value)) {
				$config[ $key ] = new ConfigOption(
					$key,
					$value['required'],
					( $value['default'] ) ?? null
				);
			} else {
				$config[ $key ] = new ConfigOption(
					$key
				);
			}
		}

		return $config;
	}

	/**
	 * @return mixed
	 */
	public final function get_config() {
		return $this->config;
	}

	/**
	 * @throws NotFoundConfigOptionException
	 */
	public final function get_config_option( string $name ) {
		if ( $this->has_config_option( $name ) ) {
			return $this->config[ $name ];
		}
		throw new NotFoundConfigOptionException( $name );
	}

	/**
	 * @throws NotAllowedConfigOptionException
	 * @throws RequiredConfigOptionException
	 */
	public final function set_config( $configOptions ) {
		foreach ( $configOptions as $key => $value ) {
			if ( ! isset( $this->config[ $key ] ) ) {
				throw new NotAllowedConfigOptionException( $key );
			}
			if ( $this->config[ $key ]->is_required() && empty( $value ) ) {
				throw new RequiredConfigOptionException( $key );
			} else {
				$this->config[ $key ]->set_value( $value );
			}
			$this->config[ $key ]->set_value( $value );
		}

		$configDiff = array_diff_key( $configOptions, $this->config );
		if ( ! empty( $configDiff ) ) {
			foreach ( $configDiff as $key => $value ) {
				if ( in_array( $key, $this->config ) && $this->config[ $key ]->is_required() ) {
					throw new RequiredConfigOptionException( $key );
				}
			}
		}
	}

	public final function has_config_option( string $name ): bool {
		return isset( $this->config[ $name ] ) && $this->config[ $name ] instanceof ConfigOption;
	}
}
