<div class="input-wrapper">
	<div class="form-group">
		<label>
			<?php _e(
				"Map product description based on:",
				"inpost-pay"
			); ?>
		</label>
		<div class="input-tooltip">
			<?php \Ilabs\Inpost_Pay\SettingsPage::productDescMapDropdown() ?>
			<div class="input-tooltip-wrapper">
				<img src="<?php echo plugin_dir_url(
										 __FILE__
									 ) .
									 "../../../assets/img/tooltip.svg"; ?>"
					 alt="">
				<div class="input-tooltip-box">
					<p><?php _e(
							"Determines if full or short description will be mapped",
							"inpost-pay"
						); ?></p>
				</div>
			</div>
		</div>
	</div>
</div>
