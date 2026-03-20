<?php

namespace Ilabs\Inpost_Pay\Integration\Shipping;

/**
 * @desc WP_Desk
 */
class WoocommercePaczkomatyInpostIntegration extends AbstractShippingMethodIntegration
	implements ShippingMethodIntegrationInterface {

	public function __construct( string $iziDeliveryMethodId ) {
		$this->iziDeliveryMethodId = $iziDeliveryMethodId;
	}

	public static function isEasyPack( $delivery_method ) {
		return strpos( $delivery_method, 'flexible_shipping_single' );
	}
}
