<?php

namespace Ilabs\Inpost_Pay\Lib\config\ShippingCost\PwwApm;


use Ilabs\Inpost_Pay\Lib\config\ShippingCost\AbstractShippingMethodField;
use Ilabs\Inpost_Pay\Lib\config\ShippingCost\ApmMethodGroupField;
use Ilabs\Inpost_Pay\Lib\config\ShippingCost\GroupInterface;
use Ilabs\Inpost_Pay\Lib\config\ShippingCost\ShippingMappingFieldInterface;
use Ilabs\Inpost_Pay\Lib\form\exception\OptionNameRequired;

final class PwwApmShippingMethod extends AbstractShippingMethodField implements ApmMethodGroupField {

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

		parent::__construct( 'izi_shipping_method_pww_apm', $zone_id );
	}

	public function init(): void {
		parent::register();
	}

	public function get_label(): string {
		return __(
			"Pricing and shipping availability from Parcel Machine Map with:",
			"inpost-pay"
		);
	}

	public function get_tooltip(): string {
		return __(
			"Determines which shipping method is to be associated",
			"inpost-pay"
		);
	}
}
