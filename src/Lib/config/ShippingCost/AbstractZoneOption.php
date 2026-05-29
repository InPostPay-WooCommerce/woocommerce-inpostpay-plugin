<?php
/**
 * Abstract zone-scoped option.
 *
 * @package Ilabs\Inpost_Pay\Lib\config\ShippingCost
 */

declare( strict_types=1 );

namespace Ilabs\Inpost_Pay\Lib\config\ShippingCost;

use Ilabs\Inpost_Pay\Lib\form\AbstractOption;

/**
 * Class AbstractZoneOption
 *
 * Base class for WordPress options that are optionally scoped to a WooCommerce shipping zone.
 */
abstract class AbstractZoneOption extends AbstractOption {
	private ?int $zone_id = null;

	/**
	 * Constructor.
	 *
	 * @param string   $option_name The WordPress option name.
	 * @param int|null $zone_id     Optional shipping zone ID.
	 */
	public function __construct( string $option_name, ?int $zone_id = null ) {
		parent::__construct( $option_name );

		if ( null !== $zone_id ) {
			$zone      = new \WC_Shipping_Zone( $zone_id );
			$zone_name = $zone->get_zone_name();
			if ( ! strpos( $zone_name, 'Poland' ) && ! strpos( $zone_name, 'Polska' ) ) {
				$this->set_zone_id( $zone_id );
				$this->set_field_name( $this->get_field_name() . '_' . $zone_id );
			}
		}
	}

	/**
	 * Sets the shipping zone ID.
	 *
	 * @param int|null $zone_id Shipping zone ID.
	 *
	 * @return void
	 */
	public function set_zone_id( ?int $zone_id ): void {
		$this->zone_id = $zone_id;
	}

	/**
	 * Returns the shipping zone ID.
	 *
	 * @return int|null
	 */
	public function get_zone_id(): ?int {
		return $this->zone_id;
	}
}
