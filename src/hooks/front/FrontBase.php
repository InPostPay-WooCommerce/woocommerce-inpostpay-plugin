<?php
/**
 * Front Base Hook Class.
 *
 * @package InpostPay
 * @subpackage Hooks/Front
 */

namespace Ilabs\Inpost_Pay\hooks\front;

use Ilabs\Inpost_Pay\hooks\Base;

/**
 * Class FrontBase
 *
 * Base class for all frontend hooks.
 */
abstract class FrontBase extends Base {

	/**
	 * Attach hooks.
	 *
	 * @return void
	 */
	abstract public function attach_hook();

	/**
	 * Attach frontend hook.
	 *
	 * @return void
	 */
	public function attach_frontend_hook(): void {
		if ( did_action( 'shutdown' ) ) {
			return;
		}

		if ( is_admin() ) {
			return;
		}

		if ( wp_doing_cron() ) {
			return;
		}

		$this->attach_hook();
	}
}
