<?php

namespace Ilabs\Inpost_Pay\WooCommerce\Mappers\Order;

use Ilabs\Inpost_Pay\Lib\item\order\InvoiceDetails;
use Ilabs\Inpost_Pay\Lib\helpers\HPOSHelper;

class InvoiceDetailsMapper {
	private \WC_Order $order;
	private HPOSHelper $HPOSHelper;

	public function __construct( $order, HPOSHelper $HPOSHelper ) {
		$this->order      = $order;
		$this->HPOSHelper = $HPOSHelper;
	}

	public function map(): InvoiceDetails {
		$invoiceDetails = new InvoiceDetails();

		$legalForm = $this->HPOSHelper->get_meta( 'impost_invoice_legal_form' );
		if ( $legalForm ) {
			$invoiceDetails->set_legal_form( $legalForm );
		}

		$invoiceDetails->set_country_code( $this->HPOSHelper->get_meta( 'impost_invoice_country_code' ) ?: '' );
		$invoiceDetails->set_tax_id_prefix( $this->HPOSHelper->get_meta( 'impost_invoice_tax_id_prefix' ) ?: '' );
		$invoiceDetails->set_tax_id( $this->HPOSHelper->get_meta( 'impost_invoice_tax_id' ) ?: '' );
		$invoiceDetails->set_company_name( $this->HPOSHelper->get_meta( 'impost_invoice_company_name' ) ?: '' );
		$invoiceDetails->set_name( $this->HPOSHelper->get_meta( 'impost_invoice_name' ) ?: '' );
		$invoiceDetails->set_surname( $this->HPOSHelper->get_meta( 'impost_invoice_surname' ) ?: '' );
		$invoiceDetails->set_city( $this->HPOSHelper->get_meta( 'impost_invoice_city' ) ?: '' );
		$invoiceDetails->set_street( $this->HPOSHelper->get_meta( 'impost_invoice_street' ) ?: '' );
		$invoiceDetails->set_building( $this->HPOSHelper->get_meta( 'impost_invoice_building' ) ?: '' );
		$invoiceDetails->set_flat( $this->HPOSHelper->get_meta( 'impost_invoice_flat' ) ?: '' );
		$invoiceDetails->set_postal_code( $this->HPOSHelper->get_meta( 'impost_invoice_postal_code' ) ?: '' );
		$invoiceDetails->set_mail( $this->HPOSHelper->get_meta( 'impost_invoice_mail' ) ?: '' );
		$invoiceDetails->set_registration_data_edited( $this->HPOSHelper->get_meta( 'registration_data_edited' ) ?: '' );
		$invoiceDetails->set_additional_information( $this->HPOSHelper->get_meta( 'impost_invoice_additional_information' ) ?: '' );

		return $invoiceDetails;
	}
}
