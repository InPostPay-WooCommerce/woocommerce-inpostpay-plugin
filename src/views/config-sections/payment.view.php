<?php
/**
 * Payment configuration section view.
 *
 * @package InPost_Pay
 */

$payment_method_options       = new \Ilabs\Inpost_Pay\Lib\config\payment\PaymentMethodsOptions();
$virtual_payment_method       = new \Ilabs\Inpost_Pay\Lib\config\payment\Virtual_Payment_Gateway_Config();
$virtual_payment_method_field = $virtual_payment_method->get_form_field();
?>
<div class="input-wrapper mt-2 mb-2">
	<div class="form-group form-group--row">
		<div class="input-tooltip">
			<label class="label-gray">
				<?php
				esc_html_e(
					'Enable payments in accordance with the signed agreement with the bank',
					'inpost-pay'
				);
				?>
			</label>
			<div class="input-tooltip-wrapper">
				<img
					src="<?php echo esc_url( plugin_dir_url( __FILE__ ) . '../../../assets/img/tooltip.svg' ); ?>"
					alt=""
				>
				<div class="input-tooltip-box">
					<p>
						<?php
						esc_html_e(
							'Payment methods have been specified in the payment gateway service agreement',
							'inpost-pay'
						);
						?>
					</p>
				</div>
			</div>
		</div>
		<input
			<?php checked( 1, (int) get_option( 'izi_payment_aion', 1 ) ); ?>
			type="checkbox"
			name="izi_payment_aion"
			value="1"
		>
	</div>

	<div class="form-group form-group--row">
		<div class="input-tooltip">
			<label class="label-gray">
				<?php
				esc_html_e(
					'Enable payment on delivery according to the signed agreement with InPost',
					'inpost-pay'
				);
				?>
			</label>
			<div class="input-tooltip-wrapper">
				<img
					src="<?php echo esc_url( plugin_dir_url( __FILE__ ) . '../../../assets/img/tooltip.svg' ); ?>"
					alt=""
				>
				<div class="input-tooltip-box">
					<p>
						<?php
						esc_html_e(
							'Cash on delivery payment will be available only if you have a signed agreement with InPost to provide this service in your store',
							'inpost-pay'
						);
						?>
					</p>
				</div>
			</div>
		</div>
		<input
			<?php checked( 1, (int) get_option( 'izi_payment_inpost' ) ); ?>
			type="checkbox"
			name="izi_payment_inpost"
			value="1"
		>
	</div>
</div>

<div class="input-wrapper mt-2 mb-2">
	<?php if ( $payment_method_options->can_show_in_form() ) : ?>
		<?php $payment_method_options_field = $payment_method_options->get_form_field(); ?>
		<div class="form-group mt-4 mb-4">
			<div class="input-tooltip">
				<?php $payment_method_options_field->print_label(); ?>
				<div class="input-tooltip-wrapper">
					<img
						src="<?php echo esc_url( plugin_dir_url( __FILE__ ) . '../../../assets/img/tooltip.svg' ); ?>"
						alt=""
					>
					<div class="input-tooltip-box">
						<p>
							<?php
							esc_html_e(
								'Select the available payment methods in InPostPay. If none are selected, all payment methods will be available by default in accordance with the signed agreement.',
								'inpost-pay'
							);
							?>
						</p>
					</div>
				</div>
			</div>
			<?php $payment_method_options_field->print_field(); ?>
		</div>
	<?php endif; ?>
</div>

<div class="input-wrapper mt-2 mb-2">
	<div class="form-group form-group--row">
		<div class="input-tooltip">
			<?php $virtual_payment_method_field->print_label(); ?>
			<div class="input-tooltip-wrapper">
				<img
					src="<?php echo esc_url( plugin_dir_url( __FILE__ ) . '../../../assets/img/tooltip.svg' ); ?>"
					alt=""
				>
				<div class="input-tooltip-box">
					<p>
						<?php
						esc_html_e(
							'Select this option to enable virtual payment method for woocommerce',
							'inpost-pay'
						);
						?>
					</p>
				</div>
			</div>
		</div>
		<?php $virtual_payment_method_field->print_field(); ?>
	</div>
</div>
