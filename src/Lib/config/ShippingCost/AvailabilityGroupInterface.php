<?php

namespace Ilabs\Inpost_Pay\Lib\config\ShippingCost;

use Ilabs\Inpost_Pay\Lib\form\AbstractOption;

interface AvailabilityGroupInterface {

	public function getAvailableFromDayField(): AbstractOption;

	public function getAvailableFromHourField(): AbstractOption;

	public function getAvailableToDayField(): AbstractOption;

	public function getAvailableToHourField(): AbstractOption;
}
