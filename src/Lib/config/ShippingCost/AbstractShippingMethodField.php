<?php
/**
 * Abstract shipping method field.
 *
 * @package Ilabs\Inpost_Pay\Lib\config\ShippingCost
 */

declare( strict_types=1 );

namespace Ilabs\Inpost_Pay\Lib\config\ShippingCost;

use Ilabs\Inpost_Pay\Lib\form\AbstractOption;
use Ilabs\Inpost_Pay\Lib\form\exception\NotAllowedConfigOptionException;
use Ilabs\Inpost_Pay\Lib\form\exception\NotFoundConfigOptionException;
use Ilabs\Inpost_Pay\Lib\form\exception\RequiredConfigOptionException;
use Ilabs\Inpost_Pay\Lib\form\FormFieldInterface;
use Ilabs\Inpost_Pay\Lib\form\Select;

/**
 * Class AbstractShippingMethodField
 *
 * Base class for WooCommerce shipping method selector fields.
 */
abstract class AbstractShippingMethodField extends AbstractZoneOption implements ShippingMappingFieldInterface {

	/**
	 * Cached list of available WooCommerce shipping methods.
	 *
	 * @var array
	 */
	protected static array $shipping_methods = array();

	/**
	 * Registers the option with the WordPress settings API.
	 *
	 * @param array $args Optional arguments passed to register_setting().
	 *
	 * @return void
	 */
	public function register( array $args = array() ): void {
		parent::register( $args );
	}

	/**
	 * Returns the saved WooCommerce shipping method identifier for this field.
	 *
	 * Normalises placeholder values ('select', 'wybierz', '0') to '0' and
	 * guarantees a string return type even when the option has never been saved
	 * to the database.
	 *
	 * @param mixed $default_value Default value returned when the option is absent from the database.
	 *
	 * @return string Shipping method identifier, '0' for unselected placeholders, or '' when absent.
	 */
	public function get( $default_value = '' ): string {
		$val = parent::get( $default_value );

		if ( ! is_string( $val ) ) {
			return '';
		}

		if ( in_array( strtolower( $val ), array( '0', 'select', 'wybierz' ), true ) ) {
			return '0';
		}

		return $val;
	}

	/**
	 * Builds and returns the Select form field for this shipping method option.
	 *
	 * @throws RequiredConfigOptionException   When required option data is missing.
	 * @throws NotAllowedConfigOptionException When the option value is not allowed.
	 * @throws NotFoundConfigOptionException   When the option cannot be found.
	 *
	 * @return FormFieldInterface
	 */
	public function get_form_field(): FormFieldInterface {

		return new Select(
			$this->get_all_available_shipping( $this->get_zone_id() ),
			array( $this->get() ),
			array(
				'label'        => $this->get_label(),
				'name'         => $this->get_field_name(),
				'label_class'  => 'label-gray',
				'multiple'     => false,
				'value_as_key' => true,
			)
		);
	}

	/**
	 * Returns all WooCommerce shipping zones including the catch-all zone (ID 0).
	 *
	 * @return \WC_Shipping_Zone[]
	 */
	private function get_all_shipping_zones(): array {
		$data_store = \WC_Data_Store::load( 'shipping-zone' );
		$raw_zones  = $data_store->get_zones();
		$zones      = array();
		foreach ( $raw_zones as $raw_zone ) {
			$zones[] = new \WC_Shipping_Zone( $raw_zone );
		}
		$zones[] = new \WC_Shipping_Zone( 0 );

		return $zones;
	}

	/**
	 * Returns available WooCommerce shipping methods, optionally scoped to a zone.
	 *
	 * Result is stored in a static cache to avoid redundant lookups within the
	 * same request.
	 *
	 * @param int|null $zone_id Zone ID to scope the lookup, or null for all zones.
	 *
	 * @return array Map of rate ID to method title.
	 */
	private function get_all_available_shipping( ?int $zone_id = null ): array {
		if ( ! empty( self::$shipping_methods ) ) {
			return self::$shipping_methods;
		}

		$available = array();
		if ( null !== $zone_id ) {
			$zone      = new \WC_Shipping_Zone( $zone_id );
			$available = $this->get_available_shipping_methods( $zone );
		} else {
			foreach ( $this->get_all_shipping_zones() as $zone ) {
				$available = $this->get_available_shipping_methods( $zone );
			}
		}

		self::$shipping_methods = $available;

		return $available;
	}

	/**
	 * Returns shipping methods available in a given zone as a rate ID to title map.
	 *
	 * @param \WC_Shipping_Zone $zone Shipping zone to query.
	 *
	 * @return array Map of rate ID to method title.
	 */
	private function get_available_shipping_methods( \WC_Shipping_Zone $zone ): array {
		$available             = array();
		$zone_shipping_methods = $zone->get_shipping_methods();
		foreach ( $zone_shipping_methods as $method ) {
			$available[ $method->get_rate_id() ] = $method->get_title();
		}

		return $available;
	}
}
