<?php

namespace Ilabs\Inpost_Pay\Integration\Shipping;

interface ParcelLockerIntegrationInterface {

	public function getFormFieldParcelLockerId(): string;

	public function setParcelLockerId( string $parcelLockerId );

	public function getParcelLockerId(): string;
}
