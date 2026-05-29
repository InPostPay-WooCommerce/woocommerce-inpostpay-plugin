<?php
/**
 * APM shipping method field.
 *
 * @package Ilabs\Inpost_Pay\Lib\config\ShippingCost\Apm
 */

declare( strict_types=1 );

namespace Ilabs\Inpost_Pay\Lib\config\ShippingCost\Apm;

use Ilabs\Inpost_Pay\Lib\config\ShippingCost\AbstractShippingMethodField;
use Ilabs\Inpost_Pay\Lib\config\ShippingCost\ApmMethodGroupField;
use Ilabs\Inpost_Pay\Lib\config\ShippingCost\GroupInterface;
use Ilabs\Inpost_Pay\Lib\config\ShippingCost\ShippingMappingFieldInterface;
use Ilabs\Inpost_Pay\Lib\form\exception\OptionNameRequired;

/**
 * Class ApmShippingMethod
 *
 * WordPress option for selecting the WooCommerce shipping method linked to APM delivery.
 */
final class ApmShippingMethod extends AbstractShippingMethodField implements ApmMethodGroupField {

	/**
	 * Returns a new ApmShippingMethod instance.
	 *
	 * @throws OptionNameRequired When the option name is not set.
	 *
	 * @param int|null $zone_id Optional zone ID.
	 *
	 * @return self
	 */
	public static function instance( ?int $zone_id = null ): self {
		return new self( $zone_id );
	}

	/**
	 * Constructor.
	 *
	 * @param int|null $zone_id Optional zone ID.
	 */
	public function __construct( ?int $zone_id = null ) {

		parent::__construct( 'izi_shipping_method_apm', $zone_id );
	}

	/**
	 * Returns the delivery option code.
	 *
	 * @return string
	 */
	public function get_delivery_option_code(): string {
		return GroupInterface::DELIVERY_OPTION_CODE_NONE;
	}

	/**
	 * Registers the option with WordPress settings API.
	 *
	 * @return void
	 */
	public function init(): void {
		parent::register();
	}

	/**
	 * Returns the field label.
	 *
	 * @return string
	 */
	public function get_label(): string {
		return __(
			'Pricing and shipping availability from Parcel Machine Map with:',
			'inpost-pay'
		);
	}

	/**
	 * Returns the field tooltip.
	 *
	 * @return string
	 */
	public function get_tooltip(): string {
		return __(
			'Determines which shipping method is to be associated',
			'inpost-pay'
		);
	}
}
