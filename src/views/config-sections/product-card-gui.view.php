<h3>
    <?php _e("Product card", "inpost-pay"); ?>
</h3>
<table class="gui-settings-table">
    <tr class="d-flex-align-center">
        <td>
            <?php _e("Show", "inpost-pay"); ?>
        </td>
        <td class="input-tooltip d-flex-align-center">
            <input <?= esc_attr(
                get_option("izi_show_details")
            ) == 1
                ? "checked"
                : "" ?> type="checkbox" name="izi_show_details" value="1">
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
            <select name="izi_place_details">
                <option value="">
                    <?php _e("Select", "inpost-pay"); ?>
                </option>

                <?php
                $productPlaces = [
                    "woocommerce_before_add_to_cart_form" => __(
                        "Before add to cart form",
                        "inpost-pay"
                    ),
                    "woocommerce_before_variations_form" => __(
                        "Before variations form",
                        "inpost-pay"
                    ),
                    "woocommerce_before_add_to_cart_button" => __(
                        "Before add to cart button",
                        "inpost-pay"
                    ),
                    "woocommerce_before_single_variation" => __(
                        "Before single variation",
                        "inpost-pay"
                    ),
                    "woocommerce_before_add_to_cart_quantity" => __(
                        "Before quantity field",
                        "inpost-pay"
                    ),
                    "woocommerce_after_add_to_cart_quantity" => __(
                        "After quantity field",
                        "inpost-pay"
                    ),
                    "woocommerce_after_add_to_cart_button" => __(
                        "After add to cart button",
                        "inpost-pay"
                    ),
                    "woocommerce_after_variations_form" => __(
                        "After variations form",
                        "inpost-pay"
                    ),
                    "woocommerce_after_add_to_cart_form" => __(
                        "After add to cart form",
                        "inpost-pay"
                    ),
                    "woocommerce_product_meta_start" => __(
                        "Product meta start",
                        "inpost-pay"
                    ),
                    "woocommerce_product_meta_end" => __(
                        "Product meta end",
                        "inpost-pay"
                    ),
                    "woocommerce_after_single_product_summary" => __(
                        "After single product summary",
                        "inpost-pay"
                    ),
                ];
                $selectedProductPlace = esc_attr(
                    get_option(
                        "izi_place_details",
                        "woocommerce_after_add_to_cart_button"
                    )
                );
                foreach (
                    $productPlaces
                    as $value => $label
                ) {
                    $selected =
                        $value == $selectedProductPlace
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
            <select name="izi_align_details">
                <option>
                    <?php _e("Select", "inpost-pay"); ?>
                </option>
                <?php
                $selectedOption = esc_attr(
                    get_option("izi_align_details")
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
<p>
    <strong><?php esc_html_e("Margins", "inpost-pay"); ?></strong>: <?php esc_html_e("Specify custom margin values if the widget is too close to standard buttons", "inpost-pay"); ?>
</p>
<table class="gui-settings-table my-2">
    <tr>
        <td>
            <?php _e("Margin top", "inpost-pay"); ?>
        </td>
        <td class="input-tooltip d-flex-align-center">
            <input type="number" name="izi_button_details_margin[top]" value="<?= isset(
                $button_details_margin["top"]
            )
                ? (int)$button_details_margin["top"]
                : "" ?>">
        </td>
    </tr>
    <tr>
        <td>
            <?php _e("Margin left", "inpost-pay"); ?>
        </td>
        <td class="input-tooltip d-flex-align-center">
            <input type="number" name="izi_button_details_margin[left]" value="<?= isset(
                $button_details_margin["left"]
            )
                ? (int)$button_details_margin["left"]
                : "" ?>">
        </td>
    </tr>
    <tr>
        <td>
            <?php _e("Margin right", "inpost-pay"); ?>
        </td>
        <td class="input-tooltip d-flex-align-center">
            <input type="number" name="izi_button_details_margin[right]" value="<?= isset(
                $button_details_margin["right"]
            )
                ? (int)$button_details_margin["right"]
                : "" ?>">
        </td>
    </tr>
    <tr>
        <td>
            <?php _e("Margin bottom", "inpost-pay"); ?>
        </td>
        <td class="input-tooltip d-flex-align-center">
            <input type="number" name="izi_button_details_margin[bottom]" value="<?= isset(
                $button_details_margin["bottom"]
            )
                ? (int)$button_details_margin["bottom"]
                : "" ?>">
        </td>
    </tr>
</table>
<p>
    <strong><?php esc_html_e("Paddings", "inpost-pay"); ?></strong>: <?php esc_html_e("Specify individual padding values for the button if the widget is too narrow or too wide", "inpost-pay"); ?>
</p>
<table class="gui-settings-table">
    <tr>
        <td>
            <?php _e("Padding top", "inpost-pay"); ?>
        </td>
        <td class="input-tooltip d-flex-align-center">
            <input type="number" name="izi_button_details_padding[top]" value="<?= isset(
                $button_details_padding["top"]
            )
                ? (int)$button_details_padding["top"]
                : "" ?>">
        </td>
    </tr>
    <tr>
        <td>
            <?php _e("Padding left", "inpost-pay"); ?>
        </td>
        <td class="input-tooltip d-flex-align-center">
            <input type="number" name="izi_button_details_padding[left]" value="<?= isset(
                $button_details_padding["left"]
            )
                ? (int)$button_details_padding["left"]
                : "" ?>">
        </td>
    </tr>
    <tr>
        <td>
            <?php _e("Padding right", "inpost-pay"); ?>
        </td>
        <td class="input-tooltip d-flex-align-center">
            <input type="number" name="izi_button_details_padding[right]" value="<?= isset(
                $button_details_padding["right"]
            )
                ? (int)$button_details_padding["right"]
                : "" ?>">
        </td>
    </tr>
    <tr>
        <td>
            <?php _e("Padding bottom", "inpost-pay"); ?>
        </td>
        <td class="input-tooltip d-flex-align-center">
            <input type="number" name="izi_button_details_padding[bottom]" value="<?= isset(
                $button_details_padding["bottom"]
            )
                ? (int)$button_details_padding["bottom"]
                : "" ?>">
        </td>
    </tr>
</table>
