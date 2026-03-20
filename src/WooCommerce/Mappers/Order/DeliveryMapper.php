<?php

namespace Ilabs\Inpost_Pay\WooCommerce\Mappers\Order;

use Ilabs\Inpost_Pay\Lib\helpers\HPOSHelper;
use Ilabs\Inpost_Pay\Lib\item\order\Delivery;
use Ilabs\Inpost_Pay\Lib\item\order\DeliveryAddress;
use Ilabs\Inpost_Pay\Lib\item\order\Phone;

class DeliveryMapper {
	private \WC_Order $order;
	private HPOSHelper $hpos_helper;

	/**
	 * Constructs a new DeliveryMapper object.
	 *
	 * @param \WC_Order  $order The order to be mapped.
	 * @param HPOSHelper $hpos_helper The HPOS helper object.
	 */
	public function __construct( $order, HPOSHelper $hpos_helper ) {
		$this->order       = $order;
		$this->hpos_helper = $hpos_helper;
	}

	/**
	 * Maps an order to a Delivery object.
	 *
	 * @return Delivery Mapped order as a Delivery object.
	 */
	public function map(): Delivery {
		$delivery      = new Delivery();
		$delivery_cost = $this->hpos_helper->get_meta( 'izi_delivery_cost' );

		$delivery_options = $delivery_cost['delivery_options'] ?? array();

		foreach ( $delivery_options as &$option ) {
			if ( isset( $option['delivery_option_price'] ) ) {
				$option['delivery_option_price']['net']   =
					number_format( (float) $option['delivery_option_price']['net'], 2, '.', '' );
				$option['delivery_option_price']['gross'] =
					number_format( (float) $option['delivery_option_price']['gross'], 2, '.', '' );
				$option['delivery_option_price']['vat']   =
					number_format( (float) $option['delivery_option_price']['vat'], 2, '.', '' );
			}
		}
		unset( $option );

		$delivery->set_delivery_options( $delivery_options );

		$delivery->set_delivery_price(
			array(
				'net'   => wc_format_decimal( $delivery_cost['base_delivery_price']['net'] ),
				'gross' => wc_format_decimal( $delivery_cost['base_delivery_price']['gross'] ),
				'vat'   => wc_format_decimal( $delivery_cost['base_delivery_price']['vat'] ),
			)
		);

		$delivery_type = $this->hpos_helper->get_meta( 'izi_delivery_type_code', true );
		$delivery->set_delivery_type( $delivery_type );

		$inpost_mail = $this->order->get_meta( '_inpost_delivery_mail' );
		$delivery->set_mail( $inpost_mail ?: $this->order->get_billing_email() );

		$delivery->set_phone( $this->map_phone( $this->order->get_shipping_phone() ) );
		$delivery->set_delivery_address( $this->map_delivery_address() );
		$delivery->set_courier_note( $this->order->get_shipping_address_2() );

		$delivery_point = $this->hpos_helper->get_meta( 'delivery_point' );
		if ( $delivery_point ) {
			$delivery->set_delivery_point( $delivery_point );
		}

		return $delivery;
	}

	/**
	 * Maps a telephone number to a Phone object.
	 *
	 * @param string $telephoneNumber The telephone number to map.
	 * @return Phone The mapped telephone number as a Phone object.
	 */
	private function map_phone( $telephoneNumber ): Phone {
		$phone = new Phone();

		$trig_phone = $this->read_phone();
		$phone->set_country_prefix( $trig_phone[0] );
		$phone->set_phone( $trig_phone[1] );

		return $phone;
	}

	/**
	 * Reads the phone number from the order meta data or the billing phone number.
	 *
	 * If the origin_phone_number meta data exists, it will be used to get the phone number.
	 * Otherwise, it will use the billing phone number.
	 *
	 * @return array The phone number as an array of two elements - country prefix and phone number.
	 */
	private function read_phone(): array {
		$phone_number = $this->hpos_helper->get_meta( 'origin_phone_number' );
		if ( $phone_number ) {
			$phone_number = json_decode( $phone_number );

			return array( $phone_number->country_prefix, $phone_number->phone );
		}

		$array = explode( ' ', $this->order->get_billing_phone() );

		return array( array_shift( $array ), implode( ' ', $array ) );
	}

	/**
	 * Maps the shipping address to a DeliveryAddress object.
	 *
	 * @return DeliveryAddress The mapped shipping address as a DeliveryAddress object.
	 */
	private function map_delivery_address(): DeliveryAddress {
		$delivery_address = new DeliveryAddress();

		$delivery_address->set_name(
			$this->order->get_shipping_first_name() . ' ' . $this->order->get_shipping_last_name()
		);
		$delivery_address->set_country_code( $this->order->get_shipping_country() );
		$delivery_address->set_address( $this->order->get_shipping_address_1() );
		$delivery_address->set_city( $this->order->get_shipping_city() );
		$delivery_address->set_postal_code( $this->order->get_shipping_postcode() );

		return $delivery_address;
	}
}
