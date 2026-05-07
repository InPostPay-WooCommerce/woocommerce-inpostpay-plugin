<?php

namespace Ilabs\Inpost_Pay\Lib\config\ShippingCost\CodCourier;

use Ilabs\Inpost_Pay\Lib\config\ShippingCost\AbstractPriceField;
use Ilabs\Inpost_Pay\Lib\config\ShippingCost\CourierMethodGroupField;
use Ilabs\Inpost_Pay\Lib\config\ShippingCost\GroupInterface;
use Ilabs\Inpost_Pay\Lib\form\exception\OptionNameRequired;
use Ilabs\Inpost_Pay\Lib\form\LegacyOptionInterface;

final class CodCourierPrice extends AbstractPriceField implements LegacyOptionInterface, CourierMethodGroupField {

	/**
	 * @throws OptionNameRequired
	 */
	public static function instance( ?int $zone_id = null ): self {
		return new self( $zone_id );
	}

	public function getDeliveryOptionCode(): string {
		return GroupInterface::DELIVERY_OPTION_CODE_COD;
	}

	public function __construct( ?int $zone_id = null ) {

		parent::__construct( 'izi_shipping_price_cod_courier', $zone_id );
	}

	public function init(): void {
		$this->register();
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
		return 'izi_transport_price_cod_courier';
	}

	public function has_legacy_option_priority(): bool {
		return true;
	}
}
