<?php
/**
 * View: Checkout GUI settings.
 *
 * @package Ilabs\Inpost_Pay
 */

?>
<h3>
	<?php esc_html_e( 'Checkout', 'inpost-pay' ); ?>
</h3>

<table class="gui-settings-table">
	<tr class="d-flex-align-center">
		<td>
			<?php esc_html_e( 'Show', 'inpost-pay' ); ?>
		</td>
		<td class="input-tooltip d-flex-align-center">
			<input
				<?php checked( 1, (int) get_option( 'izi_show_checkout' ) ); ?>
				type="checkbox"
				name="izi_show_checkout"
				value="1"
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
							'To increase conversion, we recommend displaying InPost Pay on both the cart and product pages',
							'inpost-pay'
						);
						?>
					</p>
				</div>
			</div>
		</td>
	</tr>

	<tr>
		<td>
			<?php esc_html_e( 'Placement', 'inpost-pay' ); ?>
		</td>
		<td class="input-tooltip d-flex-align-center">
			<select name="izi_place_checkout">
				<option>
					<?php esc_html_e( 'Select', 'inpost-pay' ); ?>
				</option>
				<?php
				$checkout_places = array(
					'woocommerce_before_checkout_form' => __( 'Before checkout form', 'inpost-pay' ),
					'woocommerce_checkout_before_customer_details' => __( 'Before customer details', 'inpost-pay' ),
					'woocommerce_before_checkout_billing_form' => __( 'Before billing form', 'inpost-pay' ),
					'woocommerce_after_checkout_billing_form' => __( 'After billing form', 'inpost-pay' ),
					'woocommerce_before_checkout_shipping_form' => __( 'Before shipping form', 'inpost-pay' ),
					'woocommerce_after_checkout_shipping_form' => __( 'After shipping form', 'inpost-pay' ),
					'woocommerce_checkout_after_customer_details' => __( 'After customer details', 'inpost-pay' ),
					'woocommerce_checkout_before_order_review' => __( 'Before order review', 'inpost-pay' ),
					'woocommerce_checkout_after_order_review' => __( 'After order review', 'inpost-pay' ),
					'woocommerce_after_checkout_form'  => __( 'After checkout form', 'inpost-pay' ),
				);

				$selected_checkout_place = (string) get_option( 'izi_place_checkout' );

				foreach ( $checkout_places as $value => $label ) {
					$selected = ( $value === $selected_checkout_place ) ? 'selected' : '';
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
						<?php
						esc_html_e(
							'For WooCommerce cart subpages, you can add widgets in various parts of the page. Choose a location that fits your template, following the instructions available in the Merchant Guide',
							'inpost-pay'
						);
						?>
					</p>
				</div>
			</div>
		</td>
	</tr>

	<tr>
		<td>
			<?php esc_html_e( 'Alignment', 'inpost-pay' ); ?>
		</td>
		<td class="input-tooltip d-flex-align-center">
			<select name="izi_align_checkout">
				<option>
					<?php esc_html_e( 'Select', 'inpost-pay' ); ?>
				</option>
				<?php
				$selected_align = (string) get_option( 'izi_align_checkout' );

				foreach ( $available_aligns as $value => $label ) {
					$selected = ( $selected_align === $value ) ? 'selected' : '';
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
						<?php
						esc_html_e(
							'Specify the orientation of the widget in the available space. If your template allocates a narrow space for the widget, the setting will not affect the appearance',
							'inpost-pay'
						);
						?>
					</p>
				</div>
			</div>
		</td>
	</tr>
</table>
<hr>
