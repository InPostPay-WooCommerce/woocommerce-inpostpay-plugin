<?php
/**
 * PWW APM available-from day field.
 *
 * @package Ilabs\Inpost_Pay\Lib\config\ShippingCost\PwwApm
 */

declare( strict_types=1 );

namespace Ilabs\Inpost_Pay\Lib\config\ShippingCost\PwwApm;

use Ilabs\Inpost_Pay\Lib\config\ShippingCost\AbstractAvailabilityField;
use Ilabs\Inpost_Pay\Lib\config\ShippingCost\ApmMethodGroupField;
use Ilabs\Inpost_Pay\Lib\config\ShippingCost\GroupInterface;
use Ilabs\Inpost_Pay\Lib\config\ShippingCost\ShippingMappingFieldInterface;
use Ilabs\Inpost_Pay\Lib\form\exception\OptionNameRequired;
use Ilabs\Inpost_Pay\Lib\form\LegacyOptionInterface;

/**
 * Class PwwApmAvailableFromDay
 *
 * WordPress option storing the availability start day for PWW APM delivery.
 */
final class PwwApmAvailableFromDay extends AbstractAvailabilityField implements LegacyOptionInterface, ApmMethodGroupField {

	/**
	 * Returns a new PwwApmAvailableFromDay instance.
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

		parent::__construct( 'izi_shipping_available_from_day_pww_apm', $zone_id );
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
		return 'izi_transport_available_from_day_pww_apm';
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
