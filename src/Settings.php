<?php
/**
 * Settings class for InPost Pay plugin
 *
 * @package    InPost Pay
 * @since      1.0.0
 * @author     iLabs
 */

namespace Ilabs\Inpost_Pay;

use Ilabs\Inpost_Pay\Lib\config\analytics\AnalyticsConfig;
use Ilabs\Inpost_Pay\Lib\config\attribution\AttributionConfig;
use Ilabs\Inpost_Pay\Lib\config\attribution\AttributionOverridesConfig;
use Ilabs\Inpost_Pay\Lib\config\Hooks\CartHooksConfig;
use Ilabs\Inpost_Pay\Lib\config\Hooks\OrderHooksConfig;
use Ilabs\Inpost_Pay\Lib\config\payment\PaymentMethodsOptions;
use Ilabs\Inpost_Pay\Lib\config\payment\Virtual_Payment_Gateway_Config;
use Ilabs\Inpost_Pay\Lib\config\product\ExpiredHotProductsConfig;
use Ilabs\Inpost_Pay\Lib\config\product\HotProductsConfig;
use Ilabs\Inpost_Pay\Lib\config\product\InactiveHotProductsConfig;
use Ilabs\Inpost_Pay\Lib\config\ShippingCost\ShippingMappingSettingsManager;
use Ilabs\Inpost_Pay\Lib\config\widget_v2\WidgetV2SizeConfig;
use Ilabs\Inpost_Pay\Lib\helpers\ShippingZoneHelper;

/**
 * Handles the settings and configuration for InPost Pay plugin.
 *
 * This class is responsible for registering and managing all settings,
 * configuration options, and related functionality for the InPost Pay plugin.
 * It handles the integration with WordPress settings API and manages various
 * plugin configurations including payment methods, shipping options, and more.
 *
 * @package Ilabs\Inpost_Pay
 * @since 1.0.0
 */
