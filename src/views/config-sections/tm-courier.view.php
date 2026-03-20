<?php

/**
 * @var $zone_id int|null
 */

$codCourierSettingsGroup = inpost_pay()
	->shipping_cost_settings( $zone_id )
	->getCodCourierSettingsGroup();

$courierSettingsGroup = inpost_pay()
	->shipping_cost_settings( $zone_id )
	->getCourierSettingsGroup();

$courierCodGroupIsActiveField = inpost_pay()
	->shipping_cost_settings( $zone_id )
	->getCodCourierSettingsGroup()->getIsActiveField();

$courierCodOptionCostMappingApproachObj = inpost_pay()
	->shipping_cost_settings( $zone_id )
	->getCodCourierSettingsGroup()->getOptionCostMappingApproachObj();

$courierCodOptionCostMappingApproachCheckedValFee    = $courierCodOptionCostMappingApproachObj::OPTION_COST_MAPPING_APPROACH_FEE;
$courierCodOptionCostMappingApproachCheckedValMethod = $courierCodOptionCostMappingApproachObj::OPTION_COST_MAPPING_APPROACH_SHIPPING_METHOD;
$courierCodOptionCostMappingApproachVal              = $courierCodOptionCostMappingApproachObj->get();
?>

<div class="consent-item">
	<h2 class="izi-transport-price-heading-secondary">
		<?php _e(
			"Courier:",
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
						name="<?php echo esc_attr( $courierSettingsGroup->getShippingMethodField()
																		->get_field_name() ) ?>">
						<option>
							<?php _e(
								"Select",
								"inpost-pay"
							); ?>
						</option>
						<?php
						$selectedOption = esc_attr(
							$courierSettingsGroup->getShippingMethodField()
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

	<div class="izi-group-courier-cod izi-transport-group">
		<div class="izi-group-checkbox-wrapper izi-transport-group-opacity">
			<input type="checkbox"
				   id="<?php esc_attr_e( $courierCodGroupIsActiveField->get_field_name() ); ?>"
				   name="<?php esc_attr_e( $courierCodGroupIsActiveField->get_field_name() ); ?>"
				   value="1" <?= $courierCodGroupIsActiveField->get_bool( true )
				? "checked"
				: "" ?>>

			<label
				class="mr-0 pr-0 text-bold"
				for="<?php esc_attr_e( $courierCodGroupIsActiveField->get_field_name() ); ?>"><?php esc_attr_e( $courierCodGroupIsActiveField->get_label() ); ?></label>
			<div class="input-tooltip-wrapper">
				<img src="<?php echo plugin_dir_url(
										 __FILE__
									 ) .
									 "../../../assets/img/tooltip.svg"; ?>"
					 alt="">
				<div class="input-tooltip-box">
					<p><?php esc_attr_e( $courierCodGroupIsActiveField->get_tooltip() ); ?></p>
				</div>
			</div>
		</div>
		<div class="izi-group-subgroup">
			<div class="input-wrapper">
				<div class="izi-transport-form-group">
					<input type="radio"
						   class="izi-group-subgroup-radio"
						   name="<?php echo esc_attr( $courierCodOptionCostMappingApproachObj->get_field_name() ) ?>"
						   value="<?php echo $courierCodOptionCostMappingApproachCheckedValMethod ?>"<?php if ( $courierCodOptionCostMappingApproachCheckedValMethod === $courierCodOptionCostMappingApproachVal ): ?> CHECKED<?php endif; ?> >
					<div class="izi-transport-form-subgroup">
						<label>
							<?php _e(
								"Carrier mapping",
								"inpost-pay"
							); ?>
						</label>
						<div class="input-tooltip izi-transport-form-group">
							<select
								name="<?php echo esc_attr( $codCourierSettingsGroup->getShippingMethodField()
																				   ->get_field_name() ) ?>">
								<option>
									<?php _e(
										"Select",
										"inpost-pay"
									); ?>
								</option>
								<?php
								$selectedOption = esc_attr(
									$codCourierSettingsGroup->getShippingMethodField()
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
			<div class="input-wrapper">
				<div class="izi-transport-form-group">
					<input type="radio"
						   class="izi-group-subgroup-radio"
						   name="<?php echo esc_attr( $courierCodOptionCostMappingApproachObj->get_field_name() ) ?>"
						   value="<?php echo $courierCodOptionCostMappingApproachCheckedValFee ?>"<?php if ( $courierCodOptionCostMappingApproachCheckedValFee === $courierCodOptionCostMappingApproachVal ): ?> CHECKED<?php endif; ?> >
					<div class="izi-transport-form-subgroup">
						<label>
							<?php _e( "Added fee", "inpost-pay" ); ?>
						</label>
						<div class="izi-transport-form-subgroup">
							<div class="input-tooltip izi-transport-form-group">
								<input type="number"
									   step="any"
									   inputmode="decimal"
									   name="<?php echo esc_attr( $codCourierSettingsGroup->getPriceField()
																						  ->get_field_name() ) ?>"
									   value="<?= esc_attr( str_replace( ',', '.', (string) $codCourierSettingsGroup->getPriceField()
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

	</div>

</div>
