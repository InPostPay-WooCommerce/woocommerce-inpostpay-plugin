<div class="input-wrapper">
    <div class="form-group">
        <label>
            <?php _e("Environment", "inpost-pay"); ?>
        </label>
        <div>
            <div class="input-tooltip">
                <select name="izi_environment">
                    <?php $environment = esc_attr(
                        get_option("izi_environment")
                    ); ?>
                    <?php $environment =
                        $environment ?:
                            \Ilabs\Inpost_Pay\Lib\InPostIzi::ENVIRONMENT_DEVELOP; ?>
                    <?php if (defined("IZI_LOGGER")): ?>
                        <option value="1" <?= $environment ==
                        Ilabs\Inpost_Pay\Lib\InPostIzi::ENVIRONMENT_DEVELOP
                            ? "selected"
                            : "" ?>>
                            <?php _e(
                                "Develop",
                                "inpost-pay"
                            ); ?>
                        </option>
                    <?php endif; ?>
                    <option value="3" <?= $environment ==
                    \Ilabs\Inpost_Pay\Lib\InPostIzi::ENVIRONMENT_SANDBOX
                        ? "selected"
                        : "" ?>>
                        Sandbox
                    </option>
                    <option value="2" <?= $environment ==
                    \Ilabs\Inpost_Pay\Lib\InPostIzi::ENVIRONMENT_PRODUCTION
                        ? "selected"
                        : "" ?>>
                        <?php _e(
                            "Production",
                            "inpost-pay"
                        ); ?>
                    </option>
                </select>
                <div class="input-tooltip-wrapper">
                    <img src="<?php echo plugin_dir_url(
                            __FILE__
                        ) .
                        "../../../assets/img/tooltip.svg"; ?>" alt="">
                    <div class="input-tooltip-box">
                        <p><?php _e(
                                "Choose the environment on which you want to display the InPost Pay service. Remember to ensure that the service works correctly in your store before switching to the production environment",
                                "inpost-pay"
                            ); ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="form-group">
        <label>
            <?php _e("Show widget", "inpost-pay"); ?>
        </label>
        <div class="input-tooltip">
            <select name="izi_hide_functionality">
                <?php $hideFunctionality =
                    esc_attr(
                        get_option(
                            "izi_hide_functionality"
                        )
                    ) ?:
                        "hidden"; ?>
                <?php var_dump($hideFunctionality); ?>
                <option value="hidden" <?= $hideFunctionality ==
                "hidden"
                    ? "selected"
                    : "" ?>>
                    <?php _e(
                        "For testers",
                        "inpost-pay"
                    ); ?>
                </option>
                <option value="public" <?= $hideFunctionality ==
                "public"
                    ? "selected"
                    : "" ?>>
                    <?php _e(
                        "For all",
                        "inpost-pay"
                    ); ?>
                </option>
            </select>
            <div class="input-tooltip-wrapper">
                <img src="<?php echo plugin_dir_url(
                        __FILE__
                    ) .
                    "../../../assets/img/tooltip.svg"; ?>" alt="">
                <div class="input-tooltip-box">
                    <p><?php _e(
                            'If you choose "testers", the widget will be visible only to those who should see it. To display the widget in this mode in a web browser, enter your store address with the addition of ?showIzi=true. Example: https://yourstore.com?showIzi=true',
                            "inpost-pay"
                        ); ?></p>
                </div>
            </div>
        </div>
    </div>
    <div class="form-group">
        <label>
            <?php _e("Client ID", "inpost-pay"); ?>
        </label>
        <div class="input-tooltip">
            <input type="text" name="izi_client_id" value="<?= esc_attr(
                get_option("izi_client_id")
            ) ?>">
            <div class="input-tooltip-wrapper">
                <img src="<?php echo plugin_dir_url(
                        __FILE__
                    ) .
                    "../../../assets/img/tooltip.svg"; ?>" alt="">
                <div class="input-tooltip-box">
                    <p><?php _e(
                            "Remember that the client ID differs depending on the selected environment. To obtain a sandbox Client ID, contact us through the contact form. To obtain a production Client ID, log in to InPost and complete the store's data",
                            "inpost-pay"
                        ); ?></p>
                </div>
            </div>
        </div>
    </div>
    <div class="form-group">
        <label>
            <?php _e("Client Secret", "inpost-pay"); ?>
        </label>
        <div class="input-tooltip">
            <input type="text" name="izi_client_secret" value="<?= esc_attr(
                get_option("izi_client_secret")
            )
                ? "*****"
                : "" ?>">
            <div class="input-tooltip-wrapper">
                <img src="<?php echo plugin_dir_url(
                        __FILE__
                    ) .
                    "../../../assets/img/tooltip.svg"; ?>" alt="">
                <div class="input-tooltip-box">
                    <p><?php _e(
                            "Remember that the Client Secret differs depending on the selected environment. To obtain a sandbox Client Secret, contact us through the contact form. To obtain a production Client Secret, log in to InPost and complete the store's data",
                            "inpost-pay"
                        ); ?></p>
                </div>
            </div>
        </div>
    </div>
    <div class="form-group">
        <label>
            <?php _e("POS ID", "inpost-pay"); ?>
        </label>
        <div class="input-tooltip">
            <input type="text" name="izi_pos_id" value="<?= esc_attr(
                get_option("izi_pos_id")
            ) ?>">
            <div class="input-tooltip-wrapper">
                <img src="<?php echo plugin_dir_url(
                        __FILE__
                    ) .
                    "../../../assets/img/tooltip.svg"; ?>" alt="">
                <div class="input-tooltip-box">
                    <p><?php _e(
                            "For the sandbox environment, enter a random string of characters. For the production environment, log in to InPost and retrieve the POS ID",
                            "inpost-pay"
                        ); ?></p>
                </div>
            </div>
        </div>
    </div>
	<div class="form-group">
		<label>
			<?php _e("Merchant ID", "inpost-pay"); ?>
		</label>
		<div class="input-tooltip">
			<input type="text" name="izi_merchant_id" value="<?= esc_attr(
				get_option("izi_merchant_id")
			) ?>">
			<div class="input-tooltip-wrapper">
				<img src="<?php echo plugin_dir_url(
										 __FILE__
									 ) .
									 "../../../assets/img/tooltip.svg"; ?>" alt="">
				<div class="input-tooltip-box">
					<p><?php _e(
							"Merchant Client ID can be retrieved from the merchant panel",
							"inpost-pay"
						); ?></p>
				</div>
			</div>
		</div>
	</div>
</div>
