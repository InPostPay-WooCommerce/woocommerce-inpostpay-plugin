<?php

namespace Ilabs\Inpost_Pay\Lib\config\ShippingCost;

use Ilabs\Inpost_Pay\Lib\form\AbstractOption;

abstract class AbstractZoneOption extends AbstractOption {
	private ?int $zone_id = null;

	public function __construct( string $option_name, ?int $zone_id = null ) {
		parent::__construct( $option_name );

		if ( $zone_id !== null ) {
			$zone      = new \WC_Shipping_Zone( $zone_id );
			$zone_name = $zone->get_zone_name();
			if ( ! strpos( $zone_name, 'Poland' ) && ! strpos( $zone_name, 'Polska' ) ) {
				$this->set_zone_id( $zone_id );
				$this->set_field_name( $this->get_field_name() . '_' . $zone_id );
			}
		}
	}

	public function set_zone_id( ?int $zone_id ): void {
		$this->zone_id = $zone_id;
	}

	public function get_zone_id(): ?int {
		return $this->zone_id;
	}
}
