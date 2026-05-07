<?php
/**
 * Order product transformer.
 *
 * @package Ilabs\Inpost_Pay
 */

namespace Ilabs\Inpost_Pay\Lib\Transformers;

use Ilabs\Inpost_Pay\Lib\interfaces\ProductInterface;
use Ilabs\Inpost_Pay\Lib\item\Price;
use Ilabs\Inpost_Pay\Lib\item\Product;
use Ilabs\Inpost_Pay\Lib\item\Quantity;
use WC_Order;
use WC_Order_Item_Product;
use WC_Product;

/**
 * Transforms WC order item data into product data objects.
 */
class OrderProductTransformer extends ProductTransformer {

	/**
	 * WooCommerce order item.
	 *
	 * @var mixed
	 */
	private WC_Order_Item_Product $order_item;

	private WC_Order $order;

	/**
	 * Item price net amount.
	 *
	 * @var float|mixed
	 */
	protected $item_price_net = 0;
	/**
	 * Item price gross amount.
	 *
	 * @var float|mixed
	 */
	protected $item_price_gross = 0;
	/**
	 * Item price VAT amount.
	 *
	 * @var float|mixed
	 */
	protected $item_price_vat = 0;
	/**
	 * Item promo price net amount.
	 *
	 * @var float|mixed
	 */
	protected $item_promo_price_net = 0;
	/**
	 * Item promo price gross amount.
	 *
	 * @var float|mixed
	 */
	protected $item_promo_price_gross = 0;
	/**
	 * Item promo price VAT amount.
	 *
	 * @var float|mixed
	 */
	protected $item_promo_price_vat = 0;


	/**
	 * Constructor.
	 *
	 * @param WC_Product            $product    WooCommerce product.
	 * @param WC_Order              $order      WooCommerce order.
	 * @param WC_Order_Item_Product $order_item WooCommerce order item.
	 */
	public function __construct( WC_Product $product, WC_Order $order, WC_Order_Item_Product $order_item ) {
		parent::__construct( $product );

		$this->order_item = $order_item;

		$this->order = $order;
	}

	/**
	 * Maps product data including variants.
	 *
	 * @param bool $is_related_product Whether this is a related product.
	 *
	 * @return ProductInterface
	 */
	public function map_product_data( bool $is_related_product = false ): ProductInterface {
		$product_data = parent::map_product_data( $is_related_product );

		$product_data->set_variants( $this->map_product_variables() );

		return $product_data;
	}

	/**
	 * Reads quantity from order item.
	 *
	 * @param mixed $variation_id Optional variation ID.
	 *
	 * @return Quantity
	 */
	public function readQuantity( $variation_id = null ): Quantity {
		$quantity = $this->read_stock_quantity( false, $variation_id );

		$quantity->set_quantity( $this->order_item->get_quantity() );

		return $quantity;
	}

	/**
	 * Reads final price from order item.
	 *
	 * @return Price
	 */
	public function readOrderItemFinalPrice(): Price {
		$quantity = $this->order_item->get_quantity();
		$price    = new Price();

		$price_including_tax = $this->order->get_line_subtotal( $this->order_item, true ) / $quantity;
		$price_excluding_tax = $this->order->get_line_subtotal( $this->order_item, false ) / $quantity;
		$vat                 = $price_including_tax - $price_excluding_tax;

		$price->set_net( $price_excluding_tax );
		$price->set_gross( number_format( $price_including_tax, 2, '.', '' ) );
		$price->set_vat( number_format( $vat, 2, '.', '' ) );

		$this->item_promo_price_net   += $price_excluding_tax * $quantity;
		$this->item_promo_price_gross += $price_including_tax * $quantity;
		$this->item_promo_price_vat   += $vat * $quantity;

		return $price;
	}

	/**
	 * Reads base price from order item.
	 */
	public function readCartProductBasePrice(): void {
		$quantity = $this->order_item->get_quantity();

		$price = $this->read_product_base_price();

		$this->item_price_net   = $price->get_net() * $quantity;
		$this->item_price_gross = $price->get_gross() * $quantity;
		$this->item_price_vat   = $price->get_vat() * $quantity;
	}

	/**
	 * Maps product attributes from order item meta data.
	 *
	 * @return array
	 */
	public function map_product_attributes(): array {
		$array      = array();
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
			$display_value = html_entity_decode( wp_strip_all_tags( $meta->value ), ENT_QUOTES | ENT_HTML5, 'UTF-8' );

			if ( taxonomy_exists( $attribute_key ) ) {
				$term = get_term_by( 'slug', $meta->value, $attribute_key );
				if ( is_object( $term ) && ! is_wp_error( $term ) && $term->name ) {
					$display_value = $term->name;
				}
			}

			if ( ! $include_all && $product && $product->is_type( 'variation' ) && wc_is_attribute_in_product_name( $display_value, $order_item_name ) ) {
				continue;
			}

			if ( strlen( wp_strip_all_tags( $display_value ) ) > 1 ) {
				$array[] = $this->map_product_attribute( $display_key, $display_value );
			}
		}

		return $array;
	}

	/**
	 * Returns item price net amount.
	 *
	 * @return float|int|mixed
	 */
	public function get_item_price_net() {
		return $this->item_price_net;
	}

	/**
	 * Returns item price gross amount.
	 *
	 * @return float|int|mixed
	 */
	public function get_item_price_gross() {
		return $this->item_price_gross;
	}

	/**
	 * Returns item price VAT amount.
	 *
	 * @return float|int|mixed
	 */
	public function get_item_price_vat() {
		return $this->item_price_vat;
	}

	/**
	 * Returns item promo price net amount.
	 *
	 * @return float|int|mixed
	 */
	public function get_item_promo_price_net() {
		return $this->item_promo_price_net;
	}

	/**
	 * Returns item promo price gross amount.
	 *
	 * @return float|int|mixed
	 */
	public function get_item_promo_price_gross() {
		return $this->item_promo_price_gross;
	}

	/**
	 * Returns item promo price VAT amount.
	 *
	 * @return float|int|mixed
	 */
	public function get_item_promo_price_vat() {
		return $this->item_promo_price_vat;
	}
}
