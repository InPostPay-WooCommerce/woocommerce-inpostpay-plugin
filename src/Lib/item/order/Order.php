<?php
/**
 * Order item.
 *
 * @package Inpost_Pay
 */

declare( strict_types=1 );

namespace Ilabs\Inpost_Pay\Lib\item\order;

use Ilabs\Inpost_Pay\Lib\helpers\JsonSerializationHelper;
use Ilabs\Inpost_Pay\Lib\Item;

/**
 * Represents order data.
 */
class Order extends Item implements \JsonSerializable {
	use JsonSerializationHelper;

	/**
	 * Order details.
	 *
	 * @var OrderDetails
	 */
	protected OrderDetails $order_details;

	/**
	 * Account information.
	 *
	 * @var AccountInfo
	 */
	protected AccountInfo $account_info;

	/**
	 * Invoice details.
	 *
	 * @var InvoiceDetails
	 */
	protected InvoiceDetails $invoice_details;

	/**
	 * Delivery data.
	 *
	 * @var Delivery
	 */
	protected Delivery $delivery;

	/**
	 * Consents list.
	 *
	 * @var array
	 */
	protected array $consents;

	/**
	 * Products list.
	 *
	 * @var array
	 */
	protected array $products;

	/**
	 * Serialize item to array.
	 *
	 * @return array
	 */
	public function jsonSerialize(): array {
		return $this->auto_serialize();
	}

	/**
	 * Get order details.
	 *
	 * @return OrderDetails
	 */
	public function get_order_details(): OrderDetails {
		return $this->order_details;
	}

	/**
	 * Set order details.
	 *
	 * @param OrderDetails $order_details Order details.
	 *
	 * @return self
	 */
	public function set_order_details( OrderDetails $order_details ): self {
		$this->order_details = $order_details;

		return $this;
	}

	/**
	 * Get account information.
	 *
	 * @return AccountInfo
	 */
	public function get_account_info(): AccountInfo {
		return $this->account_info;
	}

	/**
	 * Set account information.
	 *
	 * @param AccountInfo $account_info Account information.
	 *
	 * @return self
	 */
	public function set_account_info( AccountInfo $account_info ): self {
		$this->account_info = $account_info;

		return $this;
	}

	/**
	 * Get invoice details.
	 *
	 * @return InvoiceDetails
	 */
	public function get_invoice_details(): InvoiceDetails {
		return $this->invoice_details;
	}

	/**
	 * Set invoice details.
	 *
	 * @param InvoiceDetails $invoice_details Invoice details.
	 *
	 * @return self
	 */
	public function set_invoice_details( InvoiceDetails $invoice_details ): self {
		$this->invoice_details = $invoice_details;

		return $this;
	}

	/**
	 * Get delivery data.
	 *
	 * @return Delivery
	 */
	public function get_delivery(): Delivery {
		return $this->delivery;
	}

	/**
	 * Set delivery data.
	 *
	 * @param Delivery $delivery Delivery data.
	 *
	 * @return self
	 */
	public function set_delivery( Delivery $delivery ): self {
		$this->delivery = $delivery;

		return $this;
	}

	/**
	 * Get consents list.
	 *
	 * @return array
	 */
	public function get_consents(): array {
		return $this->consents;
	}

	/**
	 * Set consents list.
	 *
	 * @param array $consents Consents list.
	 *
	 * @return self
	 */
	public function set_consents( array $consents ): self {
		$this->consents = $consents;

		return $this;
	}

	/**
	 * Get products list.
	 *
	 * @return array
	 */
	public function get_products(): array {
		return $this->products;
	}

	/**
	 * Set products list.
	 *
	 * @param array $products Products list.
	 *
	 * @return self
	 */
	public function set_products( array $products ): self {
		$this->products = $products;

		return $this;
	}
}
