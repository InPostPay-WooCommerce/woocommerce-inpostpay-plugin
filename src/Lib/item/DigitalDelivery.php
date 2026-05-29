<?php
/**
 * Digital delivery item.
 *
 * @package Ilabs\Inpost_Pay\Lib\item
 */

namespace Ilabs\Inpost_Pay\Lib\item;

use Ilabs\Inpost_Pay\Lib\helpers\DigitalProduct;
use Ilabs\Inpost_Pay\Lib\Item;

/**
 * Represents a digital product delivery with a calculated delivery date.
 */
class DigitalDelivery extends Item {

	protected string $delivery_type = DigitalProduct::DELIVERY_TYPE_DIGITAL;
	protected array $delivery_price = array(
		'net'   => 0,
		'gross' => 0,
		'vat'   => 0,
	);
	protected string $delivery_date;

	/**
	 * Initializes digital delivery with a delivery date two days from now.
	 */
	public function __construct() {
		$this->delivery_date = gmdate(
			'Y-m-d\T12:00:00.000\Z',
			strtotime( ' + 2 day' )
		);
	}
}
