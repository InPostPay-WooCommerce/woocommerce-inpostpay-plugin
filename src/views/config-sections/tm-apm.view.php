<?php

/**
 * @var $zone_id int|null
 */

$pwwApmSettingsGroup = inpost_pay()
	->shipping_cost_settings( $zone_id )
	->getPwwApmSettingsGroup();

$codApmSettingsGroup = inpost_pay()
	->shipping_cost_settings( $zone_id )
	->getCodApmSettingsGroup();

$apmSettingsGroup = inpost_pay()
	->shipping_cost_settings( $zone_id )
	->getApmSettingsGroup();

$apmCodGroupIsActiveField = inpost_pay()
	->shipping_cost_settings( $zone_id )
	->getCodApmSettingsGroup()->getIsActiveField();

$apmPwwGroupIsActiveField = inpost_pay()
	->shipping_cost_settings( $zone_id )
	->getPwwApmSettingsGroup()->getIsActiveField();


$apmCodOptionCostMappingApproachObj = inpost_pay()
	->shipping_cost_settings( $zone_id )
	->getCodApmSettingsGroup()->getOptionCostMappingApproachObj();

$apmCodOptionCostMappingApproachCheckedValFee    = $apmCodOptionCostMappingApproachObj::OPTION_COST_MAPPING_APPROACH_FEE;
$apmCodOptionCostMappingApproachCheckedValMethod = $apmCodOptionCostMappingApproachObj::OPTION_COST_MAPPING_APPROACH_SHIPPING_METHOD;
$apmCodOptionCostMappingApproachVal              = $apmCodOptionCostMappingApproachObj->get();


$apmPwwOptionCostMappingApproachObj = inpost_pay()
	->shipping_cost_settings( $zone_id )
	->getPwwApmSettingsGroup()->getOptionCostMappingApproachObj();

$apmPwwOptionCostMappingApproachCheckedValFee    = $apmPwwOptionCostMappingApproachObj::OPTION_COST_MAPPING_APPROACH_FEE;
$apmPwwOptionCostMappingApproachCheckedValMethod = $apmPwwOptionCostMappingApproachObj::OPTION_COST_MAPPING_APPROACH_SHIPPING_METHOD;
$apmPwwOptionCostMappingApproachVal              = $apmPwwOptionCostMappingApproachObj->get();

?>


