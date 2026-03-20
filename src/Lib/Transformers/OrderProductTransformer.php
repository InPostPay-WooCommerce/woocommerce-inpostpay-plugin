<?php

namespace Ilabs\Inpost_Pay\Lib\Transformers;

use Ilabs\Inpost_Pay\Lib\interfaces\ProductInterface;
use Ilabs\Inpost_Pay\Lib\item\Price;
use Ilabs\Inpost_Pay\Lib\item\Product;
use Ilabs\Inpost_Pay\Lib\item\Quantity;
use WC_Order;
use WC_Order_Item_Product;
use WC_Product;

class OrderProductTransformer extends ProductTransformer {

	/**
	 * @var mixed
	 */
	private WC_Order_Item_Product $order_item;

	private WC_Order $order;

	/**
	 * @var float|mixed
	 */
	protected $itemPriceNet = 0;
	/**
	 * @var float|mixed
	 */
	protected $itemPriceGross = 0;
	/**
	 * @var float|mixed
	 */
	protected $itemPriceVat = 0;
	/**
	 * @var float|mixed
	 */
	protected $itemPromoPriceNet = 0;
	/**
	 * @var float|mixed
	 */
	protected $itemPromoPriceGross = 0;
	/**
	 * @var float|mixed
	 */
	protected $itemPromoPriceVat = 0;


	public function __construct( WC_Product $product, WC_Order $order, WC_Order_Item_Product $order_item ) {
		parent::__construct( $product );

		$this->order_item = $order_item;

		$this->order = $order;
	}

	public function mapProductData( bool $isRelatedProduct = false ): ProductInterface {
		$product_data = parent::mapProductData( $isRelatedProduct );

		$product_data->set_variants( $this->mapProductVariables() );

		return $product_data;
	}

	public function readQuantity( $variation_id = null ): Quantity {
		$quantity = $this->readStockQuantity( false, $variation_id );

		$quantity->set_quantity( $this->order_item->get_quantity() );

		return $quantity;
	}

	public function readOrderItemFinalPrice(): Price {
		$quantity = $this->order_item->get_quantity();
		$price    = new Price();

		$priceIncludingTax = $this->order->get_line_subtotal( $this->order_item, true ) / $quantity;
		$priceExcludingTax = $this->order->get_line_subtotal( $this->order_item, false ) / $quantity;
		$vat               = $priceIncludingTax - $priceExcludingTax;

		$price->set_net( $priceExcludingTax );
		$price->set_gross( number_format( $priceIncludingTax, 2, '.', '' ) );
		$price->set_vat( number_format( $vat, 2, '.', '' ) );

		$this->itemPromoPriceNet   += $priceExcludingTax * $quantity;
		$this->itemPromoPriceGross += $priceIncludingTax * $quantity;
		$this->itemPromoPriceVat   += $vat * $quantity;

		return $price;
	}

	public function readCartProductBasePrice(): void {
		$quantity = $this->order_item->get_quantity();

		$price = $this->readProductBasePrice();


		$this->itemPriceNet   = $price->get_net() * $quantity;
		$this->itemPriceGross = $price->get_gross() * $quantity;
		$this->itemPriceVat   = $price->get_vat() * $quantity;

	}

	public function mapProductAttributes(): array {
		$array      = [];
		$hideprefix = '_';

		$formatted_meta    = array();
		$include_all       = false;
		$meta_data         = $this->order_item->get_meta_data();
		$hideprefix_length = strlen( $hideprefix );
		$product           = is_callable( array( $this, 'get_product' ) ) ? $this->get_product() : false;
		$order_item_name   = $this->order_item->get_name();

		foreach ( $meta_data as $meta ) {
			if ( empty( $meta->id ) || '' === $meta->value || ! is_scalar( $meta->value ) || ( $hideprefix_length && substr( $meta->key, 0, $hideprefix_length ) === $hideprefix ) ) {
				continue;
			}

			$meta->key     = rawurldecode( (string) $meta->key );
			$meta->value   = rawurldecode( (string) $meta->value );
			$attribute_key = str_replace( 'attribute_', '', $meta->key );
			$display_key   = wc_attribute_label( $attribute_key, $product );
			$display_value = wp_kses_post( $meta->value );

			if ( taxonomy_exists( $attribute_key ) ) {
				$term = get_term_by( 'slug', $meta->value, $attribute_key );
				if ( is_object( $term ) && ! is_wp_error( $term ) && $term->name ) {
					$display_value = $term->name;
				}
			}

			if ( ! $include_all && $product && $product->is_type( 'variation' ) && wc_is_attribute_in_product_name( $display_value, $order_item_name ) ) {
				continue;
			}

			if ( strlen( strip_tags( $display_value ) ) > 1 ) {
				$array[] = $this->mapProductAttribute( $display_key, $display_value );
			}
		}

		return $array;
	}

	/**
	 * @return float|int|mixed
	 */
	public function get_item_price_net() {
		return $this->itemPriceNet;
	}

	/**
	 * @return float|int|mixed
	 */
	public function get_item_price_gross() {
		return $this->itemPriceGross;
	}

	/**
	 * @return float|int|mixed
	 */
	public function get_item_price_vat() {
		return $this->itemPriceVat;
	}

	/**
	 * @return float|int|mixed
	 */
	public function get_item_promo_price_net() {
		return $this->itemPromoPriceNet;
	}

	/**
	 * @return float|int|mixed
	 */
	public function get_item_promo_price_gross() {
		return $this->itemPromoPriceGross;
	}

	/**
	 * @return float|int|mixed
	 */
	public function get_item_promo_price_vat() {
		return $this->itemPromoPriceVat;
	}


}
