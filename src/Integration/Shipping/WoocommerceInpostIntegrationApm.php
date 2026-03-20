<?php

namespace Ilabs\Inpost_Pay\Integration\Shipping;

use Ilabs\Inpost_Pay\WooCommerce\WooDeliveryPrice;

/**
 * @desc iLabs
 */
class WoocommerceInpostIntegrationApm extends AbstractShippingMethodIntegration
	implements ParcelLockerIntegrationInterface, ShippingMethodIntegrationInterface
{
	const FORM_FIELD_PARCEL_LOCKER_ID     = 'parcel_machine_id';

	protected string $parcelLockerId;

	public function __construct(
		string $iziDeliveryMethodId,
		string $parcelLockerId
	) {
		$this->iziDeliveryMethodId = $iziDeliveryMethodId;
		$this->parcelLockerId      = $parcelLockerId;
	}

	public static function isEasyPack($delivery_method)
	{
		return strpos($delivery_method, 'easypack_');
	}
	public static function getRatesForDelivery($rate_method, $rate_instance_id, $net_price, $add_tax)
	{
		$easyRate = 0;
		if (class_exists('\InspireLabs\WoocommerceInpost\EasyPack_Helper')) {
			$easyRates = \InspireLabs\WoocommerceInpost\EasyPack_Helper::EasyPack_Helper()->get_saved_method_rates($rate_method, $rate_instance_id);
			foreach ($easyRates as $eRate) {
				$diff = abs(floatval($eRate['cost']) - floatval($net_price));
				if ($eRate['cost'] && $diff <= 0.01) {
					if (!$add_tax) {
						$easyRate = wc_format_decimal($eRate['cost'], 3);
					} else {
						$easyRate = wc_format_decimal(($eRate['cost'] / WooDeliveryPrice::getShippingTaxModifier()), 3);
					}
					break;
				}
			}
		}
		return $easyRate;
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
