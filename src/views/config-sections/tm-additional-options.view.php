<?php
// Without zone_id because it is a global parameter
$shippingMethodAvailability = inpost_pay()
	->shipping_cost_settings()
	->getCheckShippingAvailabilityField();
?>

<div class="consent-item">
	<h3 class="mt-2 mb-1 text-bold">
		<?php _e(
			"Additional options:",
			"inpost-pay"
		); ?>
	</h3>
	<table class="net-transport-price-table">
		<tr>
			<td class="form-group form-group--row">
				<div class="input-tooltip-wrapper">
					<img src="<?php echo plugin_dir_url(
											 __FILE__
										 ) .
										 "../../../assets/img/tooltip.svg"; ?>"
						 alt="">
					<div class="input-tooltip-box">
						<p><?php _e(
								"Leave this option disabled if you are not using the official InPost logistic plugin",
								"inpost-pay"
							); ?></p>
					</div>
				</div>
				<p><?php _e( "Check shipping method availability for products based on InPost logistic plugin settings",
						"inpost-pay" ); ?></p>
				<div class="toggleWrapper">
					<input class="mobileToggle" type="checkbox"
						   id="izi_check_shipping_availability"
						   name="<?php esc_attr_e( $shippingMethodAvailability->get_field_name() ) ?>"
						   value="1" <?= $shippingMethodAvailability->get_bool()
						? "checked"
						: "" ?>>
					<label for="izi_check_shipping_availability"></label>
				</div>
			</td>
		</tr>
	</table>
</div>
