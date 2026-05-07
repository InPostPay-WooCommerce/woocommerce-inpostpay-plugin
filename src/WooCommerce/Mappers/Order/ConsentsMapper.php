<?php

namespace Ilabs\Inpost_Pay\WooCommerce\Mappers\Order;

use Ilabs\Inpost_Pay\Lib\helpers\HPOSHelper;
use Ilabs\Inpost_Pay\Lib\item\order\Consent;

class ConsentsMapper {
	private $HPOSHelper;

	public function __construct( HPOSHelper $HPOSHelper ) {
		$this->HPOSHelper = $HPOSHelper;
	}

	public function map(): array {
		return array();
	}
}
