<?php

declare( strict_types=1 );

namespace Ilabs\Inpost_Pay\Lib\item\order;

use Ilabs\Inpost_Pay\Lib\helpers\JsonSerializationHelper;
use Ilabs\Inpost_Pay\Lib\Item;

class InvoiceDetails extends Item implements \JsonSerializable {
	use JsonSerializationHelper;

	protected ?string $legal_form;
	protected string $country_code;
	protected string $tax_id_prefix;
	protected string $tax_id;
	protected string $company_name;
	protected string $name;
	protected string $surname;
	protected string $city;
	protected string $street;
	protected string $building;
	protected string $flat;
	protected string $postal_code;
	protected string $mail;
	protected string $registration_data_edited;
	protected string $additional_information;

	public function jsonSerialize(): array {
		return $this->autoSerialize();
	}

	public function get_legal_form(): ?string {
		return $this->legal_form;
	}

	public function set_legal_form( ?string $legal_form ): self {
		$this->legal_form = $legal_form;

		return $this;
	}

	public function get_country_code(): string {
		return $this->country_code;
	}

	public function set_country_code( string $country_code ): self {
		$this->country_code = $country_code;

		return $this;
	}

	public function get_tax_id_prefix(): string {
		return $this->tax_id_prefix;
	}

	public function set_tax_id_prefix( string $tax_id_prefix ): self {
		$this->tax_id_prefix = $tax_id_prefix;

		return $this;
	}

	public function get_tax_id(): string {
		return $this->tax_id;
	}

	public function set_tax_id( string $tax_id ): self {
		$this->tax_id = $tax_id;

		return $this;
	}

	public function get_company_name(): string {
		return $this->company_name;
	}

	public function set_company_name( string $company_name ): self {
		$this->company_name = $company_name;

		return $this;
	}

	public function get_name(): string {
		return $this->name;
	}

	public function set_name( string $name ): self {
		$this->name = $name;

		return $this;
	}

	public function get_surname(): string {
		return $this->surname;
	}

	public function set_surname( string $surname ): self {
		$this->surname = $surname;

		return $this;
	}

	public function get_city(): string {
		return $this->city;
	}

	public function set_city( string $city ): self {
		$this->city = $city;

		return $this;
	}

	public function get_street(): string {
		return $this->street;
	}

	public function set_street( string $street ): self {
		$this->street = $street;

		return $this;
	}

	public function get_building(): string {
		return $this->building;
	}

	public function set_building( string $building ): self {
		$this->building = $building;

		return $this;
	}

	public function get_flat(): string {
		return $this->flat;
	}

	public function set_flat( string $flat ): self {
		$this->flat = $flat;

		return $this;
	}

	public function get_postal_code(): string {
		return $this->postal_code;
	}

	public function set_postal_code( string $postal_code ): self {
		$this->postal_code = $postal_code;

		return $this;
	}

	public function get_mail(): string {
		return $this->mail;
	}

	public function set_mail( string $mail ): self {
		$this->mail = $mail;

		return $this;
	}

	public function get_registration_data_edited(): string {
		return $this->registration_data_edited;
	}

	public function set_registration_data_edited( string $registration_data_edited ): self {
		$this->registration_data_edited = $registration_data_edited;

		return $this;
	}

	public function get_additional_information(): string {
		return $this->additional_information;
	}

	public function set_additional_information( string $additional_information ): self {
		$this->additional_information = $additional_information;

		return $this;
	}
}
