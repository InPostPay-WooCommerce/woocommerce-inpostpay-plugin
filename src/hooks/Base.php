<?php
/**
 * Base Hook Class.
 *
 * @package InpostPay
 * @subpackage Hooks
 */

namespace Ilabs\Inpost_Pay\hooks;

/**
 * Class Base
 *
 * Base class for all hooks.
 */
abstract class Base {

	/**
	 * Attach hooks.
	 *
	 * @return void
	 */
	abstract public function attach_hook();
}
