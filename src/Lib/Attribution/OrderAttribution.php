<?php

namespace Ilabs\Inpost_Pay\Lib\Attribution;

use Automattic\WooCommerce\Internal\Traits\OrderAttributionMeta;
use Ilabs\Inpost_Pay\Lib\config\attribution\AttributionConfig;
use Ilabs\Inpost_Pay\Lib\config\attribution\AttributionOverridesConfig;
use Ilabs\Inpost_Pay\Lib\exception\CantCreateAttribution;
use Ilabs\Inpost_Pay\models\CartSessionService;
use JsonException;
use function Ilabs\Inpost_Pay\inpost_pay_container;

class OrderAttribution {

	use OrderAttributionMeta;

	private string $cart_id;

	private array $session = array();

	private Attribution $attribution;

	private CartSessionService $cart_session;

	/**
	 * @param string $cart_id
	 *
	 * @throws CantCreateAttribution|JsonException
	 */
	public function __construct( string $cart_id ) {
		if ( ! self::is_order_attribution_feature_enabled() ) {
			throw new CantCreateAttribution( 'Order attribution feature is disabled in WooCommerce' );
		}
		if ( ! ( new AttributionConfig() )->is_enabled() ) {
			throw new CantCreateAttribution( 'Order attribution feature is disabled in InpostPay' );
		}

		/**
		 * Get from container DI.
		 *
		 * @var CartSessionService $cart_session
		 */
		$this->cart_session = inpost_pay_container()->get( CartSessionService::SERVICE_KEY );
		$this->attribution  = new Attribution();

		$this->cart_id = $cart_id;
		$this->get_from_session();

		if ( empty( $this->session ) ) {
			throw new CantCreateAttribution( 'Empty session data' );
		}

		$this->get_utm();

		if ( ( new AttributionOverridesConfig() )->is_enabled() ) {
			$this->override_utm_by_inpost();
		}
	}


	public function add_to_order( \WC_Order $order ): void {
		$this->set_fields_and_prefix();

		foreach ( $this->get_source_values( $this->attribution->to_array() ) as $key => $value ) {
			$order->add_meta_data( $this->get_meta_prefixed_field_name( $key ), $value );
		}
	}

	/**
	 * @throws JsonException
	 */
	public function get_from_session(): void {
		$this->session = $this->cart_session->get_session_id( $this->cart_id );
	}

	private function get_utm(): void {
		if ( isset( $this->session['sbjs_current'] ) ) {
			$sbjs_current = $this->parse_string_to_array( $this->session['sbjs_current'] );

			$this->attribution->set_source_type( $sbjs_current['typ'] );

			if ( $sbjs_current['typ'] === 'utm' ) {

				if ( isset( $sbjs_current['cmp'] ) ) {
					$this->attribution->set_utm_campaign( $sbjs_current['cmp'] );
				}
				if ( isset( $sbjs_current['src'] ) ) {
					$this->attribution->set_utm_source( $sbjs_current['src'] );
				}
				if ( isset( $sbjs_current['medium'] ) ) {
					$this->attribution->set_utm_medium( $sbjs_current['medium'] );
				}
				if ( isset( $sbjs_current['cnt'] ) ) {
					$this->attribution->set_utm_content( $sbjs_current['cnt'] );
				}
				if ( isset( $sbjs_current['id'] ) ) {
					$this->attribution->set_utm_id( $sbjs_current['id'] );
				}
				if ( isset( $sbjs_current['term'] ) ) {
					$this->attribution->set_utm_term( $sbjs_current['term'] );
				}
				if ( isset( $sbjs_current['plt'] ) ) {
					$this->attribution->set_utm_source_platform( $sbjs_current['plt'] );
				}
				if ( isset( $sbjs_current['fmt'] ) ) {
					$this->attribution->set_utm_creative_format( $sbjs_current['fmt'] );
				}
				if ( isset( $sbjs_current['tct'] ) ) {
					$this->attribution->set_utm_marketing_tactic( $sbjs_current['tct'] );
				}
			}
		}

		if ( isset( $this->session['sbjs_current_add'] ) ) {
			$sbjs_current_add = $this->parse_string_to_array( $this->session['sbjs_current_add'] );
			if ( isset( $sbjs_current_add['rf'] ) ) {
				$this->attribution->set_referrer( $sbjs_current_add['rf'] );
			}

			if ( isset( $sbjs_current_add['ep'] ) ) {
				$this->attribution->set_session_entry( $sbjs_current_add['ep'] );
			}
		}

		if ( isset( $this->session['sbjs_session'] ) ) {
			$sbjs_session = $this->parse_string_to_array( $this->session['sbjs_session'] );
			if ( isset( $sbjs_session['pgs'] ) ) {
				$this->attribution->set_session_start_time( $sbjs_session['pgs'] );
			}
		}

		if ( isset( $this->session['sbjs_udata'] ) ) {
			$sbjs_udata = $this->parse_string_to_array( $this->session['sbjs_udata'] );

			if ( isset( $sbjs_udata['vst'] ) ) {
				$this->attribution->set_session_count( $sbjs_udata['vst'] );
			}
			if ( isset( $sbjs_udata['uag'] ) ) {
				$this->attribution->set_user_agent( $sbjs_udata['uag'] );
			}
		}
	}

	private function override_utm_by_inpost(): void {

		$this->attribution->set_source_type( 'utm' );
		$this->attribution->set_utm_source( 'InPost Pay' );

		$this->attribution->set_utm_campaign( 'API' );

		$this->attribution->set_utm_medium( 'aplikacja (InPost Mobile)' );
		$this->attribution->set_utm_content( '' );
		$this->attribution->set_utm_id( '' );
		$this->attribution->set_utm_term( '' );
		$this->attribution->set_utm_source_platform( '' );
		$this->attribution->set_utm_creative_format( '' );
		$this->attribution->set_utm_marketing_tactic( '' );
	}

	private function parse_string_to_array( $string ): array {
		$pairs = explode( '|||', $string );

		$result = array();

		foreach ( $pairs as $pair ) {
			[ $key, $value ] = explode( '=', $pair );
			$result[ $key ]  = ( $value === '(none)' ) ? null : $value;
		}

		return $result;
	}

	public static function is_order_attribution_feature_enabled(): bool {
		$custom_feature = get_option( 'woocommerce_feature_order_attribution_enabled', 'no' );

		return $custom_feature === 'yes';
	}
}
