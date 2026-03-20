<?php

namespace Ilabs\Inpost_Pay\WooCommerce\Mappers\Order;

use Ilabs\Inpost_Pay\Lib\interfaces\ProductInterface;
use Ilabs\Inpost_Pay\Lib\item\Product;
use Ilabs\Inpost_Pay\Lib\Transformers\OrderProductTransformer;

class ProductsMapper {
	private \WC_Order $order;
	private float $orderBasePriceNet = 0.0;
	private float $orderBasePriceGross = 0.0;
	private float $orderBasePriceVat = 0.0;
	private float $orderPromoPriceNet = 0.0;
	private float $orderPromoPriceGross = 0.0;
	private float $orderPromoPriceVat = 0.0;

	public function __construct( $order ) {
		$this->order = $order;
	}

	public function map(): array {
		$products = [];

		foreach ( $this->order->get_items() as $cartContent ) {
			if ( ! $cartContent->get_product() ) {
				continue;
			}
			$products[] = $this->mapCartProduct( $cartContent );
		}

		return $products;
	}

	private function mapCartProduct( $item ): ProductInterface {
		$simpleProduct           = $item->get_product();
		$orderProductTransformer = new OrderProductTransformer( $simpleProduct, $this->order, $item );

		/** @var Product $product */
		$product = $orderProductTransformer->mapProductData();

		if ( $simpleProduct instanceof \WC_Product_Variation ) {
			$variation_id = $simpleProduct->get_id();
		} else {
			$variation_id = $item->get_variation_id();
			if ( empty( $variation_id ) ) {
				$variation_id = null;
			}
		}

		$product->set_quantity( $orderProductTransformer->readQuantity( $variation_id ) );
		$orderProductTransformer->readCartProductBasePrice();
		$product->set_base_price( $orderProductTransformer->readOrderItemFinalPrice() );

		$this->orderBasePriceNet   += $orderProductTransformer->get_item_price_net();
		$this->orderBasePriceGross += $orderProductTransformer->get_item_price_gross();
		$this->orderBasePriceVat   += $orderProductTransformer->get_item_price_vat();

		$this->orderPromoPriceNet   += $orderProductTransformer->get_item_promo_price_net();
		$this->orderPromoPriceGross += $orderProductTransformer->get_item_promo_price_gross();
		$this->orderPromoPriceVat   += $orderProductTransformer->get_item_promo_price_vat();

		return $product;
	}

	public function getCalculatedPriceTotals(): array {
		return [
			'orderBasePriceNet'    => $this->orderBasePriceNet,
			'orderBasePriceGross'  => $this->orderBasePriceGross,
			'orderBasePriceVat'    => $this->orderBasePriceVat,
			'orderPromoPriceNet'   => $this->orderPromoPriceNet,
			'orderPromoPriceGross' => $this->orderPromoPriceGross,
			'orderPromoPriceVat'   => $this->orderPromoPriceVat,
		];
	}
}
