<?php
/**
 * Product description source config section view.
 *
 * @package InPost_Pay
 */

use Ilabs\Inpost_Pay\SettingsPage;

?>
<div class="input-wrapper">
	<div class="form-group">
		<label>
			<?php esc_html_e( 'Map product description based on:', 'inpost-pay' ); ?>
		</label>
		<div class="input-tooltip">
			<?php SettingsPage::productDescMapDropdown(); ?>
			<div class="input-tooltip-wrapper">
				<img
					src="<?php echo esc_url( plugin_dir_url( __FILE__ ) . '../../../assets/img/tooltip.svg' ); ?>"
					alt=""
				>
				<div class="input-tooltip-box">
					<p>
						<?php
						esc_html_e(
							'Determines if full or short description will be mapped',
							'inpost-pay'
						);
						?>
					</p>
				</div>
			</div>
		</div>
	</div>
</div>
