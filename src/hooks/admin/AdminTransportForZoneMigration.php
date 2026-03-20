<?php
/**
 * Admin Transport For Zone Migration Hook.
 *
 * @package InpostPay
 * @subpackage Hooks/Admin
 */

namespace Ilabs\Inpost_Pay\hooks\admin;

use Ilabs\Inpost_Pay\hooks\Base;
use Ilabs\Inpost_Pay\Lib\config\ShippingCost\Apm\ApmGroup;
use Ilabs\Inpost_Pay\Lib\config\ShippingCost\Apm\ApmShippingMethod;
use Ilabs\Inpost_Pay\Lib\config\ShippingCost\CodApm\CodApmGroup;
use Ilabs\Inpost_Pay\Lib\config\ShippingCost\CodApm\CodApmPrice;
use Ilabs\Inpost_Pay\Lib\config\ShippingCost\CodApm\CodApmShippingMethod;
use Ilabs\Inpost_Pay\Lib\config\ShippingCost\CodCourier\CodCourierGroup;
use Ilabs\Inpost_Pay\Lib\config\ShippingCost\CodCourier\CodCourierPrice;
use Ilabs\Inpost_Pay\Lib\config\ShippingCost\CodCourier\CodCourierShippingMethod;
use Ilabs\Inpost_Pay\Lib\config\ShippingCost\Courier\CourierGroup;
use Ilabs\Inpost_Pay\Lib\config\ShippingCost\Courier\CourierShippingMethod;
use Ilabs\Inpost_Pay\Lib\config\ShippingCost\PwwApm\PwwApmAvailableFromDay;
use Ilabs\Inpost_Pay\Lib\config\ShippingCost\PwwApm\PwwApmAvailableFromHour;
use Ilabs\Inpost_Pay\Lib\config\ShippingCost\PwwApm\PwwApmAvailableToDay;
use Ilabs\Inpost_Pay\Lib\config\ShippingCost\PwwApm\PwwApmAvailableToHour;
use Ilabs\Inpost_Pay\Lib\config\ShippingCost\PwwApm\PwwApmGroup;
use Ilabs\Inpost_Pay\Lib\config\ShippingCost\PwwApm\PwwApmPrice;
use Ilabs\Inpost_Pay\Lib\config\ShippingCost\PwwApm\PwwApmShippingMethod;
use Ilabs\Inpost_Pay\Lib\helpers\ShippingZoneHelper;

/**
 * Class AdminTransportForZoneMigration
 *
 * Handles migration of old transportation configuration to shipping zones.
 */
class AdminTransportForZoneMigration extends Base {

	/**
	 * Migration option key.
	 *
	 * @var string
	 */
	public const MIGRATION_OPTION_KEY = 'inpost_pay_transport_zone_migration_done';

	/**
	 * Attach hooks.
	 *
	 * @return void
	 */
	public function attach_hook(): void {
		add_action( 'admin_init', array( $this, 'migrate_old_transportation_config' ) );
	}

	/**
	 * Migrate old transportation configuration.
	 *
	 * @return void
	 */
	public function migrate_old_transportation_config(): void {
		if ( get_option( self::MIGRATION_OPTION_KEY, false ) ) {
			return;
		}

		$old_keys = array(
			( new ApmShippingMethod() )->get_field_name(),
			( new CodApmPrice() )->get_field_name(),
			( new CodApmShippingMethod() )->get_field_name(),
			( new CodCourierPrice() )->get_field_name(),
			( new CodCourierShippingMethod() )->get_field_name(),
			( new CourierShippingMethod() )->get_field_name(),
			( new PwwApmAvailableFromDay() )->get_field_name(),
			( new PwwApmAvailableFromHour() )->get_field_name(),
			( new PwwApmAvailableToDay() )->get_field_name(),
			( new PwwApmAvailableToHour() )->get_field_name(),
			( new PwwApmPrice() )->get_field_name(),
			( new PwwApmShippingMethod() )->get_field_name(),
			( new ApmGroup() )->getIsActiveFieldId(),
			( new CourierGroup() )->getIsActiveFieldId(),
			( new CodCourierGroup() )->getIsActiveFieldId(),
			( new CodApmGroup() )->getIsActiveFieldId(),
			( new PwwApmGroup() )->getIsActiveFieldId(),
			( new CodCourierGroup() )->getOptionCostMappingApproachId(),
			( new CodApmGroup() )->getOptionCostMappingApproachId(),
			( new PwwApmGroup() )->getOptionCostMappingApproachId(),
		);

		$zones = ShippingZoneHelper::get_all_shipping_zones();

		if ( empty( $zones ) ) {
			return;
		}

		$poland_zone = null;
		foreach ( $zones as $zone ) {
			/**
			 * Get Zone Name.
			 *
			 * @var \WC_Shipping_Zone $zone Shipping zone object.
			 */
			$name = $zone->get_zone_name();
			if ( false !== stripos( $name, 'poland' ) || false !== stripos( $name, 'polska' ) ) {
				$poland_zone = $zone;
				break;
			}
		}

		$target_zone = $poland_zone ?? end( $zones );

		if ( ! $target_zone ) {
			return;
		}

		$zone_id = $target_zone->get_id();

		foreach ( $old_keys as $key ) {
			$old_value = get_option( $key, null );
			$new_key   = $key . '_' . $zone_id;
			if ( null !== $old_value && null === get_option( $new_key, null ) ) {
				update_option( $new_key, $old_value );
			}
		}

		update_option( self::MIGRATION_OPTION_KEY, 1 );
	}
}
