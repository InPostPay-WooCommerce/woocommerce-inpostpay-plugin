<?php
/**
 * Transport method APM config section view.
 *
 * @package InPost_Pay
 */

use function Ilabs\Inpost_Pay\inpost_pay;

/**
 * Zone ID.
 *
 * @var int|null $zone_id
 */

$pww_apm_settings_group = inpost_pay()
	->shipping_cost_settings( $zone_id )
	->get_pww_apm_settings_group();

$cod_apm_settings_group = inpost_pay()
	->shipping_cost_settings( $zone_id )
	->get_cod_apm_settings_group();

$apm_settings_group = inpost_pay()
	->shipping_cost_settings( $zone_id )
	->get_apm_settings_group();

$apm_cod_group_is_active_field = inpost_pay()
	->shipping_cost_settings( $zone_id )
	->get_cod_apm_settings_group()
	->get_is_active_field();

$apm_pww_group_is_active_field = inpost_pay()
	->shipping_cost_settings( $zone_id )
	->get_pww_apm_settings_group()
	->get_is_active_field();

$apm_cod_option_cost_mapping_approach_obj = inpost_pay()
	->shipping_cost_settings( $zone_id )
	->get_cod_apm_settings_group()
	->get_option_cost_mapping_approach_obj();

$apm_cod_option_cost_mapping_approach_checked_val_fee    =
	$apm_cod_option_cost_mapping_approach_obj::OPTION_COST_MAPPING_APPROACH_FEE;
$apm_cod_option_cost_mapping_approach_checked_val_method =
	$apm_cod_option_cost_mapping_approach_obj::OPTION_COST_MAPPING_APPROACH_SHIPPING_METHOD;
$apm_cod_option_cost_mapping_approach_val                = $apm_cod_option_cost_mapping_approach_obj->get();

$apm_pww_option_cost_mapping_approach_obj = inpost_pay()
	->shipping_cost_settings( $zone_id )
	->get_pww_apm_settings_group()
	->get_option_cost_mapping_approach_obj();

$apm_pww_option_cost_mapping_approach_checked_val_fee    =
	$apm_pww_option_cost_mapping_approach_obj::OPTION_COST_MAPPING_APPROACH_FEE;
$apm_pww_option_cost_mapping_approach_checked_val_method =
	$apm_pww_option_cost_mapping_approach_obj::OPTION_COST_MAPPING_APPROACH_SHIPPING_METHOD;
$apm_pww_option_cost_mapping_approach_val                = $apm_pww_option_cost_mapping_approach_obj->get();
?>

