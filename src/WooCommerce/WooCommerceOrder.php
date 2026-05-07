<?php

namespace Ilabs\Inpost_Pay\WooCommerce;

use Ilabs\Inpost_Pay\IziJsonResponse;
use Ilabs\Inpost_Pay\Lib\exception\CantGetOrderObjectException;
use Ilabs\Inpost_Pay\Lib\helpers\CacheHelper;
use Ilabs\Inpost_Pay\Lib\helpers\HPOSHelper;
use Ilabs\Inpost_Pay\Lib\item\order\Order;
use Ilabs\Inpost_Pay\Lib\OrderAliasHelper;
use Ilabs\Inpost_Pay\Logger;
use Ilabs\Inpost_Pay\WooCommerce\Mappers\Order\AccountInfoMapper;
use Ilabs\Inpost_Pay\WooCommerce\Mappers\Order\ConsentsMapper;
use Ilabs\Inpost_Pay\WooCommerce\Mappers\Order\DeliveryMapper;
use Ilabs\Inpost_Pay\WooCommerce\Mappers\Order\InvoiceDetailsMapper;
use Ilabs\Inpost_Pay\WooCommerce\Mappers\Order\OrderDetailsMapper;
use Ilabs\Inpost_Pay\WooCommerce\Mappers\Order\ProductsMapper;

class WooCommerceOrder extends IziJsonResponse {
	private $orderId;
	private $originalOrderId;
	private $order;
	private HPOSHelper $HPOSHelper;

	public function __construct( $orderId, $order = null ) {
		$this->orderId = (string) $orderId;
		$this->resolve_order_ids( $order );
		$this->HPOSHelper = new HPOSHelper( $this->order );
	}

	public function get_order_id() {
		return $this->orderId;
	}

	public function get_original_orderid() {
		return $this->originalOrderId;
	}

	public function getOrderObject() {
		return $this->order;
	}

	/**
	 * @throws CantGetOrderObjectException
	 */
	public static function getOrder( $orderId, $order = null ): Order {
		$wooCommerceOrder = new self( $orderId, $order );
		$order            = new Order();
		CacheHelper::disable_wp_cache();

		if ( ! $wooCommerceOrder->getOrderObject() ) {
			throw new CantGetOrderObjectException( $orderId );
		}

		$accountInfoMapper    = new AccountInfoMapper( $wooCommerceOrder->getOrderObject(), $wooCommerceOrder->HPOSHelper, $wooCommerceOrder->originalOrderId );
		$invoiceDetailsMapper = new InvoiceDetailsMapper( $wooCommerceOrder->getOrderObject(), $wooCommerceOrder->HPOSHelper );
		$deliveryMapper       = new DeliveryMapper( $wooCommerceOrder->getOrderObject(), $wooCommerceOrder->HPOSHelper );
		$productsMapper       = new ProductsMapper( $wooCommerceOrder->getOrderObject() );
		$orderDetailsMapper   = new OrderDetailsMapper(
			$wooCommerceOrder->getOrderObject(),
			$wooCommerceOrder->HPOSHelper,
			$wooCommerceOrder->orderId,
			$productsMapper->getCalculatedPriceTotals()
		);
		$consentsMapper       = new ConsentsMapper( $wooCommerceOrder->HPOSHelper );

		$order->set_account_info( $accountInfoMapper->map() );
		$order->set_invoice_details( $invoiceDetailsMapper->map() );

		$delivery = $deliveryMapper->map();
		$order->set_delivery( $delivery );

		$order->set_products( $productsMapper->map() );
		$order->set_order_details( $orderDetailsMapper->map( $delivery->get_delivery_price(), $delivery->get_delivery_options() ) );

		$consents = unserialize( $wooCommerceOrder->HPOSHelper->get_meta( 'inpost_consents' ) );
		$order->set_consents( $consents ?: $consentsMapper->map() );

		Logger::debug( sprintf( 'Order object: [%s]', print_r( $order, true ) ) );

		return $order;
	}

	/**
	 * Resolves the order object or the original order ID from the given WC_Order object or order ID.
	 *
	 * If the given order object is not null, it sets the order object and the original order ID from that object.
	 * If the given order object is null, it resolves the order object from the order ID using the OrderAliasHelper.
	 * If the resolved order object is not null, it sets the original order ID from that object.
	 *
	 * @param \WC_Order|null $order Optional WC_Order object.
	 */
	private function resolve_order_ids( $order = null ): void {
		if ( null !== $order ) {
			$this->order           = $order;
			$this->originalOrderId = (string) $order->get_id();

			return;
		}

		$this->order = OrderAliasHelper::resolve( $this->orderId );

		if ( $this->order ) {
			$this->originalOrderId = $this->order->get_id();
		}
	}
}
