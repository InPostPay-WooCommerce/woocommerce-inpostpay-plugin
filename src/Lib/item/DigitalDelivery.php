<?php

namespace Ilabs\Inpost_Pay\Lib\item;

use Ilabs\Inpost_Pay\Lib\helpers\DigitalProduct;
use Ilabs\Inpost_Pay\Lib\Item;

class DigitalDelivery extends Item
{
    protected string $delivery_type = DigitalProduct::DELIVERY_TYPE_DIGITAL;
    protected array $delivery_price = [
		'net' => 0,
		'gross' => 0,
	    'vat' => 0
    ];
	protected string $delivery_date;

	public function __construct() {
		$this->delivery_date = date( "Y-m-d\T12:00:00.000\Z",
			strtotime( " + 2 day" ) );
	}
}


