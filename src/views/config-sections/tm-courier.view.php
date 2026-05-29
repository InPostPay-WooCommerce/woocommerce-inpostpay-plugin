<?php
/**
 * Transport method courier config section view.
 *
 * @package InPost_Pay
 */

use function Ilabs\Inpost_Pay\inpost_pay;

/**
 * Zone ID.
 *
 * @var int|null $zone_id
 */

$cod_courier_settings_group = inpost_pay()
	->shipping_cost_settings( $zone_id )
	->get_cod_courier_settings_group();

$courier_settings_group = inpost_pay()
	->shipping_cost_settings( $zone_id )
	->get_courier_settings_group();

$courier_cod_group_is_active_field = inpost_pay()
	->shipping_cost_settings( $zone_id )
	->get_cod_courier_settings_group()
	->get_is_active_field();

$courier_cod_option_cost_mapping_approach_obj = inpost_pay()
	->shipping_cost_settings( $zone_id )
	->get_cod_courier_settings_group()
	->get_option_cost_mapping_approach_obj();

$courier_cod_option_cost_mapping_approach_checked_val_fee    =
	$courier_cod_option_cost_mapping_approach_obj::OPTION_COST_MAPPING_APPROACH_FEE;
$courier_cod_option_cost_mapping_approach_checked_val_method =
	$courier_cod_option_cost_mapping_approach_obj::OPTION_COST_MAPPING_APPROACH_SHIPPING_METHOD;
$courier_cod_option_cost_mapping_approach_val                = $courier_cod_option_cost_mapping_approach_obj->get();
?>

<div class="consent-item">
	<h2 class="izi-transport-price-heading-secondary">
		<?php esc_html_e( 'Courier:', 'inpost-pay' ); ?>
	</h2>

	<div class="input-wrapper">
		<div class="form-group">
			<label class="mb-05">
				<?php esc_html_e( 'Carrier mapping', 'inpost-pay' ); ?>
			</label>
			<div class="input-tooltip">
				<div>
					<select name="<?php echo esc_attr( $courier_settings_group->get_shipping_method_field()->get_field_name() ); ?>">
						<option value="0">
							<?php esc_html_e( 'Select', 'inpost-pay' ); ?>
						</option>
						<?php
						$selected_option = (string) $courier_settings_group->get_shipping_method_field()->get();

						foreach ( $available_shipping_methods as $value => $label ) {
							$selected = ( $value === $selected_option ) ? 'selected' : '';
							echo '<option ' . esc_attr( $selected ) . ' value="' . esc_attr( $value ) . '">' . esc_html( $label ) . '</option>';
						}
						?>
					</select>
				</div>
				<div class="input-tooltip-wrapper">
					<img
						src="<?php echo esc_url( plugin_dir_url( __FILE__ ) . '../../../assets/img/tooltip.svg' ); ?>"
						alt=""
					>
					<div class="input-tooltip-box">
						<p>
							<?php esc_html_e( 'Determines which shipping method is to be associated', 'inpost-pay' ); ?>
						</p>
					</div>
				</div>
			</div>
		</div>
	</div>

	<div class="izi-group-courier-cod izi-transport-group">
		<div class="izi-group-checkbox-wrapper izi-transport-group-opacity">
			<input
				type="checkbox"
				id="<?php echo esc_attr( $courier_cod_group_is_active_field->get_field_name() ); ?>"
				name="<?php echo esc_attr( $courier_cod_group_is_active_field->get_field_name() ); ?>"
				value="1"
				<?php checked( $courier_cod_group_is_active_field->get_bool( true ) ); ?>
			>

			<label
				class="mr-0 pr-0 text-bold"
				for="<?php echo esc_attr( $courier_cod_group_is_active_field->get_field_name() ); ?>"
			>
				<?php echo esc_html( $courier_cod_group_is_active_field->get_label() ); ?>
			</label>

			<div class="input-tooltip-wrapper">
				<img
					src="<?php echo esc_url( plugin_dir_url( __FILE__ ) . '../../../assets/img/tooltip.svg' ); ?>"
					alt=""
				>
				<div class="input-tooltip-box">
					<p><?php echo esc_html( $courier_cod_group_is_active_field->get_tooltip() ); ?></p>
				</div>
			</div>
		</div>

		<div class="izi-group-subgroup">
			<div class="input-wrapper">
				<div class="izi-transport-form-group">
					<input
						type="radio"
						class="izi-group-subgroup-radio"
						name="<?php echo esc_attr( $courier_cod_option_cost_mapping_approach_obj->get_field_name() ); ?>"
						value="<?php echo esc_attr( $courier_cod_option_cost_mapping_approach_checked_val_method ); ?>"
						<?php checked( $courier_cod_option_cost_mapping_approach_checked_val_method, $courier_cod_option_cost_mapping_approach_val ); ?>
					>
					<div class="izi-transport-form-subgroup">
						<label>
							<?php esc_html_e( 'Carrier mapping', 'inpost-pay' ); ?>
						</label>
						<div class="input-tooltip izi-transport-form-group">
							<select name="<?php echo esc_attr( $cod_courier_settings_group->get_shipping_method_field()->get_field_name() ); ?>">
								<option value="0">
									<?php esc_html_e( 'Select', 'inpost-pay' ); ?>
								</option>
								<?php
								$selected_option = (string) $cod_courier_settings_group->get_shipping_method_field()->get();

								foreach ( $available_shipping_methods as $value => $label ) {
									$selected = ( $value === $selected_option ) ? 'selected' : '';
									echo '<option ' . esc_attr( $selected ) . ' value="' . esc_attr( $value ) . '">' . esc_html( $label ) . '</option>';
								}
								?>
							</select>
							<div class="input-tooltip-wrapper">
								<img
									src="<?php echo esc_url( plugin_dir_url( __FILE__ ) . '../../../assets/img/tooltip.svg' ); ?>"
									alt=""
								>
								<div class="input-tooltip-box">
									<p>
										<?php esc_html_e( 'Determines which shipping method is to be associated', 'inpost-pay' ); ?>
									</p>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>

			<div class="input-wrapper">
				<div class="izi-transport-form-group">
					<input
						type="radio"
						class="izi-group-subgroup-radio"
						name="<?php echo esc_attr( $courier_cod_option_cost_mapping_approach_obj->get_field_name() ); ?>"
						value="<?php echo esc_attr( $courier_cod_option_cost_mapping_approach_checked_val_fee ); ?>"
						<?php checked( $courier_cod_option_cost_mapping_approach_checked_val_fee, $courier_cod_option_cost_mapping_approach_val ); ?>
					>
					<div class="izi-transport-form-subgroup">
						<label>
							<?php esc_html_e( 'Added fee', 'inpost-pay' ); ?>
						</label>
						<div class="izi-transport-form-subgroup">
							<div class="input-tooltip izi-transport-form-group">
								<input
									type="number"
									step="any"
									inputmode="decimal"
									name="<?php echo esc_attr( $cod_courier_settings_group->get_price_field()->get_field_name() ); ?>"
									value="<?php echo esc_attr( str_replace( ',', '.', (string) $cod_courier_settings_group->get_price_field()->get() ) ); ?>"
								>

								<div class="input-tooltip-wrapper">
									<img
										src="<?php echo esc_url( plugin_dir_url( __FILE__ ) . '../../../assets/img/tooltip.svg' ); ?>"
										alt=""
									>
									<div class="input-tooltip-box">
										<p>
											<?php
											esc_html_e(
												'Additional fee amount field for this shipping option. The amount entered is net and tax will be added depending on tax settings.',
												'inpost-pay'
											);
											?>
										</p>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
