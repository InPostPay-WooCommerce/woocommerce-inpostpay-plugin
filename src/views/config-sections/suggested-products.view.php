<?php
/**
 * Suggested products config section view.
 *
 * @package InPost_Pay
 */

?>
<div class="input-wrapper">
	<div class="form-group">
		<label>
			<?php esc_html_e( 'Max suggested products amount:', 'inpost-pay' ); ?>
		</label>
		<div class="input-tooltip">
			<input
				type="number"
				min="0"
				max="9"
				step="1"
				name="izi_related_count"
				value="<?php echo esc_attr( (string) get_option( 'izi_related_count' ) ); ?>"
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
							'To display suggested products, you must fill out the Related Products section in the WooCommerce product configuration. 1. Go to edit the product in your store. 2. In the Product Data panel, select the "Related products" section. 3. Fill in the "up-sell" or "cross-sell" sections - products from both sections will be visible in the application.',
							'inpost-pay'
						);
						?>
					</p>
				</div>
			</div>
		</div>
	</div>
</div>
