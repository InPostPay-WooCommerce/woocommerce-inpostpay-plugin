<?php
/**
 * Base admin REST routes handler for the InPost Pay plugin.
 *
 * Provides common registration logic for admin-only REST endpoints,
 * including route definition bootstrap, permission checks, and
 * cache-control handling for registered callbacks.
 *
 * @package Ilabs\Inpost_Pay
 */

namespace Ilabs\Inpost_Pay\rest;

use Closure;
use Ilabs\Inpost_Pay\Lib\helpers\LSCacheHelper;

/**
 * Base class for admin REST endpoint registrars.
 */
abstract class Admin {

	/**
	 * POST routes map.
	 *
	 * @var array<string, Closure>
	 */
	protected array $post = array();

	/**
	 * GET routes map.
	 *
	 * @var array<string, Closure>
	 */
	protected array $get = array();

	/**
	 * DELETE routes map.
	 *
	 * @var array<string, Closure>
	 */
	protected array $delete = array();

	/**
	 * Determines whether access to the routes should be restricted.
	 *
	 * @var bool
	 */
	protected bool $restricted = false;

	/**
	 * Describe and register route definitions.
	 *
	 * @return void
	 */
	abstract protected function describe(): void;

	/**
	 * Register REST routes.
	 *
	 * @return void
	 */
	public function register(): void {
		if ( $this->restricted && ! $this->can_access() ) {
			return;
		}

		$this->describe();

		add_action(
			'rest_api_init',
			function ( $server ): void {
				foreach ( $this->post as $path => $function ) {
					$server->register_route(
						'inpost',
						$path,
						array(
							'methods'             => 'POST',
							'callback'            => function ( $request ) use ( $function ) {
								$this->allow_origin_header();
								LSCacheHelper::no_cache();

								return $function( $request );
							},
							'permission_callback' => fn() => $this->can_access(),
						)
					);
				}

				foreach ( $this->get as $path => $function ) {
					$server->register_route(
						'inpost',
						$path,
						array(
							'methods'             => 'GET',
							'callback'            => function ( $request ) use ( $function ) {
								$this->allow_origin_header();
								LSCacheHelper::no_cache();

								return $function( $request );
							},
							'permission_callback' => fn() => $this->can_access(),
						)
					);
				}

				foreach ( $this->delete as $path => $function ) {
					$server->register_route(
						'inpost',
						$path,
						array(
							'methods'             => 'DELETE',
							'callback'            => function ( $request ) use ( $function ) {
								$this->allow_origin_header();
								LSCacheHelper::no_cache();

								return $function( $request );
							},
							'permission_callback' => fn() => $this->can_access(),
						)
					);
				}
			}
		);
	}

	/**
	 * Check if the current request is allowed access.
	 *
	 * @return bool
	 */
	private function can_access(): bool {
		return current_user_can( 'manage_options' );
	}

	/**
	 * Send allow-origin headers for admin requests.
	 *
	 * @return void
	 */
	private function allow_origin_header(): void {
	}
}
