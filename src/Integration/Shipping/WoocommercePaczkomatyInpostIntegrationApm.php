<?php

namespace Ilabs\Inpost_Pay\Integration\Shipping;

/**
 * @desc WP_Desk
 */
class WoocommercePaczkomatyInpostIntegrationApm extends AbstractShippingMethodIntegration
	implements ParcelLockerIntegrationInterface, ShippingMethodIntegrationInterface {

	const FORM_FIELD_PARCEL_LOCKER_ID = 'paczkomat_id';

	protected string $parcelLockerId;

	public function __construct(
		string $iziDeliveryMethodId,
		string $parcelLockerId
	) {
		$this->iziDeliveryMethodId = $iziDeliveryMethodId;
		$this->parcelLockerId      = $parcelLockerId;
	}

	public static function isEasyPack( $delivery_method ) {
		return strpos( $delivery_method, 'flexible_shipping_single' );
	}

	public function getFormFieldParcelLockerId(): string {
		return self::FORM_FIELD_PARCEL_LOCKER_ID;
	}

	public function setParcelLockerId( string $parcelLockerId ) {
		$this->parcelLockerId = $parcelLockerId;
	}

	public function getParcelLockerId(): string {
		return $this->parcelLockerId;
	}
}
