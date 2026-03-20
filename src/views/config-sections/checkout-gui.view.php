<h3>
    <?php _e("Checkout", "inpost-pay"); ?>
</h3>
<table class="gui-settings-table">
    <tr class="d-flex-align-center">
        <td>
            <?php _e("Show", "inpost-pay"); ?>
        </td>
        <td class="input-tooltip d-flex-align-center">
            <input <?= esc_attr(
                get_option("izi_show_checkout")
            ) == 1
                ? "checked"
                : "" ?> type="checkbox" name="izi_show_checkout" value="1">
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
            <select name="izi_place_checkout">
                <option>
                    <?php _e("Select", "inpost-pay"); ?>
                </option>
                <?php
                $checkoutPlaces = [
                    "woocommerce_before_checkout_form" => __(
                        "Before checkout form",
                        "inpost-pay"
                    ),
                    "woocommerce_checkout_before_customer_details" => __(
                        "Before customer details",
                        "inpost-pay"
                    ),
                    "woocommerce_before_checkout_billing_form" => __(
                        "Before billing form",
                        "inpost-pay"
                    ),
                    "woocommerce_after_checkout_billing_form" => __(
                        "After billing form",
                        "inpost-pay"
                    ),
                    "woocommerce_before_checkout_shipping_form" => __(
                        "Before shipping form",
                        "inpost-pay"
                    ),
                    "woocommerce_after_checkout_shipping_form" => __(
                        "After shipping form",
                        "inpost-pay"
                    ),
                    "woocommerce_checkout_after_customer_details" => __(
                        "After customer details",
                        "inpost-pay"
                    ),
                    "woocommerce_checkout_before_order_review" => __(
                        "Before order review",
                        "inpost-pay"
                    ),
                    "woocommerce_checkout_after_order_review" => __(
                        "After order review",
                        "inpost-pay"
                    ),
                    "woocommerce_after_checkout_form" => __(
                        "After checkout form",
                        "inpost-pay"
                    ),
                ];
                $selectedCheckoutPlace = esc_attr(
                    get_option("izi_place_checkout")
                );
                foreach (
                    $checkoutPlaces
                    as $value => $label
                ) {
                    $selected =
                        $value == $selectedCheckoutPlace
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
            <select name="izi_align_checkout">
                <option>
                    <?php _e("Select", "inpost-pay"); ?>
                </option>
                <?php
                foreach (
                    $availableAligns
                    as $value => $label
                ) {
                    $selected =
                        $value == esc_attr(
							get_option("izi_align_checkout")
						)
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