<div class="consent-item">
	<h2 class="izi-transport-price-heading-secondary">
		<?php esc_html_e( 'Parcel Locker:', 'inpost-pay' ); ?>
	</h2>

	<div class="input-wrapper">
		<div class="form-group">
			<label class="mb-05">
				<?php esc_html_e( 'Carrier mapping', 'inpost-pay' ); ?>
			</label>
			<div class="input-tooltip">
				<div>
					<select name="<?php echo esc_attr( $apm_settings_group->get_shipping_method_field()->get_field_name() ); ?>">
						<option value="0">
							<?php esc_html_e( 'Select', 'inpost-pay' ); ?>
						</option>
						<?php
						$selected_option = (string) $apm_settings_group->get_shipping_method_field()->get();

						foreach ( $available_shipping_methods as $value => $label ) {
							$selected = ( (string) $value === $selected_option ) ? 'selected' : '';
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

	<div class="izi-group-apm-cod izi-transport-group mb-2">
		<div class="izi-group-checkbox-wrapper izi-transport-group-opacity">
			<input
				class="mobileToggle"
				type="checkbox"
				id="<?php echo esc_attr( $apm_cod_group_is_active_field->get_field_name() ); ?>"
				name="<?php echo esc_attr( $apm_cod_group_is_active_field->get_field_name() ); ?>"
				value="1"
				<?php checked( $apm_cod_group_is_active_field->get_bool( true ) ); ?>
			>

			<label
				class="mr-0 pr-0 text-bold"
				for="<?php echo esc_attr( $apm_cod_group_is_active_field->get_field_name() ); ?>"
			>
				<?php echo esc_html( $apm_cod_group_is_active_field->get_label() ); ?>
			</label>

			<div class="input-tooltip-wrapper">
				<img
					src="<?php echo esc_url( plugin_dir_url( __FILE__ ) . '../../../assets/img/tooltip.svg' ); ?>"
					alt=""
				>
				<div class="input-tooltip-box">
					<p><?php echo esc_html( $apm_cod_group_is_active_field->get_tooltip() ); ?></p>
				</div>
			</div>
		</div>

		<div class="izi-group-subgroup">
			<div class="input-wrapper">
				<div class="izi-transport-form-group">
					<input
						type="radio"
						class="izi-group-subgroup-radio"
						name="<?php echo esc_attr( $apm_cod_option_cost_mapping_approach_obj->get_field_name() ); ?>"
						value="<?php echo esc_attr( $apm_cod_option_cost_mapping_approach_checked_val_method ); ?>"
						<?php checked( $apm_cod_option_cost_mapping_approach_checked_val_method, $apm_cod_option_cost_mapping_approach_val ); ?>
					>
					<div class="izi-transport-form-subgroup">
						<label>
							<?php esc_html_e( 'Carrier mapping', 'inpost-pay' ); ?>
						</label>
						<div class="input-tooltip izi-transport-form-group">
							<select name="<?php echo esc_attr( $cod_apm_settings_group->get_shipping_method_field()->get_field_name() ); ?>">
								<option value="0">
									<?php esc_html_e( 'Select', 'inpost-pay' ); ?>
								</option>
								<?php
								$selected_option = (string) $cod_apm_settings_group->get_shipping_method_field()->get();

								foreach ( $available_shipping_methods as $value => $label ) {
									$selected = ( (string) $value === $selected_option ) ? 'selected' : '';
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
		</div>

		<div class="izi-group-subgroup">
			<div class="input-wrapper">
				<div class="izi-transport-form-group">
					<input
						type="radio"
						class="izi-group-subgroup-radio"
						name="<?php echo esc_attr( $apm_cod_option_cost_mapping_approach_obj->get_field_name() ); ?>"
						value="<?php echo esc_attr( $apm_cod_option_cost_mapping_approach_checked_val_fee ); ?>"
						<?php checked( $apm_cod_option_cost_mapping_approach_checked_val_fee, $apm_cod_option_cost_mapping_approach_val ); ?>
					>
					<div class="izi-transport-form-subgroup">
						<label><?php esc_html_e( 'Added fee', 'inpost-pay' ); ?></label>
						<div class="input-tooltip izi-transport-form-group">
							<input
								type="number"
								name="<?php echo esc_attr( $cod_apm_settings_group->get_price_field()->get_field_name() ); ?>"
								step="any"
								inputmode="decimal"
								value="<?php echo esc_attr( str_replace( ',', '.', (string) $cod_apm_settings_group->get_price_field()->get() ) ); ?>"
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

	<div class="izi-group-apm-pww izi-transport-group">
		<div class="izi-group-checkbox-wrapper izi-transport-group-opacity">
			<input
				class="mobileToggle"
				type="checkbox"
				id="<?php echo esc_attr( $apm_pww_group_is_active_field->get_field_name() ); ?>"
				name="<?php echo esc_attr( $apm_pww_group_is_active_field->get_field_name() ); ?>"
				value="1"
				<?php checked( $apm_pww_group_is_active_field->get_bool( true ) ); ?>
			>

			<label
				class="mr-0 pr-0 text-bold"
				for="<?php echo esc_attr( $apm_pww_group_is_active_field->get_field_name() ); ?>"
			>
				<?php echo esc_html( $apm_pww_group_is_active_field->get_label() ); ?>
			</label>

			<div class="input-tooltip-wrapper">
				<img
					src="<?php echo esc_url( plugin_dir_url( __FILE__ ) . '../../../assets/img/tooltip.svg' ); ?>"
					alt=""
				>
				<div class="input-tooltip-box">
					<p><?php echo esc_html( $apm_pww_group_is_active_field->get_tooltip() ); ?></p>
				</div>
			</div>
		</div>

		<div class="izi-group-subgroup">
			<div class="input-wrapper">
				<div class="izi-transport-form-group">
					<input
						type="radio"
						class="izi-group-subgroup-radio"
						name="<?php echo esc_attr( $apm_pww_option_cost_mapping_approach_obj->get_field_name() ); ?>"
						value="<?php echo esc_attr( $apm_pww_option_cost_mapping_approach_checked_val_method ); ?>"
						<?php checked( $apm_pww_option_cost_mapping_approach_checked_val_method, $apm_pww_option_cost_mapping_approach_val ); ?>
					>
					<div class="izi-transport-form-subgroup">
						<label>
							<?php esc_html_e( 'Carrier mapping', 'inpost-pay' ); ?>
						</label>
						<div class="input-tooltip izi-transport-form-group">
							<select name="<?php echo esc_attr( $pww_apm_settings_group->get_shipping_method_field()->get_field_name() ); ?>">
								<option value="0">
									<?php esc_html_e( 'Select', 'inpost-pay' ); ?>
								</option>
								<?php
								$selected_option = (string) $pww_apm_settings_group->get_shipping_method_field()->get();

								foreach ( $available_shipping_methods as $value => $label ) {
									$selected = ( (string) $value === $selected_option ) ? 'selected' : '';
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
		</div>

		<div class="izi-group-subgroup">
			<div class="input-wrapper">
				<div class="izi-transport-form-group">
					<input
						type="radio"
						class="izi-group-subgroup-radio"
						name="<?php echo esc_attr( $apm_pww_option_cost_mapping_approach_obj->get_field_name() ); ?>"
						value="<?php echo esc_attr( $apm_pww_option_cost_mapping_approach_checked_val_fee ); ?>"
						<?php checked( $apm_pww_option_cost_mapping_approach_checked_val_fee, $apm_pww_option_cost_mapping_approach_val ); ?>
					>
					<div class="izi-transport-form-subgroup">
						<label><?php esc_html_e( 'Added fee', 'inpost-pay' ); ?></label>
						<div class="input-tooltip izi-transport-form-group">
							<input
								type="number"
								name="<?php echo esc_attr( $pww_apm_settings_group->get_price_field()->get_field_name() ); ?>"
								step="any"
								inputmode="decimal"
								value="<?php echo esc_attr( str_replace( ',', '.', (string) $pww_apm_settings_group->get_price_field()->get() ) ); ?>"
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

		<div class="izi-transport-form-group izi-date-time-group">
			<div class="izi-transport-form-subgroup">
				<label>
					<?php esc_html_e( 'Available from', 'inpost-pay' ); ?>
				</label>
				<div class="input-tooltip d-flex-align-center">
					<select name="<?php echo esc_attr( $pww_apm_settings_group->get_available_from_day_field()->get_field_name() ); ?>">
						<?php
						$selected_option = (string) $pww_apm_settings_group->get_available_from_day_field()->get();

						foreach ( $days_of_week as $value => $label ) {
							$selected = ( (string) $value === $selected_option ) ? 'selected' : '';
							echo '<option ' . esc_attr( $selected ) . ' value="' . esc_attr( $value ) . '">' . esc_html( $label ) . '</option>';
						}
						?>
					</select>

					<select name="<?php echo esc_attr( $pww_apm_settings_group->get_available_from_hour_field()->get_field_name() ); ?>">
						<?php
						$selected_option = (string) $pww_apm_settings_group->get_available_from_hour_field()->get();

						foreach ( $hours_of_day as $value => $label ) {
							$selected = ( (string) $value === $selected_option ) ? 'selected' : '';
							echo '<option ' . esc_attr( $selected ) . ' value="' . esc_attr( $value ) . '">' . esc_html( $label ) . ':00</option>';
						}
						?>
					</select>
				</div>
			</div>

			<div class="izi-transport-form-subgroup">
				<label>
					<?php esc_html_e( 'Available to', 'inpost-pay' ); ?>
				</label>
				<div class="input-tooltip d-flex-align-center">
					<select name="<?php echo esc_attr( $pww_apm_settings_group->get_available_to_day_field()->get_field_name() ); ?>">
						<?php
						$selected_option = (string) $pww_apm_settings_group->get_available_to_day_field()->get();

						foreach ( $days_of_week as $value => $label ) {
							$selected = ( (string) $value === $selected_option ) ? 'selected' : '';
							echo '<option ' . esc_attr( $selected ) . ' value="' . esc_attr( $value ) . '">' . esc_html( $label ) . '</option>';
						}
						?>
					</select>

					<select name="<?php echo esc_attr( $pww_apm_settings_group->get_available_to_hour_field()->get_field_name() ); ?>">
						<?php
						$selected_option = (string) $pww_apm_settings_group->get_available_to_hour_field()->get();

						foreach ( $hours_of_day as $value => $label ) {
							$selected = ( (string) $value === $selected_option ) ? 'selected' : '';
							echo '<option ' . esc_attr( $selected ) . ' value="' . esc_attr( $value ) . '">' . esc_html( $label ) . ':00</option>';
						}
						?>
					</select>
				</div>
			</div>
		</div>
	</div>
</div>

