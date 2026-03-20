<?php

/**
 * @var $zone_id int|null
 */

$courierIsActiveField = inpost_pay()
	->shipping_cost_settings( $zone_id )
	->getCourierSettingsGroup()->getIsActiveField();
$apmIsActiveField     = inpost_pay()
	->shipping_cost_settings( $zone_id )
	->getApmSettingsGroup()->getIsActiveField();
?>

<div class="consent-item">
	<h2 class="izi-transport-price-heading-secondary">
		<?php _e(
			"Select transport methods:",
			"inpost-pay"
		); ?>
	</h2>
	<div class="izi-transport-switchers">
		<div class="form-group form-group--row">
			<p><?php esc_html_e( $courierIsActiveField->get_label() ); ?></p>
			<div class="toggleWrapper">
				<input class="mobileToggle" type="checkbox"
						id="<?php esc_attr_e( $courierIsActiveField->get_field_name() ); ?>"
						name="<?php esc_attr_e( $courierIsActiveField->get_field_name() ); ?>"
						value="1" <?= $courierIsActiveField->get_bool( true )
					? "checked"
					: "" ?>>
				<label
					for="<?php esc_attr_e( $courierIsActiveField->get_field_name() ); ?>"></label>
			</div>
		</div>
		<div class="form-group form-group--row">
			<p><?php esc_html_e( $apmIsActiveField->get_label() ); ?></p>
			<div class="toggleWrapper">
				<input class="mobileToggle" type="checkbox"
						id="<?php esc_attr_e( $apmIsActiveField->get_field_name() ); ?>"
						name="<?php esc_attr_e( $apmIsActiveField->get_field_name() ); ?>"
						value="1" <?= $apmIsActiveField->get_bool( true )
					? "checked"
					: "" ?>>
				<label
					for="<?php esc_attr_e( $apmIsActiveField->get_field_name() ); ?>"></label>
			</div>
		</div>
	</div>
</div>

<?php $visibleClass = ! $apmIsActiveField->get_bool() && ! $courierIsActiveField->get_bool() ? 'izi-section-active' : 'izi-section-inactive'; ?>

<div
	class="consent-item izi-no-delivery-method-selected <?php esc_attr_e( $visibleClass ); ?>">
	<img src="<?php echo plugin_dir_url( __FILE__ ) . "../../../assets/img/alert.svg"; ?>" alt="">
	<p><?php _e(
			"You haven't selected any active delivery method. The Buyer will be unable to select a delivery method in the app.",
			"inpost-pay"
		); ?></p>
</div>
