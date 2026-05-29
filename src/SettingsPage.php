<?php

namespace Ilabs\Inpost_Pay;

use Ilabs\Inpost_Pay\Lib\Authorization;
use Ilabs\Inpost_Pay\Lib\exception\AuthorizationException;
use Ilabs\Inpost_Pay\Lib\helpers\CacheHelper;
use Ilabs\Inpost_Pay\Type\ConsentType;

class SettingsPage {

	public const OPT_KEY_PRODUCT_DESC_MAP                 = 'izi_product_desc_map';
	public const OPT_DROPDOWN_ID_FULL_PRODUCT_DESC_MAP    = 'full';
	public const OPT_DROPDOWN_ID_SHORT_PRODUCT_DESC_MAP   = 'short';
	public const OPT_DROPDOWN_ID_DEFAULT_PRODUCT_DESC_MAP = self::OPT_DROPDOWN_ID_FULL_PRODUCT_DESC_MAP;
	private bool $check_authorization                     = false;


	public function check_authorization(): void {
		if ( ! $this->check_authorization ) {
			$this->check_authorization = true;
			$authorization             = new Authorization();
			try {
				$authorization->getToken( true );
				update_option( 'izi_is_authorized', true );
				CacheHelper::flush_cache();
			} catch ( AuthorizationException $ex ) {
				add_settings_error(
					'izi_messages',
					'izi_message',
					__( 'Wrong credentials', 'inpost-pay' )
				);
			}
		}
	}

	private function allShippingZones() {
		$data_store = \WC_Data_Store::load( 'shipping-zone' );
		$raw_zones  = $data_store->get_zones();
		foreach ( $raw_zones as $raw_zone ) {
			$zones[] = new \WC_Shipping_Zone( $raw_zone );
		}
		$zones[] = new \WC_Shipping_Zone( 0 ); // ADD ZONE "0" MANUALLY

		return $zones;
	}

	public function displayPluginAdminDashboard() {
		$consent_requirement = ConsentType::all();

		$days_of_week = array(
			1 => __( 'Monday', 'inpost-pay' ),
			2 => __( 'Tuesday', 'inpost-pay' ),
			3 => __( 'Wednesday', 'inpost-pay' ),
			4 => __( 'Thursday', 'inpost-pay' ),
			5 => __( 'Friday', 'inpost-pay' ),
			6 => __( 'Saturday', 'inpost-pay' ),
			7 => __( 'Sunday', 'inpost-pay' ),
		);

		$hours_of_day = range( 0, 23 );

		$available_aligns = array(
			'left'   => __( 'To the left', 'inpost-pay' ),
			'center' => __( 'To the center', 'inpost-pay' ),
			'right'  => __( 'To the right', 'inpost-pay' ),
		);

		$available_backgrounds = array(
			'bright' => __( 'Bright', 'inpost-pay' ),
			'dark'   => __( 'Dark', 'inpost-pay' ),
		);

		$available_variants = array(
			'primary'   => __( 'Yellow', 'inpost-pay' ),
			'secondary' => __( 'Black', 'inpost-pay' ),
		);

		$available_frame_style = array(
			'none'    => __( 'No round', 'inpost-pay' ),
			'round'   => __( 'Big round', 'inpost-pay' ),
			'rounded' => __( 'Small round', 'inpost-pay' ),
		);

		$paymentOptions         = get_option( 'izi_merchant_payment' );
		$button_cart_margin     = get_option( 'izi_button_cart_margin' );
		$button_cart_padding    = get_option( 'izi_button_cart_padding' );
		$button_details_margin  = get_option( 'izi_button_details_margin' );
		$button_details_padding = get_option( 'izi_button_details_padding' );
		$checked                = function ( $name ) use ( $paymentOptions ) {
			if ( ! is_array( $paymentOptions ) ) {
				return '';
			}
			if ( in_array( $name, $paymentOptions ) ) {
				return 'checked';
			}

			return '';
		};
		require_once inpost_pay()->get_plugin_dir() . 'src/views/admin.php';
	}

	public static function statusDropdown( $status ) {
		$value = esc_attr( get_option( 'izi_event_' . $status ) );
		echo "<select name='izi_event_{$status}'>";
		echo "<option value=''>" . __( 'Select', 'inpost-pay' ) . '</option>';
		foreach ( StatusTranslator::ayastmAvailableStatusses() as $system => $availableLabel ) {
			$selected = $value == $system ? 'selected' : '';
			echo "<option {$selected} value='{$system}'>{$availableLabel}</option>";
		}
		echo '</select>';
	}

	public static function statusCodDropdown( $status ) {
		$value = esc_attr( get_option( 'izi_event_cod_' . $status ) );
		echo "<select name='izi_event_cod_{$status}'>";
		echo "<option value=''>" . __( 'Select', 'inpost-pay' ) . '</option>';
		foreach ( StatusTranslator::ayastmAvailableStatusses() as $system => $availableLabel ) {
			$selected = $value == $system ? 'selected' : '';
			echo "<option {$selected} value='{$system}'>{$availableLabel}</option>";
		}
		echo '</select>';
	}

	public static function initialOrderStatusDropdown() {
		$value = esc_attr( get_option( 'izi_initial_order_status' ) );
		echo "<select name='izi_initial_order_status'>";
		echo "<option value=''>" . __( 'Select', 'inpost-pay' ) . '</option>';
		foreach ( StatusTranslator::ayastmAvailableStatusses() as $system => $availableLabel ) {
			$selected = $value == $system ? 'selected' : '';
			echo "<option {$selected} value='{$system}'>{$availableLabel}</option>";
		}
		echo '</select>';
	}

	public static function productDescMapDropdown() {
		$optId = self::OPT_KEY_PRODUCT_DESC_MAP;
		$value = esc_attr(
			get_option(
				$optId,
				self::OPT_DROPDOWN_ID_DEFAULT_PRODUCT_DESC_MAP
			)
		);

		echo "<select name='{$optId}'>";
		foreach (
			array(
				self::OPT_DROPDOWN_ID_FULL_PRODUCT_DESC_MAP  => __(
					'Full description',
					'inpost-pay'
				),
				self::OPT_DROPDOWN_ID_SHORT_PRODUCT_DESC_MAP => __(
					'Short description',
					'inpost-pay'
				),
			) as $key => $label
		) {
			$selected = $value === $key ? 'selected' : '';
			echo "<option {$selected} value='{$key}'>{$label}</option>";
		}
		echo '</select>';
	}

	public static function statusMap() {
		$value = get_option( 'izi_status_map' );
		echo '<table><tbody>';
		foreach ( StatusTranslator::ayastmAvailableStatusses() as $system => $availableLabel ) {
			$label = ( ! empty( $value[ $system ] ) ) ? esc_attr( $value[ $system ] ) : $availableLabel;
			echo "<tr><td>{$availableLabel}</td><td><input type='text' name='izi_status_map[{$system}]' value='{$label}'></td></tr>";
		}
		echo '</tbody></table>';
	}
}
