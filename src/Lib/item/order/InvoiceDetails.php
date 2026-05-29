<?php
/**
 * Invoice details item.
 *
 * @package Inpost_Pay
 */

declare( strict_types=1 );

namespace Ilabs\Inpost_Pay\Lib\item\order;

use Ilabs\Inpost_Pay\Lib\helpers\JsonSerializationHelper;
use Ilabs\Inpost_Pay\Lib\Item;

/**
 * Represents invoice details.
 */
class InvoiceDetails extends Item implements \JsonSerializable {
	use JsonSerializationHelper;

	/**
	 * Legal form.
	 *
	 * @var string|null
	 */
	protected ?string $legal_form;

	/**
	 * Country code.
	 *
	 * @var string
	 */
	protected string $country_code;

	/**
	 * Tax ID prefix.
	 *
	 * @var string
	 */
	protected string $tax_id_prefix;

	/**
	 * Tax ID.
	 *
	 * @var string
	 */
	protected string $tax_id;

	/**
	 * Company name.
	 *
	 * @var string
	 */
	protected string $company_name;

	/**
	 * Name.
	 *
	 * @var string
	 */
	protected string $name;

	/**
	 * Surname.
	 *
	 * @var string
	 */
	protected string $surname;

	/**
	 * City.
	 *
	 * @var string
	 */
	protected string $city;

	/**
	 * Street.
	 *
	 * @var string
	 */
	protected string $street;

	/**
	 * Building.
	 *
	 * @var string
	 */
	protected string $building;

	/**
	 * Flat.
	 *
	 * @var string
	 */
	protected string $flat;

	/**
	 * Postal code.
	 *
	 * @var string
	 */
	protected string $postal_code;

	/**
	 * Email address.
	 *
	 * @var string
	 */
	protected string $mail;

	/**
	 * Registration data edited flag.
	 *
	 * @var string
	 */
	protected string $registration_data_edited;

	/**
	 * Additional information.
	 *
	 * @var string
	 */
	protected string $additional_information;

	/**
	 * Serialize item to array.
	 *
	 * @return array
	 */
	public function jsonSerialize(): array {
		return $this->auto_serialize();
	}

	/**
	 * Get legal form.
	 *
	 * @return string|null
	 */
	public function get_legal_form(): ?string {
		return $this->legal_form;
	}

	/**
	 * Set legal form.
	 *
	 * @param string|null $legal_form Legal form.
	 *
	 * @return self
	 */
	public function set_legal_form( ?string $legal_form ): self {
		$this->legal_form = $legal_form;

		return $this;
	}

	/**
	 * Get country code.
	 *
	 * @return string
	 */
	public function get_country_code(): string {
		return $this->country_code;
	}

	/**
	 * Set country code.
	 *
	 * @param string $country_code Country code.
	 *
	 * @return self
	 */
	public function set_country_code( string $country_code ): self {
		$this->country_code = $country_code;

		return $this;
	}

	/**
	 * Get tax ID prefix.
	 *
	 * @return string
	 */
	public function get_tax_id_prefix(): string {
		return $this->tax_id_prefix;
	}

	/**
	 * Set tax ID prefix.
	 *
	 * @param string $tax_id_prefix Tax ID prefix.
	 *
	 * @return self
	 */
	public function set_tax_id_prefix( string $tax_id_prefix ): self {
		$this->tax_id_prefix = $tax_id_prefix;

		return $this;
	}

	/**
	 * Get tax ID.
	 *
	 * @return string
	 */
	public function get_tax_id(): string {
		return $this->tax_id;
	}

	/**
	 * Set tax ID.
	 *
	 * @param string $tax_id Tax ID.
	 *
	 * @return self
	 */
	public function set_tax_id( string $tax_id ): self {
		$this->tax_id = $tax_id;

		return $this;
	}

	/**
	 * Get company name.
	 *
	 * @return string
	 */
	public function get_company_name(): string {
		return $this->company_name;
	}

	/**
	 * Set company name.
	 *
	 * @param string $company_name Company name.
	 *
	 * @return self
	 */
	public function set_company_name( string $company_name ): self {
		$this->company_name = $company_name;

		return $this;
	}

	/**
	 * Get name.
	 *
	 * @return string
	 */
	public function get_name(): string {
		return $this->name;
	}

	/**
	 * Set name.
	 *
	 * @param string $name Name.
	 *
	 * @return self
	 */
	public function set_name( string $name ): self {
		$this->name = $name;

		return $this;
	}

	/**
	 * Get surname.
	 *
	 * @return string
	 */
	public function get_surname(): string {
		return $this->surname;
	}

	/**
	 * Set surname.
	 *
	 * @param string $surname Surname.
	 *
	 * @return self
	 */
	public function set_surname( string $surname ): self {
		$this->surname = $surname;

		return $this;
	}

	/**
	 * Get city.
	 *
	 * @return string
	 */
	public function get_city(): string {
		return $this->city;
	}

	/**
	 * Set city.
	 *
	 * @param string $city City.
	 *
	 * @return self
	 */
	public function set_city( string $city ): self {
		$this->city = $city;

		return $this;
	}

	/**
	 * Get street.
	 *
	 * @return string
	 */
	public function get_street(): string {
		return $this->street;
	}

	/**
	 * Set street.
	 *
	 * @param string $street Street.
	 *
	 * @return self
	 */
	public function set_street( string $street ): self {
		$this->street = $street;

		return $this;
	}

	/**
	 * Get building.
	 *
	 * @return string
	 */
	public function get_building(): string {
		return $this->building;
	}

	/**
	 * Set building.
	 *
	 * @param string $building Building.
	 *
	 * @return self
	 */
	public function set_building( string $building ): self {
		$this->building = $building;

		return $this;
	}

	/**
	 * Get flat.
	 *
	 * @return string
	 */
	public function get_flat(): string {
		return $this->flat;
	}

	/**
	 * Set flat.
	 *
	 * @param string $flat Flat.
	 *
	 * @return self
	 */
	public function set_flat( string $flat ): self {
		$this->flat = $flat;

		return $this;
	}

	/**
	 * Get postal code.
	 *
	 * @return string
	 */
	public function get_postal_code(): string {
		return $this->postal_code;
	}

	/**
	 * Set postal code.
	 *
	 * @param string $postal_code Postal code.
	 *
	 * @return self
	 */
	public function set_postal_code( string $postal_code ): self {
		$this->postal_code = $postal_code;

		return $this;
	}

	/**
	 * Get email address.
	 *
	 * @return string
	 */
	public function get_mail(): string {
		return $this->mail;
	}

	/**
	 * Set email address.
	 *
	 * @param string $mail Email address.
	 *
	 * @return self
	 */
	public function set_mail( string $mail ): self {
		$this->mail = $mail;

		return $this;
	}

	/**
	 * Get registration data edited flag.
	 *
	 * @return string
	 */
	public function get_registration_data_edited(): string {
		return $this->registration_data_edited;
	}

	/**
	 * Set registration data edited flag.
	 *
	 * @param string $registration_data_edited Registration data edited flag.
	 *
	 * @return self
	 */
	public function set_registration_data_edited( string $registration_data_edited ): self {
		$this->registration_data_edited = $registration_data_edited;

		return $this;
	}

	/**
	 * Get additional information.
	 *
	 * @return string
	 */
	public function get_additional_information(): string {
		return $this->additional_information;
	}

	/**
	 * Set additional information.
	 *
	 * @param string $additional_information Additional information.
	 *
	 * @return self
	 */
	public function set_additional_information( string $additional_information ): self {
		$this->additional_information = $additional_information;

		return $this;
	}
}
