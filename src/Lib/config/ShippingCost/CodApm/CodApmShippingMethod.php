<?php
/**
 * COD APM shipping method field.
 *
 * @package Ilabs\Inpost_Pay\Lib\config\ShippingCost\CodApm
 */

declare( strict_types=1 );

namespace Ilabs\Inpost_Pay\Lib\config\ShippingCost\CodApm;

use Ilabs\Inpost_Pay\Lib\config\ShippingCost\AbstractShippingMethodField;
use Ilabs\Inpost_Pay\Lib\config\ShippingCost\ApmMethodGroupField;
use Ilabs\Inpost_Pay\Lib\config\ShippingCost\GroupInterface;
use Ilabs\Inpost_Pay\Lib\config\ShippingCost\ShippingMappingFieldInterface;
use Ilabs\Inpost_Pay\Lib\form\exception\OptionNameRequired;
use Ilabs\Inpost_Pay\Lib\form\LegacyOptionInterface;

/**
 * Class CodApmShippingMethod
 *
 * WordPress option for selecting the WooCommerce shipping method linked to COD APM delivery.
 */
final class CodApmShippingMethod extends AbstractShippingMethodField implements LegacyOptionInterface, ApmMethodGroupField {

	/**
	 * Returns a new CodApmShippingMethod instance.
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

		parent::__construct( 'izi_shipping_method_cod_apm', $zone_id );
	}

	/**
	 * Returns the delivery option code.
	 *
	 * @return string
	 */
	public function get_delivery_option_code(): string {
		return GroupInterface::DELIVERY_OPTION_CODE_COD;
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

	/**
	 * Returns the legacy option ID.
	 *
	 * @return string
	 */
	public function get_legacy_option_id(): string {
		return 'izi_transport_method_apm'; // Legacy option name without "cod" prefix.
	}

	/**
	 * Returns true as legacy option takes priority.
	 *
	 * @return bool
	 */
	public function has_legacy_option_priority(): bool {
		return true;
	}
}
