<?php

/**
 * Thankyou page
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/checkout/thankyou.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 3.7.0
 */


defined('ABSPATH') || exit;


?>



    <?php
    global $wp;
    $order_id  = absint($wp->query_vars['order-received']);
    $order = wc_get_order($order_id);

    if (isset($order) && $order) :
        do_action('woocommerce_before_thankyou', $order->get_id());
    ?>

        <?php if ($order->has_status('failed')) : ?>
        <?php else : ?>
                <inpost-thank-you></inpost-thank-you>
				<div class="woocommerce-order"></div>
        <?php endif; ?>

    <?php else : ?>

    <?php endif; ?>


