<div class="form-group mt-3">
	<div class="col-6">
		<label>
			<?php _e(
				"Statuses for order paid by InPost Pay",
				"inpost-pay"
			); ?>
		</label>
		<div class="input-tooltip">
			<?php \Ilabs\Inpost_Pay\SettingsPage::statusDropdown(
				"AUTHORIZED"
			) ?>
			<div class="input-tooltip-wrapper">
				<img src="<?php echo plugin_dir_url(
										 __FILE__
									 ) .
									 "../../../assets/img/tooltip.svg"; ?>" alt="">
				<div class="input-tooltip-box">
					<p><?php _e(
							"Determines what order status should be set after the payment is completed",
							"inpost-pay"
						); ?></p>
				</div>
			</div>
		</div>
	</div>

	<div class="col-6">
		<label>
			<?php _e(
				"Statuses for COD order",
				"inpost-pay"
			); ?>
		</label>
		<div class="input-tooltip">
			<?php \Ilabs\Inpost_Pay\SettingsPage::statusCodDropdown(
				"AUTHORIZED"
			) ?>
			<div class="input-tooltip-wrapper">
				<img src="<?php echo plugin_dir_url(
										 __FILE__
									 ) .
									 "../../../assets/img/tooltip.svg"; ?>" alt="">
				<div class="input-tooltip-box">
					<p><?php _e(
							"Determines what order status should be set in cod order",
							"inpost-pay"
						); ?></p>
				</div>
			</div>
		</div>
	</div>

</div>
<hr>
<div class="input-wrapper">
    <fieldset>
        <div class="input-tooltip mb-2">
            <legend class="text-bold">
                <?php _e(
                    "Statuses for orders",
                    "inpost-pay"
                ); ?>
            </legend>
            <div class="input-tooltip-wrapper">
                <img src="<?php echo plugin_dir_url(
                        __FILE__
                    ) .
                    "../../../assets/img/tooltip.svg"; ?>" alt="">
                <div class="input-tooltip-box">
                    <p><?php _e(
                            'Please verify if the order status dictionary matches what is actually presented to customers. At any time, you can change the names of the displayed statuses'
                        ); ?></p>
                </div>
            </div>
        </div>
        <div class="api-settings-table">
            <?php \Ilabs\Inpost_Pay\SettingsPage::statusMap() ?>
        </div>
    </fieldset>
</div>
<hr>
