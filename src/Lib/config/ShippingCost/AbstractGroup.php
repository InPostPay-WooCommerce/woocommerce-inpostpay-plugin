<?php
/**
 * Abstract shipping cost group.
 *
 * @package Ilabs\Inpost_Pay\Lib\config\ShippingCost
 */

declare( strict_types=1 );

namespace Ilabs\Inpost_Pay\Lib\config\ShippingCost;

/**
 * Class AbstractGroup
 *
 * Base implementation of GroupInterface providing common shipping cost group behaviour.
 */
abstract class AbstractGroup implements GroupInterface {

	private ?BoolField $is_active_field                              = null;
	private ?OptionCostMappingApproach $option_cost_mapping_approach = null;
	private ?int $zone_id = null;

	/**
	 * Constructor.
	 *
	 * @param int|null $zone_id Optional shipping zone ID.
	 */
	public function __construct( ?int $zone_id = null ) {
		$this->zone_id = $zone_id;
	}

	/**
	 * Returns the unique group identifier.
	 *
	 * @return string
	 */
	public function get_group_id(): string {
		$id = $this->get_delivery_type_code();

		if ( $this->get_delivery_option_code() !== GroupInterface::DELIVERY_OPTION_CODE_NONE ) {
			$id .= '_' . $this->get_delivery_option_code();
		}

		return $id;
	}

	/**
	 * Returns the human-readable delivery option name.
	 *
	 * @return string|null
	 */
	public function get_delivery_option_name(): ?string {
		if ( GroupInterface::DELIVERY_OPTION_CODE_COD === $this->get_delivery_option_code() ) {
			return 'Pobranie';
		}

		if ( GroupInterface::DELIVERY_OPTION_CODE_PWW === $this->get_delivery_option_code() ) {
			return 'Paczka w Weekend';
		}

		return null;
	}

	/**
	 * Returns the option ID for the is-active field.
	 *
	 * @return string
	 */
	abstract protected function get_is_active_field_id(): string;

	/**
	 * Returns the label for the is-active field.
	 *
	 * @return string
	 */
	protected function get_is_active_field_label(): string {
		return __( 'Enabled', 'inpost-pay' );
	}

	/**
	 * Returns the tooltip for the is-active field.
	 *
	 * @return string
	 */
	protected function get_is_active_field_tooltip(): string {
		return '';
	}

	/**
	 * Returns the default value for the is-active field.
	 *
	 * @return bool
	 */
	protected function get_is_active_field_default(): bool {
		return true;
	}

	/**
	 * Returns the is-active boolean field.
	 *
	 * @return BoolField
	 */
	public function get_is_active_field(): BoolField {
		if ( false === $this->is_active_field instanceof BoolField ) {
			$this->init_is_active_field();
		}

		return $this->is_active_field;
	}

	/**
	 * Initialises and registers the is-active field.
	 *
	 * @return void
	 */
	public function init_is_active_field(): void {

		$this->is_active_field = new BoolField(
			$this->get_is_active_field_id(),
			$this->get_is_active_field_label(),
			$this->get_is_active_field_tooltip(),
			$this->get_is_active_field_default()
		);
		$this->is_active_field->init();
	}


	/**
	 * Returns the selected option cost mapping approach value.
	 *
	 * @return string
	 */
	public function get_option_cost_mapping_approach(): string {
		$this->init_option_cost_mapping_approach();
		if ( $this->option_cost_mapping_approach ) {
			return $this->option_cost_mapping_approach->get( OptionCostMappingApproach::OPTION_COST_MAPPING_APPROACH_SHIPPING_METHOD );
		}

		return $this->get_option_cost_mapping_approach_default();
	}

	/**
	 * Returns the option ID for the cost mapping approach field.
	 *
	 * @return string|null
	 */
	public function get_option_cost_mapping_approach_id(): ?string {
		return null;
	}

	/**
	 * Returns the label for the cost mapping approach field.
	 *
	 * @return string
	 */
	protected function get_option_cost_mapping_approach_label(): string {
		return '';
	}

	/**
	 * Returns the tooltip for the cost mapping approach field.
	 *
	 * @return string
	 */
	protected function get_option_cost_mapping_approach_tooltip(): string {
		return '';
	}

	/**
	 * Returns the default value for the cost mapping approach field.
	 *
	 * @return string
	 */
	protected function get_option_cost_mapping_approach_default(): string {
		return OptionCostMappingApproach::OPTION_COST_MAPPING_APPROACH_SHIPPING_METHOD;
	}

	/**
	 * Returns the OptionCostMappingApproach object.
	 *
	 * @return OptionCostMappingApproach|null
	 */
	public function get_option_cost_mapping_approach_obj(): ?OptionCostMappingApproach {
		if ( false === $this->option_cost_mapping_approach instanceof OptionCostMappingApproach ) {
			$this->init_option_cost_mapping_approach();
		}

		return $this->option_cost_mapping_approach;
	}

	/**
	 * Initialises and registers the option cost mapping approach field.
	 *
	 * @return void
	 */
	public function init_option_cost_mapping_approach(): void {
		if ( $this->option_cost_mapping_approach ) {
			return;
		}

		$id      = $this->get_option_cost_mapping_approach_id();
		$label   = $this->get_option_cost_mapping_approach_label();
		$default = $this->get_option_cost_mapping_approach_default();
		$tooltip = $this->get_option_cost_mapping_approach_tooltip();

		if ( $id ) {
			$this->option_cost_mapping_approach = new OptionCostMappingApproach(
				$this->get_option_cost_mapping_approach_id(),
				$this->get_option_cost_mapping_approach_label(),
				$this->get_option_cost_mapping_approach_tooltip(),
				$this->get_option_cost_mapping_approach_default()
			);

			$this->option_cost_mapping_approach->init();
		}
	}

	/**
	 * Returns the shipping zone ID.
	 *
	 * @return int|null
	 */
	public function get_zone_id(): ?int {
		return $this->zone_id;
	}
}
