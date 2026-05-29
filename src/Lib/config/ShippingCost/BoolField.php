<?php
/**
 * Boolean configuration field.
 *
 * @package Ilabs\Inpost_Pay\Lib\config\ShippingCost
 */

declare( strict_types=1 );

namespace Ilabs\Inpost_Pay\Lib\config\ShippingCost;

use Ilabs\Inpost_Pay\Lib\form\AbstractOption;
use Ilabs\Inpost_Pay\Lib\form\FormFieldInterface;
use Ilabs\Inpost_Pay\Lib\form\Select;

/**
 * Class BoolField
 *
 * Represents a boolean (yes/no) WordPress option with label and tooltip.
 */
final class BoolField extends AbstractOption {

	private bool $default_value;
	private string $label;
	private string $tooltip;

	/**
	 * Creates a new BoolField instance.
	 *
	 * @param string $id      Option ID.
	 * @param string $label   Field label.
	 * @param string $tooltip Field tooltip.
	 * @param bool   $default_value Default value.
	 *
	 * @return self
	 */
	public static function instance(
		string $id,
		string $label,
		string $tooltip,
		bool $default_value = true
	): self {
		return new self( $id, $label, $tooltip, $default_value );
	}

	/**
	 * Constructor.
	 *
	 * @param string $id      Option ID.
	 * @param string $label   Field label.
	 * @param string $tooltip Field tooltip.
	 * @param bool   $default_value Default value.
	 */
	public function __construct(
		string $id,
		string $label,
		string $tooltip,
		bool $default_value = true
	) {
		$this->default_value = $default_value;
		$this->label         = $label;
		$this->tooltip       = $tooltip;
		parent::__construct( $id );
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
				'default' => $this->default_value,
			)
		);
	}

	/**
	 * Returns the field label.
	 *
	 * @return string
	 */
	public function get_label(): string {
		return $this->label;
	}

	/**
	 * Returns the field tooltip.
	 *
	 * @return string
	 */
	public function get_tooltip(): string {
		return $this->tooltip;
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
