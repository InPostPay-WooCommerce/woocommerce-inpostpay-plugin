<?php
/**
 * Transport method additional options config section view.
 *
 * @package InPost_Pay
 */

use function Ilabs\Inpost_Pay\inpost_pay;

// Without zone_id because it is a global parameter.
$shipping_method_availability = inpost_pay()
	->shipping_cost_settings()
	->get_check_shipping_availability_field();
?>

<div class="consent-item">
	<h3 class="mt-2 mb-1 text-bold">
		<?php esc_html_e( 'Additional options:', 'inpost-pay' ); ?>
	</h3>

	<table class="net-transport-price-table">
		<tr>
			<td class="form-group form-group--row">
				<div class="input-tooltip-wrapper">
					<img
						src="<?php echo esc_url( plugin_dir_url( __FILE__ ) . '../../../assets/img/tooltip.svg' ); ?>"
						alt=""
					>
					<div class="input-tooltip-box">
						<p>
							<?php
							esc_html_e(
								'Leave this option disabled if you are not using the official InPost logistic plugin',
								'inpost-pay'
							);
							?>
						</p>
					</div>
				</div>

				<p>
					<?php
					esc_html_e(
						'Check shipping method availability for products based on InPost logistic plugin settings',
						'inpost-pay'
					);
					?>
				</p>

				<div class="toggleWrapper">
					<input
						class="mobileToggle"
						type="checkbox"
						id="izi_check_shipping_availability"
						name="<?php echo esc_attr( $shipping_method_availability->get_field_name() ); ?>"
						value="1"
						<?php checked( $shipping_method_availability->get_bool() ); ?>
					>
					<label for="izi_check_shipping_availability"></label>
				</div>
			</td>
		</tr>
	</table>
</div>
