<?php

namespace Ilabs\Inpost_Pay\WooCommerce\Mappers\Order;

use Ilabs\Inpost_Pay\Lib\item\order\AccountInfo;
use Ilabs\Inpost_Pay\Lib\item\order\ClientAddress;
use Ilabs\Inpost_Pay\Lib\item\order\PhoneNumber;
use Ilabs\Inpost_Pay\Lib\helpers\HPOSHelper;

class AccountInfoMapper {
	private $order;
	private $HPOSHelper;
	private $originalOrderId;

	public function __construct( $order, HPOSHelper $HPOSHelper, $originalOrderId ) {
		$this->order           = $order;
		$this->HPOSHelper      = $HPOSHelper;
		$this->originalOrderId = $originalOrderId;
	}

	public function map(): AccountInfo {
		$data = array();
		try {
			$data = unserialize( get_post_meta( $this->originalOrderId, 'inpost_account_info', true ) );
			if ( empty( $data ) || empty( $data->phone_number ) ) {
				$data = unserialize( $this->HPOSHelper->get_meta( 'inpost_account_info' ) );
			}
		} catch ( \Exception $e ) {
			// Silent exception
		}

		$accountInfo = new AccountInfo();

		$phoneNumber   = $this->mapPhoneNumber( $data );
		$clientAddress = $this->mapClientAddress( $data );

		$accountInfo->set_name( $data->name ?? '' );
		$accountInfo->set_surname( $data->surname ?? '' );
		$accountInfo->set_phone_number( $phoneNumber );
		$accountInfo->set_mail( $data->mail ?? '' );
		$accountInfo->set_client_address( $clientAddress );

		return $accountInfo;
	}

	private function mapPhoneNumber( $data ): PhoneNumber {
		$phoneNumber = new PhoneNumber();
		$phoneNumber->set_country_prefix(
			isset( $data->phone_number->country_prefix ) && $data->phone_number->country_prefix
				? $data->phone_number->country_prefix
				: ''
		);
		$phoneNumber->set_phone(
			isset( $data, $data->phone_number, $data->phone_number->phone )
				? $data->phone_number->phone
				: ''
		);

		return $phoneNumber;
	}


	private function mapClientAddress( $data ): ClientAddress {
		$clientAddress = new ClientAddress();
		$clientAddress->set_country_code(
			isset( $data, $data->client_address, $data->client_address->country_code )
				? $data->client_address->country_code
				: 'PL'
		);
		$clientAddress->set_address(
			isset( $data, $data->client_address, $data->client_address->address )
				? $data->client_address->address
				: ''
		);
		$clientAddress->set_city(
			isset( $data, $data->client_address, $data->client_address->city )
				? $data->client_address->city
				: ''
		);
		$clientAddress->set_postal_code(
			isset( $data, $data->client_address, $data->client_address->postal_code )
				? $data->client_address->postal_code
				: ''
		);

		return $clientAddress;
	}
}
