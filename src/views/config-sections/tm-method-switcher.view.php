<?php
/**
 * Transport method switcher config section view.
 *
 * @package InPost_Pay
 */

use function Ilabs\Inpost_Pay\inpost_pay;

/**
 * Zone ID variable passed to this view.
 *
 * @var int|null $zone_id
 */

$courier_is_active_field = inpost_pay()
	->shipping_cost_settings( $zone_id )
	->get_courier_settings_group()
	->get_is_active_field();

$apm_is_active_field = inpost_pay()
	->shipping_cost_settings( $zone_id )
	->get_apm_settings_group()
	->get_is_active_field();

$visible_class = ! $apm_is_active_field->get_bool() && ! $courier_is_active_field->get_bool()
	? 'izi-section-active'
	: 'izi-section-inactive';
?>

<div class="consent-item">
	<h2 class="izi-transport-price-heading-secondary">
		<?php esc_html_e( 'Select transport methods:', 'inpost-pay' ); ?>
	</h2>

	<div class="izi-transport-switchers">
		<div class="form-group form-group--row">
			<p><?php echo esc_html( $courier_is_active_field->get_label() ); ?></p>
			<div class="toggleWrapper">
				<input
					class="mobileToggle"
					type="checkbox"
					id="<?php echo esc_attr( $courier_is_active_field->get_field_name() ); ?>"
					name="<?php echo esc_attr( $courier_is_active_field->get_field_name() ); ?>"
					value="1"
					<?php checked( $courier_is_active_field->get_bool( true ) ); ?>
				>
				<label for="<?php echo esc_attr( $courier_is_active_field->get_field_name() ); ?>"></label>
			</div>
		</div>

		<div class="form-group form-group--row">
			<p><?php echo esc_html( $apm_is_active_field->get_label() ); ?></p>
			<div class="toggleWrapper">
				<input
					class="mobileToggle"
					type="checkbox"
					id="<?php echo esc_attr( $apm_is_active_field->get_field_name() ); ?>"
					name="<?php echo esc_attr( $apm_is_active_field->get_field_name() ); ?>"
					value="1"
					<?php checked( $apm_is_active_field->get_bool( true ) ); ?>
				>
				<label for="<?php echo esc_attr( $apm_is_active_field->get_field_name() ); ?>"></label>
			</div>
		</div>
	</div>
</div>

<div class="consent-item izi-no-delivery-method-selected <?php echo esc_attr( $visible_class ); ?>">
	<img src="<?php echo esc_url( plugin_dir_url( __FILE__ ) . '../../../assets/img/alert.svg' ); ?>" alt="">
	<p>
		<?php
		esc_html_e(
			"You haven't selected any active delivery method. The Buyer will be unable to select a delivery method in the app.",
			'inpost-pay'
		);
		?>
	</p>
</div>
