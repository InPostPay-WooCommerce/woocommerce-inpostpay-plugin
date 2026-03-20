<h3>
    <?php _e("Cart", "inpost-pay"); ?>
</h3>

<p>
	<?php esc_html_e( "If your store uses Elementor, please refer to the manual to activate widget visibility",
		"inpost-pay" ); ?></p>
<p>
	<?php esc_html_e( "For the added widget in the elementor editor or gutenberg block, disable the display in the standard configuration as this may cause the button to be displayed twice",
		"inpost-pay" ); ?>
</p>

<table class="gui-settings-table">
    <tr class="d-flex-align-center">
        <td>
            <?php _e("Show", "inpost-pay"); ?>
        </td>
        <td class="input-tooltip d-flex-align-center">
            <input <?= esc_attr(
                get_option("izi_show_basket")
            ) == 1
                ? "checked"
                : "" ?> type="checkbox" name="izi_show_basket" value="1">
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
            <select name="izi_place_basket">
                <option>
                    <?php _e("Show", "inpost-pay"); ?>
                </option>
                <?php
                $cartPlaces = [
                    "woocommerce_before_cart" => __(
                        "Before cart",
                        "inpost-pay"
                    ),
                    "woocommerce_before_cart_table" => __(
                        "Before cart table",
                        "inpost-pay"
                    ),
                    "woocommerce_before_cart_contents" => __(
                        "Before cart content",
                        "inpost-pay"
                    ),
                    "woocommerce_cart_contents" => __(
                        "Cart contents",
                        "inpost-pay"
                    ),
                    "woocommerce_cart_coupon" => __(
                        "Cart coupon",
                        "inpost-pay"
                    ),
                    "woocommerce_after_cart_contents" => __(
                        "After cart contents",
                        "inpost-pay"
                    ),
                    "woocommerce_after_cart_table" => __(
                        "After cart table slot 1",
                        "inpost-pay"
                    ),
                    "woocommerce_cart_collaterals" => __(
                        "After cart table slot 2",
                        "inpost-pay"
                    ),
                    "woocommerce_before_cart_totals" => __(
                        "Before cart totals",
                        "inpost-pay"
                    ),
                    "woocommerce_cart_totals_before_shipping" => __(
                        "Before shipping",
                        "inpost-pay"
                    ),
                    "woocommerce_before_shipping_calculator" => __(
                        "Before shipping calculator",
                        "inpost-pay"
                    ),
                    "woocommerce_after_shipping_calculator" => __(
                        "After shipping calculator",
                        "inpost-pay"
                    ),
                    "woocommerce_cart_totals_after_shipping" => __(
                        "After shipping",
                        "inpost-pay"
                    ),
                    "woocommerce_cart_totals_before_order_total" => __(
                        "Before order total",
                        "inpost-pay"
                    ),
                    "woocommerce_cart_totals_after_order_total" => __(
                        "After order total",
                        "inpost-pay"
                    ),
                    "woocommerce_proceed_to_checkout" => __(
                        "Proceed to checkout",
                        "inpost-pay"
                    ),
                    "woocommerce_after_cart_totals" => __(
                        "After cart totals",
                        "inpost-pay"
                    ),
                    "woocommerce_after_cart" => __(
                        "After cart area",
                        "inpost-pay"
                    ),
                ];

                foreach (
                    $cartPlaces
                    as $value => $label
                ) {
                    $selected =
                        $value == esc_attr(get_option("izi_place_basket"))
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
            <select name="izi_align_basket">
                <option>
                    <?php _e("Select", "inpost-pay"); ?>
                </option>
                <?php

                foreach (
                    $availableAligns
                    as $value => $label
                ) {
                    $selected =
                        $value == esc_attr(get_option("izi_align_basket"))
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
<table class="gui-settings-table my-2">
    <tr>
        <td>
            <?php _e("Margin top", "inpost-pay"); ?>
        </td>
        <td class="input-tooltip d-flex-align-center">
            <input type="number" name="izi_button_cart_margin[top]" value="<?= isset(
                $button_cart_margin["top"]
            )
                ? (int)$button_cart_margin["top"]
                : "" ?>">
        </td>
    </tr>
    <tr>
        <td>
            <?php _e("Margin left", "inpost-pay"); ?>
        </td>
        <td class="input-tooltip d-flex-align-center">
            <input type="number" name="izi_button_cart_margin[left]" value="<?= isset(
                $button_cart_margin["left"]
            )
                ? (int)$button_cart_margin["left"]
                : "" ?>">
        </td>
    </tr>
    <tr>
        <td>
            <?php _e("Margin right", "inpost-pay"); ?>
        </td>
        <td class="input-tooltip d-flex-align-center">
            <input type="number" name="izi_button_cart_margin[right]" value="<?= isset(
                $button_cart_margin["right"]
            )
                ? (int)$button_cart_margin["right"]
                : "" ?>">
        </td>
    </tr>
    <tr>
        <td>
            <?php _e("Margin bottom", "inpost-pay"); ?>
        </td>
        <td class="input-tooltip d-flex-align-center">
            <input type="number" name="izi_button_cart_margin[bottom]" value="<?= isset(
                $button_cart_margin["bottom"]
            )
                ? (int)$button_cart_margin["bottom"]
                : "" ?>">
        </td>
    </tr>
</table>
<table class="gui-settings-table">
    <tr>
        <td>
            <?php _e("Padding top", "inpost-pay"); ?>
        </td>
        <td class="input-tooltip d-flex-align-center">
            <input type="number" name="izi_button_cart_padding[top]" value="<?= isset(
                $button_cart_padding["top"]
            )
                ? (int)$button_cart_padding["top"]
                : "" ?>">
        </td>
    </tr>
    <tr>
        <td>
            <?php _e("Padding left", "inpost-pay"); ?>
        </td>
        <td class="input-tooltip d-flex-align-center">
            <input type="number" name="izi_button_cart_padding[left]" value="<?= isset(
                $button_cart_padding["left"]
            )
                ? (int)$button_cart_padding["left"]
                : "" ?>">
        </td>
    </tr>
    <tr>
        <td>
            <?php _e("Padding right", "inpost-pay"); ?>
        </td>
        <td class="input-tooltip d-flex-align-center">
            <input type="number" name="izi_button_cart_padding[right]" value="<?= isset(
                $button_cart_padding["right"]
            )
                ? (int)$button_cart_padding["right"]
                : "" ?>">
        </td>
    </tr>
    <tr>
        <td>
            <?php _e("Padding bottom", "inpost-pay"); ?>
        </td>
        <td class="input-tooltip d-flex-align-center">
            <input type="number" name="izi_button_cart_padding[bottom]" value="<?= isset(
                $button_cart_padding["bottom"]
            )
                ? (int)$button_cart_padding["bottom"]
                : "" ?>">
        </td>
    </tr>
</table>
<hr>
