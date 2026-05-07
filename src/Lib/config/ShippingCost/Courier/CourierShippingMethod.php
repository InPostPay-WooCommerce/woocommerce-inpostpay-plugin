<?php

namespace Ilabs\Inpost_Pay\Lib\config\ShippingCost\Courier;

use Ilabs\Inpost_Pay\Lib\config\ShippingCost\AbstractShippingMethodField;
use Ilabs\Inpost_Pay\Lib\config\ShippingCost\CourierMethodGroupField;
use Ilabs\Inpost_Pay\Lib\config\ShippingCost\GroupInterface;
use Ilabs\Inpost_Pay\Lib\config\ShippingCost\ShippingMappingFieldInterface;
use Ilabs\Inpost_Pay\Lib\form\exception\OptionNameRequired;

final class CourierShippingMethod extends AbstractShippingMethodField implements CourierMethodGroupField {

	/**
	 * @throws OptionNameRequired
	 */
	public static function instance( ?int $zone_id ): self {
		return new self( $zone_id );
	}

	public function getDeliveryOptionCode(): string {
		return GroupInterface::DELIVERY_OPTION_CODE_NONE;
	}

	public function __construct( ?int $zone_id = null ) {

		parent::__construct( 'izi_shipping_method_courier', $zone_id );
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
}
