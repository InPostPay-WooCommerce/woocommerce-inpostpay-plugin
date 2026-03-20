<div class="consent-item">
	<div class="input-wrapper input-wrapper--center">
		<label>
			<?php _e(
				"Add VAT to the transport price:",
				"inpost-pay"
			); ?>
		</label>
		<div class="input-tooltip d-flex-align-center">
			<select name="izi_transport_add_tax">
				<?php $addShippingTax =
					get_option("izi_transport_add_tax") !==
					false
						? esc_attr(
						get_option(
							"izi_transport_add_tax"
						)
					)
						: "23"; ?>
				<option value="23" <?= $addShippingTax ==
									   "23"
					? "selected"
					: "" ?>>
					<?php _e("Yes", "inpost-pay"); ?>
				</option>
				<option value="0" <?= $addShippingTax == "0"
					? "selected"
					: "" ?>>
					<?php _e("No", "inpost-pay"); ?>
				</option>
			</select>
			<div class="input-tooltip-wrapper">
				<img src="<?php echo plugin_dir_url(
										 __FILE__
									 ) .
									 "../../../assets/img/tooltip.svg"; ?>" alt="">
				<div class="input-tooltip-box">
					<p><?php _e(
							"Determines whether tax should be added to the shipping price",
							"inpost-pay"
						); ?></p>
				</div>
			</div>
		</div>
	</div>
</div>
<hr>
