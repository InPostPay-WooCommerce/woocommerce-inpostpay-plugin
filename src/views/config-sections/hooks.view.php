<?php
/**
 * View: Hooks settings.
 *
 * @package Ilabs\Inpost_Pay
 */

use Ilabs\Inpost_Pay\Lib\config\Hooks\CartHooksConfig;
use Ilabs\Inpost_Pay\Lib\config\Hooks\OrderHooksConfig;

$orders_hook_config = new OrderHooksConfig();
$cart_hook_config   = new CartHooksConfig();
$order_hook_field   = $orders_hook_config->get_form_field();
$cart_hook_field    = $cart_hook_config->get_form_field();

?>

<hr>

<div class="form-group">
	<div class="input-tooltip mb-2">
		<?php $order_hook_field->print_legend(); ?>
		<div class="input-tooltip-wrapper">
			<img src="<?php echo esc_url( plugin_dir_url( __FILE__ ) . '../../../assets/img/tooltip.svg' ); ?>" alt="">
			<div class="input-tooltip-box">
				<p>
					<?php
					esc_html_e(
						'These actions are used to manually trigger WooCommerce logic for plugins like SalesKing when using InPost Pay. Enable only if needed – some plugins may require additional $_POST data or cause conflicts.',
						'inpost-pay'
					);
					?>
				</p>
			</div>
		</div>
	</div>

	<?php $order_hook_field->print_field(); ?>
</div>

<div class="form-group mt-2">
	<div class="input-tooltip mb-2">
		<?php $cart_hook_field->print_legend(); ?>
		<div class="input-tooltip-wrapper">
			<img src="<?php echo esc_url( plugin_dir_url( __FILE__ ) . '../../../assets/img/tooltip.svg' ); ?>" alt="">
			<div class="input-tooltip-box">
				<p>
					<?php
					esc_html_e(
						"These actions are used to trigger logic on cart events (like quantity change or removal), useful for syncing InPost Pay basket when native WooCommerce hooks aren't enough. Enable only if needed.",
						'inpost-pay'
					);
					?>
				</p>
			</div>
		</div>
	</div>

	<?php $cart_hook_field->print_field(); ?>
</div>

<hr>
