<?php
/**
 * View: Login page GUI settings.
 *
 * @package Ilabs\Inpost_Pay
 */

?>

<h3>
	<?php esc_html_e( 'Login Page', 'inpost-pay' ); ?>
</h3>

<table class="gui-settings-table">
	<tr class="d-flex-align-center">
		<td>
			<?php esc_html_e( 'Show', 'inpost-pay' ); ?>
		</td>
		<td class="input-tooltip d-flex-align-center">
			<input
				<?php checked( 1, (int) get_option( 'izi_show_login_page' ) ); ?>
				type="checkbox"
				name="izi_show_login_page"
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
			<select name="izi_place_login_page">
				<option>
					<?php esc_html_e( 'Select', 'inpost-pay' ); ?>
				</option>
				<?php
				$login_page_places = array(
					'woocommerce_auth_page_footer' => __( 'Login Footer', 'inpost-pay' ),
					'woocommerce_auth_page_header' => __( 'Login Header', 'inpost-pay' ),
					'woocommerce_before_customer_login_form' => __( 'Before Login Form', 'inpost-pay' ),
					'woocommerce_login_form_start' => __( 'Login Form Start', 'inpost-pay' ),
					'woocommerce_login_form'       => __( 'Login Form', 'inpost-pay' ),
					'woocommerce_login_form_end'   => __( 'Login Form End', 'inpost-pay' ),
				);

				$selected_login_page_place = (string) get_option( 'izi_place_login_page' );

				foreach ( $login_page_places as $value => $label ) {
					$selected = ( $value === $selected_login_page_place ) ? 'selected' : '';
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
			<select name="izi_align_login_page">
				<option>
					<?php esc_html_e( 'Select', 'inpost-pay' ); ?>
				</option>
				<?php
				$selected_option = (string) get_option( 'izi_align_login_page' );

				foreach ( $available_aligns as $value => $label ) {
					$selected = ( $value === $selected_option ) ? 'selected' : '';
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