class Settings {
	/**
	 * Registers all the settings for Inpost Pay.
	 *
	 * This function registers all the settings and config options for Inpost Pay.
	 */
	public function register(): void {
		add_action(
			'admin_init',
			static function () {

				// if (
				// isset($_POST['option_page']) &&
				// $_POST['option_page'] === 'inpost-izi'
				// ) {
				// var_dump('INPOST POST: ' . print_r($_POST, true));die;
				// }

				add_filter(
					'pre_update_option_izi_client_secret',
					static function ( $value, $old_value ) {
						if ( $old_value && ( ! str_replace( '*', '', $value ) ) ) {
							return $old_value;
						}

						return $value;
					},
					10,
					2
				);

				inpost_pay()->shipping_cost_settings()->register();
				/**
				* Shipping zone object.
				*
				* @var \WC_Shipping_Zone $zone
				*/
				foreach ( ShippingZoneHelper::get_all_shipping_zones() as $zone ) {
					inpost_pay()->shipping_cost_settings( $zone->get_id() )->register();
				}

				register_setting( 'inpost-izi', 'izi_show_basket' );
				register_setting( 'inpost-izi', 'izi_place_basket' );
				register_setting( 'inpost-izi', 'izi_align_basket' );
				register_setting(
					'inpost-izi',
					'izi_background',
					array(
						'type'    => 'string',
						'default' => 'bright',
					)
				);
				register_setting(
					'inpost-izi',
					'izi_variant',
					array(
						'type'    => 'string',
						'default' => 'primary',
					)
				);
				register_setting(
					'inpost-izi',
					'izi_frame_style',
					array(
						'type'    => 'string',
						'default' => 'none',
					)
				);

				register_setting( 'inpost-izi', 'izi_show_order' );
				register_setting( 'inpost-izi', 'izi_place_order' );
				register_setting( 'inpost-izi', 'izi_align_order' );

				register_setting( 'inpost-izi', 'izi_show_checkout' );
				register_setting( 'inpost-izi', 'izi_place_checkout' );
				register_setting( 'inpost-izi', 'izi_align_checkout' );

				register_setting( 'inpost-izi', 'izi_show_login_page' );
				register_setting( 'inpost-izi', 'izi_place_login_page' );
				register_setting( 'inpost-izi', 'izi_align_login_page' );

				register_setting( 'inpost-izi', 'izi_show_minicart' );
				register_setting( 'inpost-izi', 'izi_place_minicart' );
				register_setting( 'inpost-izi', 'izi_align_minicart' );

				register_setting( 'inpost-izi', 'izi_show_list' );
				register_setting( 'inpost-izi', 'izi_place_list' );
				register_setting( 'inpost-izi', 'izi_align_list' );

				register_setting( 'inpost-izi', 'izi_button_cart_margin' );
				register_setting( 'inpost-izi', 'izi_button_cart_padding' );

				register_setting( 'inpost-izi', 'izi_button_details_margin' );
				register_setting( 'inpost-izi', 'izi_button_details_padding' );

				register_setting( 'inpost-izi', 'izi_show_details' );
				register_setting( 'inpost-izi', 'izi_place_details' );
				register_setting( 'inpost-izi', 'izi_align_details' );

				register_setting( 'inpost-izi', 'izi_client_id' );
				register_setting( 'inpost-izi', 'izi_environment' );
				register_setting( 'inpost-izi', 'izi_client_secret' );
				register_setting( 'inpost-izi', 'izi_merchant_payment' );
				register_setting( 'inpost-izi', 'izi_hide_functionality' );

				register_setting( 'inpost-izi', 'izi_consents' );

				register_setting( 'inpost-izi', 'izi_event_AUTHORIZED' );
				register_setting( 'inpost-izi', 'izi_event_cod_AUTHORIZED' );
				register_setting( 'inpost-izi', 'izi_status_map' );

				register_setting( 'inpost-izi', SettingsPage::OPT_KEY_PRODUCT_DESC_MAP );
				register_setting( 'inpost-izi', 'izi_related_count' );

				register_setting( 'inpost-izi', 'izi_pos_id' );
				register_setting( 'inpost-izi', 'izi_merchant_id' );

				register_setting( 'inpost-izi', 'izi_payment_aion' );
				register_setting( 'inpost-izi', 'izi_payment_inpost' );

				register_setting(
					'inpost-izi',
					'izi_debug',
					array(
						'type'    => 'bool',
						'default' => false,
					)
				);

				register_setting(
					'inpost-izi',
					'izi_check_shipping_availability',
					array(
						'type'    => 'bool',
						'default' => false,
					)
				);

				register_setting(
					'inpost-izi',
					'izi_is_authorized',
					array(
						'type'    => 'bool',
						'default' => false,
					)
				);

				register_setting(
					'inpost-izi',
					'izi_omnibus_show_on_listing',
					array(
						'type'    => 'bool',
						'default' => false,
					)
				);

				register_setting(
					'inpost-izi',
					'izi_omnibus_show_on_none_discount_products',
					array(
						'type'    => 'bool',
						'default' => false,
					)
				);

				register_setting(
					'inpost-izi',
					'izi_refresh_after_add_to_cart',
					array(
						'type'    => 'bool',
						'default' => true,
					)
				);

				register_setting(
					'inpost-izi',
					'izi_main_image_only',
					array(
						'type'    => 'bool',
						'default' => false,
					)
				);

				register_setting(
					'inpost-izi',
					'izi_custom_basket_response_enabled',
					array(
						'type'    => 'bool',
						'default' => false,
					)
				);

				register_setting(
					'inpost-izi',
					'izi_custom_response_enabled',
					array(
						'type'    => 'bool',
						'default' => true,
					)
				);

				register_setting(
					'inpost-izi',
					'izi_early_update_response_enabled',
					array(
						'type'    => 'bool',
						'default' => false,
					)
				);

				register_setting(
					'inpost-izi',
					'izi_thank_you_page_id',
					array(
						'type'              => 'integer',
						'default'           => 0,
						'sanitize_callback' => 'absint',
					)
				);

				( new PaymentMethodsOptions() )->register();
				( new AttributionConfig() )->register();
				( new AttributionOverridesConfig() )->register();

				( new WidgetV2SizeConfig() )->register();
				( new HotProductsConfig() )->register();
				( new InactiveHotProductsConfig() )->register();
				( new ExpiredHotProductsConfig() )->register();

				( new OrderHooksConfig() )->register();
				( new CartHooksConfig() )->register();

				( new AnalyticsConfig() )->register();
				( new Virtual_Payment_Gateway_Config() )->register();
			}
		);
	}
}
