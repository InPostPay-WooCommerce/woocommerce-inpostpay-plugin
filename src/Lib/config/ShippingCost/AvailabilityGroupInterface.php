<?php
/**
 * Availability group interface.
 *
 * @package Ilabs\Inpost_Pay\Lib\config\ShippingCost
 */

declare( strict_types=1 );

namespace Ilabs\Inpost_Pay\Lib\config\ShippingCost;

use Ilabs\Inpost_Pay\Lib\form\AbstractOption;

/**
 * Interface AvailabilityGroupInterface
 *
 * Defines the contract for shipping groups with availability day/hour window settings.
 */
interface AvailabilityGroupInterface {

	/**
	 * Returns the available-from day field.
	 *
	 * @return AbstractOption
	 */
	public function get_available_from_day_field(): AbstractOption;

	/**
	 * Returns the available-from hour field.
	 *
	 * @return AbstractOption
	 */
	public function get_available_from_hour_field(): AbstractOption;

	/**
	 * Returns the available-to day field.
	 *
	 * @return AbstractOption
	 */
	public function get_available_to_day_field(): AbstractOption;

	/**
	 * Returns the available-to hour field.
	 *
	 * @return AbstractOption
	 */
	public function get_available_to_hour_field(): AbstractOption;
}
