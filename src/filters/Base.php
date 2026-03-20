<?php
/**
 * Base filter class for InPost Pay plugin.
 *
 * @package    Inpost_Pay
 * @subpackage Filters
 * @since      2.0.1
 */

namespace Ilabs\Inpost_Pay\filters;

/**
 * Base class for filter registration.
 *
 * @package Ilabs\Inpost_Pay\filters
 * @since 2.0.1
 */
abstract class Base {
	/**
	 * Registers filters required by the plugin.
	 *
	 * This method is abstract and should be implemented by all filter classes.
	 *
	 * @since 2.0.1
	 *
	 * @abstract
	 * @return void
	 */
	abstract public function register_filters(): void;
}
