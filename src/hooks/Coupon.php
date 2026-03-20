<?php
/**
 * Coupon Hook.
 *
 * @package InpostPay
 * @subpackage Hooks
 */

namespace Ilabs\Inpost_Pay\hooks;

/**
 * Class Coupon
 *
 * Handles coupon functionality.
 */
class Coupon extends Base {

	/**
	 * Attach hooks.
	 *
	 * @return void
	 */
	public function attach_hook() {
		( new \Ilabs\Inpost_Pay\Lib\Coupons\Coupon() )->hooks();
	}
}
