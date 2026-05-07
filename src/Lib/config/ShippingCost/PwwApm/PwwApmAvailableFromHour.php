<?php

namespace Ilabs\Inpost_Pay\Lib\config\ShippingCost\PwwApm;

use Ilabs\Inpost_Pay\Lib\config\ShippingCost\AbstractAvailabilityField;
use Ilabs\Inpost_Pay\Lib\config\ShippingCost\ApmMethodGroupField;
use Ilabs\Inpost_Pay\Lib\config\ShippingCost\GroupInterface;
use Ilabs\Inpost_Pay\Lib\config\ShippingCost\ShippingMappingFieldInterface;
use Ilabs\Inpost_Pay\Lib\form\exception\OptionNameRequired;
use Ilabs\Inpost_Pay\Lib\form\LegacyOptionInterface;

final class PwwApmAvailableFromHour extends AbstractAvailabilityField implements LegacyOptionInterface, ApmMethodGroupField {

	/**
	 * @throws OptionNameRequired
	 */
	public static function instance( ?int $zone_id = null ): self {
		return new self( $zone_id );
	}

	public function getDeliveryOptionCode(): string {
		return GroupInterface::DELIVERY_OPTION_CODE_PWW;
	}

	public function __construct( ?int $zone_id = null ) {

		parent::__construct( 'izi_shipping_available_from_hour_pww_apm', $zone_id );
	}

	public function init(): void {
		parent::register();
	}

	public function get_label(): string {
		return __(
			'Prices and courier shipping availability map with:',
			'inpost-pay'
		);
	}

	public function get_tooltip(): string {
		return __(
			'Determines which shipping method is to be associated',
			'inpost-pay'
		);
	}

	public function get_legacy_option_id(): string {
		return 'izi_transport_available_from_hour_pww_apm';
	}

	public function has_legacy_option_priority(): bool {
		return true;
	}
}
