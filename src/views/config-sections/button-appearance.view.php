<?php
/**
 * View: Button appearance settings.
 *
 * @package Ilabs\Inpost_Pay
 */

use Ilabs\Inpost_Pay\hooks\front\FrontWidgetV2;
use Ilabs\Inpost_Pay\Lib\config\widget_v2\WidgetV2SizeConfig;
use Ilabs\Inpost_Pay\Lib\InPostIzi;

$widget_v2_size = new WidgetV2SizeConfig();
?>
<div class="button-wrapper">
	<div class="button-wrapper-left-side">
		<h2>
			<?php esc_html_e( 'Button appearance', 'inpost-pay' ); ?>
		</h2>
		<h3>
			<?php esc_html_e( 'Display', 'inpost-pay' ); ?>
		</h3>
		<table class="gui-settings-table">
			<tr>
				<td>
					<?php esc_html_e( 'Background', 'inpost-pay' ); ?>
				</td>
				<td class="input-tooltip d-flex-align-center">
					<select id="izi-background-select" name="izi_background">
						<?php
						$selected_option = (string) get_option( 'izi_background', 'bright' );

						foreach ( $available_backgrounds as $value => $label ) {
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
								<?php esc_html_e( 'Determines the background theme', 'inpost-pay' ); ?>
							</p>
						</div>
					</div>
				</td>
			</tr>
			<tr>
				<td>
					<?php esc_html_e( 'Variant', 'inpost-pay' ); ?>
				</td>
				<td class="input-tooltip d-flex-align-center">
					<select id="izi-variant-select" name="izi_variant">
						<?php
						$selected_option = (string) get_option( 'izi_variant', 'primary' );

						foreach ( $available_variants as $value => $label ) {
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
								<?php esc_html_e( 'Determines the variant of button', 'inpost-pay' ); ?>
							</p>
						</div>
					</div>
				</td>
			</tr>

			<tr>
				<td>
					<?php esc_html_e( 'Round style', 'inpost-pay' ); ?>
				</td>
				<td class="input-tooltip d-flex-align-center">
					<select id="izi-frame-style-select" name="izi_frame_style">
						<?php
						$selected_option = (string) get_option( 'izi_frame_style' );

						foreach ( $available_frame_style as $value => $label ) {
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
								<?php esc_html_e( 'Determines the button frame style', 'inpost-pay' ); ?>
							</p>
						</div>
					</div>
				</td>
			</tr>

			<tr>
				<td>
					<?php $widget_v2_size->get_form_field()->print_label(); ?>
				</td>
				<td class="input-tooltip d-flex-align-center">
					<?php $widget_v2_size->get_form_field()->print_field(); ?>
					<div class="input-tooltip-wrapper">
						<img
							src="<?php echo esc_url( plugin_dir_url( __FILE__ ) . '../../../assets/img/tooltip.svg' ); ?>"
							alt=""
						>
						<div class="input-tooltip-box">
							<p><?php echo esc_html( $widget_v2_size->get_description() ); ?></p>
						</div>
					</div>
				</td>
			</tr>
		</table>
	</div>

	<div class="button-wrapper-right-side">
	</div>

	<script
		src="<?php echo esc_url( InPostIzi::getJsUrl() ); ?>?a=<?php echo esc_attr( (string) wp_rand( 100, 100000 ) ); ?>"
		id="InpostpayWidgetV2-js"
	></script> <?php // phpcs:ignore WordPress.WP.EnqueuedResources.NonEnqueuedScript ?>

	<script type="application/javascript">
		const IPPWidgetOptions = {
			merchantClientId: '<?php echo esc_js( FrontWidgetV2::get_merchant_id() ); ?>',
			basketBindingApiKey: '',
		};
		InPostPayWidget.init(IPPWidgetOptions);
	</script>
</div>
<hr>
