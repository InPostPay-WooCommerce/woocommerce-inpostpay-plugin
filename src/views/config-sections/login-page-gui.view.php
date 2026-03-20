<h3>
    <?php _e("Login Page", "inpost-pay"); ?>
</h3>
<table class="gui-settings-table">
    <tr class="d-flex-align-center">
        <td>
            <?php _e("Show", "inpost-pay"); ?>
        </td>
        <td class="input-tooltip d-flex-align-center">
            <input <?= esc_attr(
                get_option("izi_show_login_page")
            ) == 1
                ? "checked"
                : "" ?> type="checkbox" name="izi_show_login_page" value="1">
            <div class="input-tooltip-wrapper">
                <img src="<?php echo plugin_dir_url(
                        __FILE__
                    ) .
                    "../../../assets/img/tooltip.svg"; ?>" alt="">
                <div class="input-tooltip-box">
                    <p><?php _e(
                            "To increase conversion, we recommend displaying InPost Pay on both the cart and product pages",
                            "inpost-pay"
                        ); ?></p>
                </div>
            </div>
        </td>
    </tr>
    <tr>
        <td>
            <?php _e("Placement", "inpost-pay"); ?>
        </td>
        <td class="input-tooltip d-flex-align-center">
            <select name="izi_place_login_page">
                <option>
                    <?php _e("Select", "inpost-pay"); ?>
                </option>
                <?php
                $loginPagePlaces = [
                    "woocommerce_auth_page_footer" => __(
                        "Login Footer",
                        "inpost-pay"
                    ),
                    "woocommerce_auth_page_header" => __(
                        "Login Header",
                        "inpost-pay"
                    ),
					"woocommerce_before_customer_login_form" => __(
						"Before Login Form",
						"inpost-pay"
					),
					"woocommerce_login_form_start" => __(
						"Login Form Start",
						"inpost-pay"
					),
					"woocommerce_login_form" => __(
						"Login Form",
						"inpost-pay"
					),
					"woocommerce_login_form_end" => __(
						"Login Form End",
						"inpost-pay"
					)
                ];
                $selectedLoginPagePlace = esc_attr(
                    get_option("izi_place_login_page")
                );
                foreach (
                    $loginPagePlaces
                    as $value => $label
                ) {
                    $selected =
                        $value == $selectedLoginPagePlace
                            ? "selected"
                            : "";
                    echo "<option {$selected} value='{$value}'>{$label}</option>";
                }
                ?>
            </select>
            <div class="input-tooltip-wrapper">
                <img src="<?php echo plugin_dir_url(
                        __FILE__
                    ) .
                    "../../../assets/img/tooltip.svg"; ?>" alt="">
                <div class="input-tooltip-box">
                    <p><?php _e(
                            "For WooCommerce cart subpages, you can add widgets in various parts of the page. Choose a location that fits your template, following the instructions available in the Merchant Guide",
                            "inpost-pay"
                        ); ?></p>
                </div>
            </div>
        </td>
    </tr>
    <tr>
        <td>
            <?php _e("Alignment", "inpost-pay"); ?>
        </td>
        <td class="input-tooltip d-flex-align-center">
            <select name="izi_align_login_page">
                <option>
                    <?php _e("Select", "inpost-pay"); ?>
                </option>
                <?php
                $selectedOption = esc_attr(
                    get_option("izi_align_login_page")
                );
                foreach (
                    $availableAligns
                    as $value => $label
                ) {
                    $selected =
                        $value == $selectedOption
                            ? "selected"
                            : "";
                    echo "<option {$selected} value='{$value}'>{$label}</option>";
                }
                ?>
            </select>
            <div class="input-tooltip-wrapper">
                <img src="<?php echo plugin_dir_url(
                        __FILE__
                    ) .
                    "../../../assets/img/tooltip.svg"; ?>" alt="">
                <div class="input-tooltip-box">
                    <p><?php _e(
                            "Specify the orientation of the widget in the available space. If your template allocates a narrow space for the widget, the setting will not affect the appearance",
                            "inpost-pay"
                        ); ?></p>
                </div>
            </div>
        </td>
    </tr>
</table>
<hr>
<?php
/* ?>
                            <h3>Lista produktów</h3>
                            <table>
                                <tr>
                                    <td>Wyświetlaj</td>
                                    <td>Wyrównanie</td>
                                    <td>Tło</td>
                                    <td>Wariant</td>

                                </tr>
                                <tr>
                                    <td>
                                        <input <?= esc_attr(get_option('izi_show_list')) == 1 ? 'checked' : '' ?> type="checkbox"
                                            name="izi_show_list" value="1">
                                    </td>
                                    <td>
                                        <select name="izi_align_list">
                                            <option>Wybierz</option>
                                            <?php
                                        $selectedOption = esc_attr(get_option('izi_align_list'));
                                        foreach ($availableAligns as $value => $label) {
                                            $selected = $value == $selectedOption ? 'selected' : '';
                                            echo "<option {$selected} value='{$value}'>{$label}</option>";
                                        }
                                        ?>
                                        </select>
                                    </td>
                                    <td>
                                        <select name="izi_background_list">
                                            <option>Wybierz</option>
                                            <?php
                                        $selectedOption = esc_attr(get_option('izi_background_list'));
                                        foreach ($availableBackgrounds as $value => $label) {
                                            $selected = $value == $selectedOption ? 'selected' : '';
                                            echo "<option {$selected} value='{$value}'>{$label}</option>";
                                        }
                                        ?>
                                        </select>
                                    </td>
                                    <td>
                                        <select name="izi_variant_list">
                                            <option>Wybierz</option>
                                            <?php
                                        $selectedOption = esc_attr(get_option('izi_variant_list'));
                                        foreach ($availableVariants as $value => $label) {
                                            $selected = $value == $selectedOption ? 'selected' : '';
                                            echo "<option {$selected} value='{$value}'>{$label}</option>";
                                        }
                                        ?>
                                        </select>
                                    </td>
                                </tr>
                            </table>
                            <?php */
?>
