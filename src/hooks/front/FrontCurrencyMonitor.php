<?php
/**
 * Front Currency Monitor Hook.
 *
 * @package InpostPay
 * @subpackage Hooks/Front
 */

namespace Ilabs\Inpost_Pay\hooks\front;

use Ilabs\Inpost_Pay\Integration\Currency\CurrencyStateManager;

/**
 * Class FrontCurrencyMonitor
 *
 * Monitors currency changes on the frontend.
 */
class FrontCurrencyMonitor extends FrontBase {
	/**
	 * Attach hooks.
	 *
	 * @return void
	 */
	public function attach_hook() {
		add_action( 'init', array( CurrencyStateManager::class, 'handleCurrencyChange' ), 20 );
	}
}
