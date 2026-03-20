<?php

namespace Ilabs\Inpost_Pay\Lib\exception;

class CantGetOrderObjectException extends \Exception {

	/**
	 * @param $order_id
	 */
	public function __construct( $order_id ) {
		parent::__construct( 'Cant get order object for order id: ' . $order_id );
	}
}
