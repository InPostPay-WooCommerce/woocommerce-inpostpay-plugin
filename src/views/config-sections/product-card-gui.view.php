<?php
/**
 * Product card GUI config section view.
 *
 * @package InPost_Pay
 */

$selected_product_place = (string) get_option(
	'izi_place_details',
	'woocommerce_after_add_to_cart_button'
);
$selected_option        = (string) get_option( 'izi_align_details' );
?>
<h3>
	<?php esc_html_e( 'Product card', 'inpost-pay' ); ?>
</h3>

<table class="gui-settings-table">
	<tr class="d-flex-align-center">
		<td>
			<?php esc_html_e( 'Show', 'inpost-pay' ); ?>
		</td>
		<td class="input-tooltip d-flex-align-center">
			<input
				<?php checked( 1, (int) get_option( 'izi_show_details' ) ); ?>
				type="checkbox"
				name="izi_show_details"
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
			<select name="izi_place_details">
				<option value="">
					<?php esc_html_e( 'Select', 'inpost-pay' ); ?>
				</option>

				<?php
				$product_places = array(
					'woocommerce_before_add_to_cart_form'  => __( 'Before add to cart form', 'inpost-pay' ),
					'woocommerce_before_variations_form'   => __( 'Before variations form', 'inpost-pay' ),
					'woocommerce_before_add_to_cart_button' => __( 'Before add to cart button', 'inpost-pay' ),
					'woocommerce_before_single_variation'  => __( 'Before single variation', 'inpost-pay' ),
					'woocommerce_before_add_to_cart_quantity' => __( 'Before quantity field', 'inpost-pay' ),
					'woocommerce_after_add_to_cart_quantity' => __( 'After quantity field', 'inpost-pay' ),
					'woocommerce_after_add_to_cart_button' => __( 'After add to cart button', 'inpost-pay' ),
					'woocommerce_after_variations_form'    => __( 'After variations form', 'inpost-pay' ),
					'woocommerce_after_add_to_cart_form'   => __( 'After add to cart form', 'inpost-pay' ),
					'woocommerce_product_meta_start'       => __( 'Product meta start', 'inpost-pay' ),
					'woocommerce_product_meta_end'         => __( 'Product meta end', 'inpost-pay' ),
					'woocommerce_after_single_product_summary' => __( 'After single product summary', 'inpost-pay' ),
				);

				foreach ( $product_places as $value => $label ) {
					$selected = ( $selected_product_place === $value ) ? 'selected' : '';
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
			<select name="izi_align_details">
				<option value="">
					<?php esc_html_e( 'Select', 'inpost-pay' ); ?>
				</option>
				<?php
				foreach ( $available_aligns as $value => $label ) {
					$selected = ( $selected_option === $value ) ? 'selected' : '';
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

<p>
	<strong><?php esc_html_e( 'Margins', 'inpost-pay' ); ?></strong>:
	<?php esc_html_e( 'Specify custom margin values if the widget is too close to standard buttons', 'inpost-pay' ); ?>
</p>

<table class="gui-settings-table my-2">
	<tr>
		<td>
			<?php esc_html_e( 'Margin top', 'inpost-pay' ); ?>
		</td>
		<td class="input-tooltip d-flex-align-center">
			<input
				type="number"
				name="izi_button_details_margin[top]"
				value="<?php echo isset( $button_details_margin['top'] ) ? esc_attr( (string) (int) $button_details_margin['top'] ) : ''; ?>"
			>
		</td>
	</tr>

	<tr>
		<td>
			<?php esc_html_e( 'Margin left', 'inpost-pay' ); ?>
		</td>
		<td class="input-tooltip d-flex-align-center">
			<input
				type="number"
				name="izi_button_details_margin[left]"
				value="<?php echo isset( $button_details_margin['left'] ) ? esc_attr( (string) (int) $button_details_margin['left'] ) : ''; ?>"
			>
		</td>
	</tr>

	<tr>
		<td>
			<?php esc_html_e( 'Margin right', 'inpost-pay' ); ?>
		</td>
		<td class="input-tooltip d-flex-align-center">
			<input
				type="number"
				name="izi_button_details_margin[right]"
				value="<?php echo isset( $button_details_margin['right'] ) ? esc_attr( (string) (int) $button_details_margin['right'] ) : ''; ?>"
			>
		</td>
	</tr>

	<tr>
		<td>
			<?php esc_html_e( 'Margin bottom', 'inpost-pay' ); ?>
		</td>
		<td class="input-tooltip d-flex-align-center">
			<input
				type="number"
				name="izi_button_details_margin[bottom]"
				value="<?php echo isset( $button_details_margin['bottom'] ) ? esc_attr( (string) (int) $button_details_margin['bottom'] ) : ''; ?>"
			>
		</td>
	</tr>
</table>

<p>
	<strong><?php esc_html_e( 'Paddings', 'inpost-pay' ); ?></strong>:
	<?php esc_html_e( 'Specify individual padding values for the button if the widget is too narrow or too wide', 'inpost-pay' ); ?>
</p>

<table class="gui-settings-table">
	<tr>
		<td>
			<?php esc_html_e( 'Padding top', 'inpost-pay' ); ?>
		</td>
		<td class="input-tooltip d-flex-align-center">
			<input
				type="number"
				name="izi_button_details_padding[top]"
				value="<?php echo isset( $button_details_padding['top'] ) ? esc_attr( (string) (int) $button_details_padding['top'] ) : ''; ?>"
			>
		</td>
	</tr>

	<tr>
		<td>
			<?php esc_html_e( 'Padding left', 'inpost-pay' ); ?>
		</td>
		<td class="input-tooltip d-flex-align-center">
			<input
				type="number"
				name="izi_button_details_padding[left]"
				value="<?php echo isset( $button_details_padding['left'] ) ? esc_attr( (string) (int) $button_details_padding['left'] ) : ''; ?>"
			>
		</td>
	</tr>

	<tr>
		<td>
			<?php esc_html_e( 'Padding right', 'inpost-pay' ); ?>
		</td>
		<td class="input-tooltip d-flex-align-center">
			<input
				type="number"
				name="izi_button_details_padding[right]"
				value="<?php echo isset( $button_details_padding['right'] ) ? esc_attr( (string) (int) $button_details_padding['right'] ) : ''; ?>"
			>
		</td>
	</tr>

	<tr>
		<td>
			<?php esc_html_e( 'Padding bottom', 'inpost-pay' ); ?>
		</td>
		<td class="input-tooltip d-flex-align-center">
			<input
				type="number"
				name="izi_button_details_padding[bottom]"
				value="<?php echo isset( $button_details_padding['bottom'] ) ? esc_attr( (string) (int) $button_details_padding['bottom'] ) : ''; ?>"
			>
		</td>
	</tr>
</table>
