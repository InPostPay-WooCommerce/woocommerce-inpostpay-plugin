<?php
/**
 * Check shipping availability option.
 *
 * @package Ilabs\Inpost_Pay\Lib\config\ShippingCost
 */

declare( strict_types=1 );

namespace Ilabs\Inpost_Pay\Lib\config\ShippingCost;

use Ilabs\Inpost_Pay\Lib\form\AbstractOption;
use Ilabs\Inpost_Pay\Lib\form\FormFieldInterface;
use Ilabs\Inpost_Pay\Lib\form\LegacyOptionInterface;
use Ilabs\Inpost_Pay\Lib\form\Select;

/**
 * Class CheckShippingAvailability
 *
 * WordPress option that controls whether shipping availability is checked.
 */
final class CheckShippingAvailability extends AbstractOption implements LegacyOptionInterface {

	/**
	 * Returns a new CheckShippingAvailability instance.
	 *
	 * @return self
	 */
	public static function instance(): self {
		return new self();
	}

	/**
	 * Constructor.
	 */
	public function __construct() {

		parent::__construct( 'izi_shipping_check_shipping_availability' );
	}

	/**
	 * Registers the option with WordPress settings API.
	 *
	 * @return void
	 */
	public function init(): void {
		parent::register(
			array(
				'type'    => 'bool',
				'default' => false,
			)
		);
	}

	/**
	 * Returns the field label.
	 *
	 * @return string
	 */
	public function get_label(): string {
		// todo: clarify - this applies to product-level availability.
		return __(
			'Prices and courier shipping availability map with:',
			'inpost-pay'
		);
	}

	/**
	 * Returns the legacy option ID.
	 *
	 * @return string
	 */
	public function get_legacy_option_id(): string {
		return 'izi_check_shipping_availability';
	}

	/**
	 * Returns true as legacy option takes priority.
	 *
	 * @return bool
	 */
	public function has_legacy_option_priority(): bool {
		return true;
	}

	/**
	 * Returns the field tooltip.
	 *
	 * @return string
	 */
	public function get_tooltip(): string {
		return __(
			'Determines which shipping method is to be associated',
			'inpost-pay'
		);
	}

	/**
	 * Returns the form field representation.
	 *
	 * @return FormFieldInterface
	 */
	public function get_form_field(): FormFieldInterface {

		return new Select(
			array(),
			array( $this->get() ),
			array(
				'label'        => $this->get_label(),
				'name'         => $this->get_field_name(),
				'label_class'  => 'label-gray',
				'multiple'     => false,
				'value_as_key' => true,
			)
		);
	}
}
