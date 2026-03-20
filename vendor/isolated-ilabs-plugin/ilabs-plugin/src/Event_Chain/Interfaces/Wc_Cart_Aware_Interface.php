<?php

declare (strict_types=1);
namespace Isolated\Inpost_Pay\Ilabs\Ilabs_Plugin\Event_Chain\Interfaces;

use WC_Cart;
interface Wc_Cart_Aware_Interface
{
    public function get_cart() : WC_Cart;
}
