<?php
/**
 * COD courier shipping method field.
 *
 * @package Ilabs\Inpost_Pay\Lib\config\ShippingCost\CodCourier
 */

declare( strict_types=1 );

namespace Ilabs\Inpost_Pay\Lib\config\ShippingCost\CodCourier;

use Ilabs\Inpost_Pay\Lib\config\ShippingCost\AbstractShippingMethodField;
use Ilabs\Inpost_Pay\Lib\config\ShippingCost\CourierMethodGroupField;
use Ilabs\Inpost_Pay\Lib\config\ShippingCost\GroupInterface;
use Ilabs\Inpost_Pay\Lib\config\ShippingCost\ShippingMappingFieldInterface;
use Ilabs\Inpost_Pay\Lib\form\exception\OptionNameRequired;
use Ilabs\Inpost_Pay\Lib\form\LegacyOptionInterface;

/**
 * Class CodCourierShippingMethod
 *
 * WordPress option for selecting the WooCommerce shipping method linked to COD courier delivery.
 */
final class CodCourierShippingMethod extends AbstractShippingMethodField implements LegacyOptionInterface, CourierMethodGroupField {

	/**
	 * Returns a new CodCourierShippingMethod instance.
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
		return GroupInterface::DELIVERY_OPTION_CODE_COD;
	}

	/**
	 * Constructor.
	 *
	 * @param int|null $zone_id Optional zone ID.
	 */
	public function __construct( ?int $zone_id = null ) {

		parent::__construct( 'izi_shipping_method_cod_courier', $zone_id );
	}

	/**
	 * Registers the option with WordPress settings API.
	 *
	 * @return void
	 */
	public function init(): void {
		$this->register();
	}

	/**
	 * Returns the field label.
	 *
	 * @return string
	 */
	public function get_label(): string {
		return __(
			'Prices and courier shipping availability map with:',
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
		return 'izi_transport_method_courier'; // Legacy option name without "cod" prefix.
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
