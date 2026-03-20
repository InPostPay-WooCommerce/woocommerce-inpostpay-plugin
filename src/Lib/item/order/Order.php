<?php

declare( strict_types=1 );

namespace Ilabs\Inpost_Pay\Lib\item\order;

use Ilabs\Inpost_Pay\Lib\helpers\JsonSerializationHelper;
use Ilabs\Inpost_Pay\Lib\Item;

class Order extends Item implements \JsonSerializable {
	use JsonSerializationHelper;

	protected OrderDetails $order_details;
	protected AccountInfo $account_info;
	protected InvoiceDetails $invoice_details;
	protected Delivery $delivery;
	protected array $consents;
	protected array $products;

	public function jsonSerialize(): array {
		return $this->autoSerialize();
	}

	public function get_order_details(): OrderDetails {
		return $this->order_details;
	}

	public function set_order_details( OrderDetails $order_details ): self {
		$this->order_details = $order_details;

		return $this;
	}

	public function get_account_info(): AccountInfo {
		return $this->account_info;
	}

	public function set_account_info( AccountInfo $account_info ): self {
		$this->account_info = $account_info;

		return $this;
	}

	public function get_invoice_details(): InvoiceDetails {
		return $this->invoice_details;
	}

	public function set_invoice_details( InvoiceDetails $invoice_details ): self {
		$this->invoice_details = $invoice_details;

		return $this;
	}

	public function get_delivery(): Delivery {
		return $this->delivery;
	}

	public function set_delivery( Delivery $delivery ): self {
		$this->delivery = $delivery;

		return $this;
	}

	public function get_consents(): array {
		return $this->consents;
	}

	public function set_consents( array $consents ): self {
		$this->consents = $consents;

		return $this;
	}

	public function get_products(): array {
		return $this->products;
	}

	public function set_products( array $products ): self {
		$this->products = $products;

		return $this;
	}
}
