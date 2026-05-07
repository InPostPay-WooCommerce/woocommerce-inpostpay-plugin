<?php
/**
 * Net transport price config section view.
 *
 * @package InPost_Pay
 */

$transport_add_tax_option = get_option( 'izi_transport_add_tax' );
$add_shipping_tax         = false !== $transport_add_tax_option ? (string) $transport_add_tax_option : '23';
?>
<div class="consent-item">
	<div class="input-wrapper input-wrapper--center">
		<label>
			<?php esc_html_e( 'Add VAT to the transport price:', 'inpost-pay' ); ?>
		</label>

		<div class="input-tooltip d-flex-align-center">
			<select name="izi_transport_add_tax">
				<option value="23" <?php selected( '23', $add_shipping_tax ); ?>>
					<?php esc_html_e( 'Yes', 'inpost-pay' ); ?>
				</option>
				<option value="0" <?php selected( '0', $add_shipping_tax ); ?>>
					<?php esc_html_e( 'No', 'inpost-pay' ); ?>
				</option>
			</select>

			<div class="input-tooltip-wrapper">
				<img
					src="<?php echo esc_url( plugin_dir_url( __FILE__ ) . '../../../assets/img/tooltip.svg' ); ?>"
					alt=""
				>
				<div class="input-tooltip-box">
					<p>
						<?php
						esc_html_e(
							'Determines whether tax should be added to the shipping price',
							'inpost-pay'
						);
						?>
					</p>
				</div>
			</div>
		</div>
	</div>
</div>
<hr>