<div class="consent-item">
	<h2 class="izi-transport-price-heading-secondary">
		<?php
		_e(
			"Parcel Locker:",
			"inpost-pay"
		); ?>
	</h2>

	<div class="input-wrapper">
		<div class="form-group">
			<label class="mb-05">
				<?php _e(
					"Carrier mapping",
					"inpost-pay"
				); ?>
			</label>
			<div class="input-tooltip">
				<div>
					<select
						name="<?php echo esc_attr( $apmSettingsGroup->getShippingMethodField()
																	->get_field_name() ) ?>">
						<option value="0">
							<?php _e( "Select", "inpost-pay" ); ?>
						</option>
						<?php
						$selectedOption = esc_attr(
							$apmSettingsGroup->getShippingMethodField()
											 ->get()
						);
						foreach (
							$availableShippingMethods
							as $value => $label
						) {
							$selected =
								$value == $selectedOption
									? "selected"
									: "";
							echo "<option {$selected} value='{$value}'>{$label}</option>";
						}
						?>
					</select>
				</div>
				<div class="input-tooltip-wrapper">
					<img src="<?php echo plugin_dir_url(
											 __FILE__
										 ) .
										 "../../../assets/img/tooltip.svg"; ?>"
						 alt="">
					<div class="input-tooltip-box">
						<p><?php _e(
								"Determines which shipping method is to be associated",
								"inpost-pay"
							); ?></p>
					</div>
				</div>
			</div>
		</div>
	</div>

	</table>

	<div class="izi-group-apm-cod izi-transport-group mb-2">
		<div class="izi-group-checkbox-wrapper izi-transport-group-opacity">
			<input class="mobileToggle" type="checkbox"
				   id="<?php esc_attr_e( $apmCodGroupIsActiveField->get_field_name() ); ?>"
				   name="<?php esc_attr_e( $apmCodGroupIsActiveField->get_field_name() ); ?>"
				   value="1" <?= $apmCodGroupIsActiveField->get_bool( true )
				? "checked"
				: "" ?>>

			<label
				class="mr-0 pr-0 text-bold"
				for="<?php esc_attr_e( $apmCodGroupIsActiveField->get_field_name() ); ?>"><?php esc_attr_e( $apmCodGroupIsActiveField->get_label() ); ?></label>

			<div class="input-tooltip-wrapper">
				<img src="<?php echo plugin_dir_url(
										 __FILE__
									 ) .
									 "../../../assets/img/tooltip.svg"; ?>"
					 alt="">
				<div class="input-tooltip-box">
					<p><?php esc_attr_e( $apmCodGroupIsActiveField->get_tooltip() ) ?></p>
				</div>
			</div>
		</div>
		<div class="izi-group-subgroup">
			<div class="input-wrapper">
				<div class="izi-transport-form-group">
					<input type="radio"
						   class="izi-group-subgroup-radio"
						   name="<?php echo esc_attr( $apmCodOptionCostMappingApproachObj->get_field_name() ) ?>"
						   value="<?php echo $apmCodOptionCostMappingApproachCheckedValMethod ?>"<?php if ( $apmCodOptionCostMappingApproachCheckedValMethod === $apmCodOptionCostMappingApproachVal ): ?> CHECKED<?php endif; ?> >
					<div class="izi-transport-form-subgroup">
						<label>
							<?php _e(
								"Carrier mapping",
								"inpost-pay"
							); ?>
						</label>
						<div class="input-tooltip izi-transport-form-group">
							<select
								name="<?php echo esc_attr( $codApmSettingsGroup->getShippingMethodField()
																			   ->get_field_name() ) ?>">
								<option value="0">
									<?php _e( "Select", "inpost-pay" ); ?>
								</option>
								<?php
								$selectedOption = esc_attr(
									$codApmSettingsGroup->getShippingMethodField()
														->get()
								);
								foreach (
									$availableShippingMethods
									as $value => $label
								) {
									$selected =
										$value == $selectedOption
											? "selected"
											: "";
									echo "<option {$selected} value='{$value}'>{$label}</option>";
								}
								?>
							</select>
							<div class="input-tooltip-wrapper">
								<img src="<?php echo plugin_dir_url(
														 __FILE__
													 ) .
													 "../../../assets/img/tooltip.svg"; ?>"
									 alt="">
								<div class="input-tooltip-box">
									<p><?php _e(
											"Determines which shipping method is to be associated",
											"inpost-pay"
										); ?></p>
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
					<input type="radio"
						   class="izi-group-subgroup-radio"
						   name="<?php echo esc_attr( $apmCodOptionCostMappingApproachObj->get_field_name() ) ?>"
						   value="<?php echo $apmCodOptionCostMappingApproachCheckedValFee ?>"<?php if ( $apmCodOptionCostMappingApproachCheckedValFee === $apmCodOptionCostMappingApproachVal ): ?> CHECKED<?php endif; ?> >
					<div class="izi-transport-form-subgroup">
						<label><?php _e( "Added fee", "inpost-pay" ); ?></label>
						<div class="input-tooltip izi-transport-form-group">
							<input type="number"
								   name="<?php echo esc_attr( $codApmSettingsGroup->getPriceField()
																				  ->get_field_name() ) ?>"
								   step='any'
								   inputmode='decimal'
								   value="<?= esc_attr( str_replace( ',', '.', (string) $codApmSettingsGroup->getPriceField()
																											->get() ) ) ?>">
							<div class="input-tooltip-wrapper">
								<img src="<?php echo plugin_dir_url(
														 __FILE__
													 ) .
													 "../../../assets/img/tooltip.svg"; ?>"
									 alt="">
								<div class="input-tooltip-box">
									<p><?php _e(
											"Additional fee amount field for this shipping option. The amount entered is net and tax will be added depending on tax settings.",
											"inpost-pay"
										); ?></p>
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
			<input class="mobileToggle" type="checkbox"
				   id="<?php esc_attr_e( $apmPwwGroupIsActiveField->get_field_name() ); ?>"
				   name="<?php esc_attr_e( $apmPwwGroupIsActiveField->get_field_name() ); ?>"
				   value="1" <?= $apmPwwGroupIsActiveField->get_bool( true )
				? "checked"
				: "" ?>>

			<label
				class="mr-0 pr-0 text-bold"
				for="<?php esc_attr_e( $apmPwwGroupIsActiveField->get_field_name() ); ?>"><?php esc_attr_e( $apmPwwGroupIsActiveField->get_label() ); ?></label>
			<div class="input-tooltip-wrapper">
				<img src="<?php echo plugin_dir_url(
										 __FILE__
									 ) .
									 "../../../assets/img/tooltip.svg"; ?>"
					 alt="">
				<div class="input-tooltip-box">
					<p><?php esc_attr_e( $apmPwwGroupIsActiveField->get_tooltip() ) ?></p>
				</div>
			</div>
		</div>
		<div class="izi-group-subgroup">
			<div class="input-wrapper">
				<div class="izi-transport-form-group">
					<input type="radio"
						   class="izi-group-subgroup-radio"
						   name="<?php echo esc_attr( $apmPwwOptionCostMappingApproachObj->get_field_name() ) ?>"
						   value="<?php echo $apmPwwOptionCostMappingApproachCheckedValMethod ?>"<?php if ( $apmPwwOptionCostMappingApproachCheckedValMethod === $apmPwwOptionCostMappingApproachVal ): ?> CHECKED<?php endif; ?> >
					<div class="izi-transport-form-subgroup">
						<label>
							<?php _e(
								"Carrier mapping",
								"inpost-pay"
							); ?>
						</label>
						<div class="input-tooltip izi-transport-form-group">
							<select
								name="<?php echo esc_attr( $pwwApmSettingsGroup->getShippingMethodField()
																			   ->get_field_name() ) ?>">
								<option value="0">
									<?php _e( "Select", "inpost-pay" ); ?>
								</option>
								<?php
								$selectedOption = esc_attr(
									$pwwApmSettingsGroup->getShippingMethodField()
														->get()
								);
								foreach (
									$availableShippingMethods
									as $value => $label
								) {
									$selected =
										$value == $selectedOption
											? "selected"
											: "";
									echo "<option {$selected} value='{$value}'>{$label}</option>";
								}
								?>
							</select>
							<div class="input-tooltip-wrapper">
								<img src="<?php echo plugin_dir_url(
														 __FILE__
													 ) .
													 "../../../assets/img/tooltip.svg"; ?>"
									 alt="">
								<div class="input-tooltip-box">
									<p><?php _e(
											"Determines which shipping method is to be associated",
											"inpost-pay"
										); ?></p>
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
					<input type="radio"
						   class="izi-group-subgroup-radio"
						   name="<?php echo esc_attr( $apmPwwOptionCostMappingApproachObj->get_field_name() ) ?>"
						   value="<?php echo $apmPwwOptionCostMappingApproachCheckedValFee ?>"<?php if ( $apmPwwOptionCostMappingApproachCheckedValFee === $apmPwwOptionCostMappingApproachVal ): ?> CHECKED<?php endif; ?> >
					<div class="izi-transport-form-subgroup">
						<label>
							<?php _e( "Added fee", "inpost-pay" ); ?>
						</label>
						<div class="input-tooltip izi-transport-form-group">
							<input type="number"
								   name="<?php echo esc_attr( $pwwApmSettingsGroup->getPriceField()
																				  ->get_field_name() ) ?>"
								   step='any'
								   inputmode='decimal'
								   value="<?= esc_attr( str_replace( ',', '.', (string) $pwwApmSettingsGroup->getPriceField()
																											->get() ) ) ?>">
							<div class="input-tooltip-wrapper">
								<img src="<?php echo plugin_dir_url(
														 __FILE__
													 ) .
													 "../../../assets/img/tooltip.svg"; ?>"
									 alt="">
								<div class="input-tooltip-box">
									<p><?php _e(
											"Additional fee amount field for this shipping option. The amount entered is net and tax will be added depending on tax settings.",
											"inpost-pay"
										); ?></p>
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
					<?php _e( "Available from", "inpost-pay" ); ?>
				</label>
				<div class="input-tooltip d-flex-align-center">
					<select
						name="<?php echo esc_attr( $pwwApmSettingsGroup->getAvailableFromDayField()
																	   ->get_field_name() ) ?>">
						<?php
						$selectedOption = esc_attr(
							$pwwApmSettingsGroup->getAvailableFromDayField()
												->get()
						);
						foreach (
							$daysOfWeek
							as $value => $label
						) {
							$selected =
								$value == $selectedOption
									? "selected"
									: "";
							echo "<option {$selected} value='{$value}'>{$label}</option>";
						}
						?>
					</select>
					<select
						name="<?php echo esc_attr( $pwwApmSettingsGroup->getAvailableFromHourField()
																	   ->get_field_name() ) ?>">
						<?php
						$selectedOption = esc_attr(
							$pwwApmSettingsGroup->getAvailableFromHourField()
												->get()
						);
						foreach (
							$hoursOfDay
							as $value => $label
						) {
							$selected =
								$value == $selectedOption
									? "selected"
									: "";
							echo "<option {$selected} value='{$value}'>{$label}:00</option>";
						}
						?>
					</select>
				</div>
			</div>
			<div class="izi-transport-form-subgroup">
				<label>
					<?php _e( "Available to", "inpost-pay" ); ?>
				</label>
				<div class="input-tooltip d-flex-align-center">
					<select
						name="<?php echo esc_attr( $pwwApmSettingsGroup->getAvailableToDayField()
																	   ->get_field_name() ) ?>">
						<?php
						$selectedOption = esc_attr(
							$pwwApmSettingsGroup->getAvailableToDayField()
												->get()
						);
						foreach (
							$daysOfWeek
							as $value => $label
						) {
							$selected =
								$value == $selectedOption
									? "selected"
									: "";
							echo "<option {$selected} value='{$value}'>{$label}</option>";
						}
						?>
					</select>
					<select
						name="<?php echo esc_attr( $pwwApmSettingsGroup->getAvailableToHourField()
																	   ->get_field_name() ) ?>">
						<?php
						$selectedOption = esc_attr(
							$pwwApmSettingsGroup->getAvailableToHourField()
												->get()
						);
						foreach (
							$hoursOfDay
							as $value => $label
						) {
							$selected =
								$value == $selectedOption
									? "selected"
									: "";
							echo "<option {$selected} value='{$value}'>{$label}:00</option>";
						}
						?>
					</select>
				</div>
			</div>
		</div>
	</div>

</div>
