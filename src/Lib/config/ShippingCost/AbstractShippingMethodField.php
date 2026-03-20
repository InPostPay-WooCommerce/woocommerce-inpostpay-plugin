<?php

namespace Ilabs\Inpost_Pay\Lib\config\ShippingCost;

use Ilabs\Inpost_Pay\Lib\form\AbstractOption;
use Ilabs\Inpost_Pay\Lib\form\exception\NotAllowedConfigOptionException;
use Ilabs\Inpost_Pay\Lib\form\exception\NotFoundConfigOptionException;
use Ilabs\Inpost_Pay\Lib\form\exception\RequiredConfigOptionException;
use Ilabs\Inpost_Pay\Lib\form\FormFieldInterface;
use Ilabs\Inpost_Pay\Lib\form\Select;

abstract class AbstractShippingMethodField extends AbstractZoneOption implements ShippingMappingFieldInterface {

	static protected array $shipping_methods = [];

	public function register( array $args = [] ): void {
		parent::register( $args );
	}

	public function get( $default = false ) {
		$val = parent::get( $default );

		if ( in_array( strtolower( $val ), [ '0', 'select', 'wybierz' ] ) ) {
			return '0';
		}

		return $val;
	}

	/**
	 * @throws RequiredConfigOptionException
	 * @throws NotAllowedConfigOptionException
	 * @throws NotFoundConfigOptionException
	 */
	public function get_form_field(): FormFieldInterface {

		return new Select(
			$this->get_all_available_shipping( $this->get_zone_id() ),
			[ $this->get() ],
			[
				'label'        => $this->get_label(),
				'name'         => $this->get_field_name(),
				'label_class'  => 'label-gray',
				'multiple'     => false,
				'value_as_key' => true,
			]
		);
	}

	private function get_all_ahipping_zones(): array {
		$data_store = \WC_Data_Store::load( 'shipping-zone' );
		$raw_zones  = $data_store->get_zones();
		foreach ( $raw_zones as $raw_zone ) {
			$zones[] = new \WC_Shipping_Zone( $raw_zone );
		}
		$zones[] = new \WC_Shipping_Zone( 0 );

		return $zones;
	}

	private function get_all_available_shipping( ?int $zone_id = null ): array {
		if ( ! empty( self::$shipping_methods ) ) {
			return self::$shipping_methods;
		}

		$available = [];
		if ( $zone_id !== null ) {
			$zone = new \WC_Shipping_Zone( $zone_id );
			$available = $this->get_available_shipping_methods( $zone );
		} else {
			foreach ( $this->get_all_ahipping_zones() as $zone ) {
				$available = $this->get_available_shipping_methods( $zone );
			}
		}

		self::$shipping_methods = $available;

		return $available;
	}

	private function get_available_shipping_methods( \WC_Shipping_Zone  $zone): array {
		$available = [];
		$zone_shipping_methods = $zone->get_shipping_methods();
		foreach ( $zone_shipping_methods as $index => $method ) {
			$available[ $method->get_rate_id() ] = $method->get_title();
		}

		return $available;
	}
}
