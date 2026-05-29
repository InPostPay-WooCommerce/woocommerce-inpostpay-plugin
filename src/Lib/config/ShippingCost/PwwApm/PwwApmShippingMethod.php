<?php
/**
 * PWW APM shipping method field.
 *
 * @package Ilabs\Inpost_Pay\Lib\config\ShippingCost\PwwApm
 */

declare( strict_types=1 );

namespace Ilabs\Inpost_Pay\Lib\config\ShippingCost\PwwApm;

use Ilabs\Inpost_Pay\Lib\config\ShippingCost\AbstractShippingMethodField;
use Ilabs\Inpost_Pay\Lib\config\ShippingCost\ApmMethodGroupField;
use Ilabs\Inpost_Pay\Lib\config\ShippingCost\GroupInterface;
use Ilabs\Inpost_Pay\Lib\config\ShippingCost\ShippingMappingFieldInterface;
use Ilabs\Inpost_Pay\Lib\form\exception\OptionNameRequired;

/**
 * Class PwwApmShippingMethod
 *
 * WordPress option for selecting the WooCommerce shipping method linked to PWW APM delivery.
 */
final class PwwApmShippingMethod extends AbstractShippingMethodField implements ApmMethodGroupField {

	/**
	 * Returns a new PwwApmShippingMethod instance.
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
	 * Returns the delivery option code.
	 *
	 * @return string
	 */
	public function get_delivery_option_code(): string {
		return GroupInterface::DELIVERY_OPTION_CODE_PWW;
	}

	/**
	 * Constructor.
	 *
	 * @param int|null $zone_id Optional zone ID.
	 */
	public function __construct( ?int $zone_id = null ) {

		parent::__construct( 'izi_shipping_method_pww_apm', $zone_id );
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
