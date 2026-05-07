<?php
/**
 * View: Environment settings.
 *
 * @package Ilabs\Inpost_Pay
 */

use Ilabs\Inpost_Pay\Lib\InPostIzi;

$environment = (string) get_option( 'izi_environment' );
$environment = $environment ? $environment : InPostIzi::ENVIRONMENT_DEVELOP;

$hide_functionality = (string) get_option( 'izi_hide_functionality' );
$hide_functionality = $hide_functionality ? $hide_functionality : 'hidden';

$client_id         = (string) get_option( 'izi_client_id' );
$client_secret     = (string) get_option( 'izi_client_secret' );
$pos_id            = (string) get_option( 'izi_pos_id' );
$merchant_id       = (string) get_option( 'izi_merchant_id' );
$has_client_secret = ! empty( $client_secret );
?>
<div class="input-wrapper">
	<div class="form-group">
		<label>
			<?php esc_html_e( 'Environment', 'inpost-pay' ); ?>
		</label>
		<div>
			<div class="input-tooltip">
				<select name="izi_environment">
					<?php if ( defined( 'IZI_LOGGER' ) ) : ?>
						<option value="1" <?php selected( InPostIzi::ENVIRONMENT_DEVELOP, $environment ); ?>>
							<?php esc_html_e( 'Develop', 'inpost-pay' ); ?>
						</option>
					<?php endif; ?>

					<option value="3" <?php selected( InPostIzi::ENVIRONMENT_SANDBOX, $environment ); ?>>
						<?php esc_html_e( 'Sandbox', 'inpost-pay' ); ?>
					</option>

					<option value="2" <?php selected( InPostIzi::ENVIRONMENT_PRODUCTION, $environment ); ?>>
						<?php esc_html_e( 'Production', 'inpost-pay' ); ?>
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
								'Choose the environment on which you want to display the InPost Pay service. Remember to ensure that the service works correctly in your store before switching to the production environment',
								'inpost-pay'
							);
							?>
						</p>
					</div>
				</div>
			</div>
		</div>
	</div>

	<div class="form-group">
		<label>
			<?php esc_html_e( 'Show widget', 'inpost-pay' ); ?>
		</label>
		<div class="input-tooltip">
			<select name="izi_hide_functionality">
				<option value="hidden" <?php selected( 'hidden', $hide_functionality ); ?>>
					<?php esc_html_e( 'For testers', 'inpost-pay' ); ?>
				</option>
				<option value="public" <?php selected( 'public', $hide_functionality ); ?>>
					<?php esc_html_e( 'For all', 'inpost-pay' ); ?>
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
							'If you choose "testers", the widget will be visible only to those who should see it. To display the widget in this mode in a web browser, enter your store address with the addition of ?showIzi=true. Example: https://yourstore.com?showIzi=true',
							'inpost-pay'
						);
						?>
					</p>
				</div>
			</div>
		</div>
	</div>

	<div class="form-group">
		<label>
			<?php esc_html_e( 'Client ID', 'inpost-pay' ); ?>
		</label>
		<div class="input-tooltip">
			<input
				type="text"
				name="izi_client_id"
				value="<?php echo esc_attr( $client_id ); ?>"
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
							"Remember that the client ID differs depending on the selected environment. To obtain a sandbox Client ID, contact us through the contact form. To obtain a production Client ID, log in to InPost and complete the store's data",
							'inpost-pay'
						);
						?>
					</p>
				</div>
			</div>
		</div>
	</div>

	<div class="form-group">
		<label>
			<?php esc_html_e( 'Client Secret', 'inpost-pay' ); ?>
		</label>
		<div class="input-tooltip">
			<input
				type="text"
				name="izi_client_secret"
				value="<?php echo esc_attr( $has_client_secret ? '*****' : '' ); ?>"
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
							"Remember that the Client Secret differs depending on the selected environment. To obtain a sandbox Client Secret, contact us through the contact form. To obtain a production Client Secret, log in to InPost and complete the store's data",
							'inpost-pay'
						);
						?>
					</p>
				</div>
			</div>
		</div>
	</div>

	<div class="form-group">
		<label>
			<?php esc_html_e( 'POS ID', 'inpost-pay' ); ?>
		</label>
		<div class="input-tooltip">
			<input
				type="text"
				name="izi_pos_id"
				value="<?php echo esc_attr( $pos_id ); ?>"
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
							'For the sandbox environment, enter a random string of characters. For the production environment, log in to InPost and retrieve the POS ID',
							'inpost-pay'
						);
						?>
					</p>
				</div>
			</div>
		</div>
	</div>

	<div class="form-group">
		<label>
			<?php esc_html_e( 'Merchant ID', 'inpost-pay' ); ?>
		</label>
		<div class="input-tooltip">
			<input
				type="text"
				name="izi_merchant_id"
				value="<?php echo esc_attr( $merchant_id ); ?>"
			>
			<div class="input-tooltip-wrapper">
				<img
					src="<?php echo esc_url( plugin_dir_url( __FILE__ ) . '../../../assets/img/tooltip.svg' ); ?>"
					alt=""
				>
				<div class="input-tooltip-box">
					<p>
						<?php esc_html_e( 'Merchant Client ID can be retrieved from the merchant panel', 'inpost-pay' ); ?>
					</p>
				</div>
			</div>
		</div>
	</div>
</div>
