<?php
/**
 * Minicart GUI config section view.
 *
 * @package InPost_Pay
 */

$selected_minicart_place = (string) get_option( 'izi_place_minicart' );
$selected_align          = (string) get_option( 'izi_align_minicart' );
?>
<h3>
	<?php esc_html_e( 'Minicart', 'inpost-pay' ); ?>
</h3>

<table class="gui-settings-table">
	<tr class="d-flex-align-center">
		<td>
			<?php esc_html_e( 'Show', 'inpost-pay' ); ?>
		</td>
		<td class="input-tooltip d-flex-align-center">
			<input
				<?php checked( 1, (int) get_option( 'izi_show_minicart' ) ); ?>
				type="checkbox"
				name="izi_show_minicart"
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
			<select name="izi_place_minicart">
				<option>
					<?php esc_html_e( 'Select', 'inpost-pay' ); ?>
				</option>
				<?php
				$minicart_places = array(
					'woocommerce_before_mini_cart'                    => __( 'Before minicart', 'inpost-pay' ),
					'woocommerce_before_mini_cart_contents'           => __( 'Before minicart contents', 'inpost-pay' ),
					'woocommerce_mini_cart_contents'                  => __( 'After minicart contents', 'inpost-pay' ),
					'woocommerce_widget_shopping_cart_total'          => __( 'At total', 'inpost-pay' ),
					'woocommerce_widget_shopping_cart_before_buttons' => __( 'Before buttons', 'inpost-pay' ),
					'woocommerce_widget_shopping_cart_after_buttons'  => __( 'After buttons', 'inpost-pay' ),
					'woocommerce_after_mini_cart'                     => __( 'After minicart', 'inpost-pay' ),
				);

				foreach ( $minicart_places as $value => $label ) {
					$selected = ( $selected_minicart_place === $value ) ? 'selected' : '';
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
			<select name="izi_align_minicart">
				<option>
					<?php esc_html_e( 'Select', 'inpost-pay' ); ?>
				</option>
				<?php
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
