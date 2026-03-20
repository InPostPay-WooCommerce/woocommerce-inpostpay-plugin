<?php

declare (strict_types=1);
namespace Isolated\Inpost_Pay\Ilabs\Ilabs_Plugin\Event_Chain\Interfaces;

use WC_Order;
interface Wc_Order_Aware_Interface
{
    public function get_order() : WC_Order;
}
