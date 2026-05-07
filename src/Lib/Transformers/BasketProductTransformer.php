<?php
/**
 * Basket product transformer.
 *
 * @package Ilabs\Inpost_Pay
 */

namespace Ilabs\Inpost_Pay\Lib\Transformers;

use Ilabs\Inpost_Pay\Integration\Basket\Quantity\QuantityIntegrationFactory;
use Ilabs\Inpost_Pay\Lib\interfaces\ProductInterface;
use Ilabs\Inpost_Pay\Lib\item\Price;
use Ilabs\Inpost_Pay\Lib\item\Quantity;
use WC_Product;

/**
 * Transforms WC cart item data into product data objects.
 */
final class BasketProductTransformer extends ProductTransformer {

	private array $cart_item;
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
	 * @param WC_Product $product   WooCommerce product.
	 * @param mixed      $cart_item Cart item data.
	 */
	public function __construct( WC_Product $product, $cart_item ) {
		parent::__construct( $product );

		$this->cart_item = $cart_item;
	}

	/**
	 * Maps product data including variants.
	 *
	 * @param bool $is_related_product Whether this is a related product.
	 *
	 * @return ProductInterface
	 */
	public function map_product_data( bool $is_related_product = false ): ProductInterface {
		$product_data = parent::map_product_data();

		$product_data->set_variants( $this->map_product_variables() );

		return $product_data;
	}

	/**
	 * Returns unique product identifier including cart item key.
	 *
	 * @return string
	 */
	public function get_product_identifier(): string {
		if ( isset( $this->cart_item['key'] ) ) {
			return $this->cart_item['data']->get_id() . ':' . $this->cart_item['key'];
		}

		return parent::get_product_identifier();
	}

	/**
	 * Reads quantity from cart item.
	 *
	 * @param mixed $variation_id Optional variation ID.
	 *
	 * @return Quantity
	 */
	public function read_quantity( $variation_id = null ): Quantity {
		$quantity = $this->read_stock_quantity( false, $variation_id );

		$quantity->set_quantity(
			( (int) $this->cart_item['quantity'] === $this->cart_item['quantity'] )
			? $this->cart_item['quantity']
			: number_format( $this->cart_item['quantity'], 2, '.', '' )
		);

		return $quantity;
	}

	/**
	 * Reads base price from cart item.
	 *
	 * @return Price
	 */
	public function read_cart_product_base_price(): Price {
		$quantity = $this->cart_item['quantity'];
		$price    = new Price();

		$price_including_tax = wc_get_price_including_tax( $this->wc_product, array( 'price' => $this->wc_product->get_regular_price() ) );
		$price_excluding_tax = wc_get_price_excluding_tax( $this->wc_product, array( 'price' => $this->wc_product->get_regular_price() ) );
		$vat                 = $price_including_tax - $price_excluding_tax;

		$price->set_gross( number_format( $price_including_tax, 2, '.', '' ) );
		$price->set_net( $price_excluding_tax );
		$price->set_vat( number_format( $vat, 2, '.', '' ) );

		$this->item_price_net   = $price_excluding_tax * $quantity;
		$this->item_price_gross = $price_including_tax * $quantity;
		$this->item_price_vat   = $vat * $quantity;

		return $price;
	}

	/**
	 * Reads promo price from cart item.
	 *
	 * @return Price
	 */
	public function read_cart_product_promo_price(): Price {
		$quantity = $this->cart_item['quantity'];

		if ( $this->wc_product->is_type( 'compositepro' ) ) {
			// Store YES or NO.
			$compositepro_per_item_shipping = get_post_meta( $this->wc_product->get_id(), '_compositepro_per_item_shipping', true );

			$compositepro_per_item_pricing = get_post_meta( $this->wc_product->get_id(), '_compositepro_per_item_pricing', true );

			if ( 'no' === $compositepro_per_item_pricing && 'no' === $compositepro_per_item_shipping ) {
				$price = new Price();

				$price_including_tax = wc_get_price_including_tax( $this->wc_product );
				$price_excluding_tax = wc_get_price_excluding_tax( $this->wc_product );
				$vat                 = $price_including_tax - $price_excluding_tax;

				$price->set_net( $price_excluding_tax );
				$price->set_gross( number_format( $price_including_tax, 2, '.', '' ) );
				$price->set_vat( number_format( $vat, 2, '.', '' ) );

				return $price;
			}
		}

		$line_total_key = 'subtotal';

		if ( ! isset( $this->cart_item['line_tax_data'][ $line_total_key ] ) ) {
			$this->cart_item['line_tax_data'][ $line_total_key ] = array();
		}

		$price = new Price();

		$tax_total = \WC_Tax::get_tax_total( $this->cart_item['line_tax_data'][ $line_total_key ] );

		$product_quantity = ( new QuantityIntegrationFactory() )->create( $this->wc_product );

		if ( 'DECIMAL' === $product_quantity->get_quantity_type() ) {
			$price_including_tax = ( $this->cart_item[ "line_$line_total_key" ] + $tax_total );
			$price_excluding_tax = $this->cart_item[ "line_$line_total_key" ];
			$vat                 = $tax_total;
		} else {
			$price_including_tax = ( $this->cart_item[ "line_$line_total_key" ] + $tax_total ) / $quantity;
			$price_excluding_tax = $this->cart_item[ "line_$line_total_key" ] / $quantity;
			$vat                 = $tax_total / $quantity;
		}

		$this->item_promo_price_net   = $this->cart_item[ "line_$line_total_key" ];
		$this->item_promo_price_gross = $this->cart_item[ "line_$line_total_key" ] + $tax_total;
		$this->item_promo_price_vat   = $tax_total;

		$price->set_gross( number_format( $price_including_tax, 2, '.', '' ) );
		$price->set_net( $price_excluding_tax );
		$price->set_vat( number_format( $vat, 2, '.', '' ) );

		return $price;
	}

	/**
	 * Maps product attributes from variation and cart item data.
	 *
	 * @return array
	 */
	public function map_product_attributes(): array {
		$array     = array();
		$item_data = array();

		// Variation values are shown only if they are not found in the title as of 3.0.
		// This is because variation titles display the attributes.
		if ( $this->cart_item['data']->is_type( 'variation' ) && is_array( $this->cart_item['variation'] ) ) {
			foreach ( $this->cart_item['variation'] as $name => $value ) {
				$taxonomy = wc_attribute_taxonomy_name( str_replace( 'attribute_pa_', '', urldecode( $name ) ) );

				if ( taxonomy_exists( $taxonomy ) ) {
					// If this is a term slug, get the term's nice name.
					$term = get_term_by( 'slug', $value, $taxonomy );
					if ( ! is_wp_error( $term ) && $term && $term->name ) {
						$value = $term->name;
					}
					$label = wc_attribute_label( $taxonomy );
				} else {
					// If this is a custom option slug, get the options name.
					$value = apply_filters( 'woocommerce_variation_option_name', $value, null, $taxonomy, $this->cart_item['data'] );
					$label = wc_attribute_label( str_replace( 'attribute_', '', $name ), $this->cart_item['data'] );
				}

				// Check the nicename against the title.
				if ( '' === $value || wc_is_attribute_in_product_name( $value, $this->cart_item['data']->get_name() ) ) {
					continue;
				}

				if ( ! strlen( $value ) ) {
					continue;
				}

				$item_data[] = array(
					'key'   => $label,
					'value' => $value,
				);
			}
		}

		// Filter item data to allow 3rd parties to add more to the array.
		$item_data = apply_filters( 'woocommerce_get_item_data', $item_data, $this->cart_item );

		if ( is_array( $item_data ) ) {
			// Format item data ready to display.
			foreach ( $item_data as $key => $data ) {
				// Set hidden to true to not display meta on cart.
				if ( ! empty( $data['hidden'] ) ) {
					unset( $item_data[ $key ] );
					continue;
				}
				$item_data[ $key ]['key']     = ! empty( $data['key'] ) ? $data['key'] : $data['name'];
				$item_data[ $key ]['display'] = ! empty( $data['display'] ) ? $data['display'] : $data['value'];
			}

			// Output flat or in list format.
			if ( count( $item_data ) > 0 ) {
				foreach ( $item_data as $data ) {
					$attribute_value = $data['value'] ?? $data['display'];
					$sanitized_value = wp_kses_post( $attribute_value );
					if ( strlen( strip_tags( $sanitized_value ) ) > 1 ) {
						$array[] = $this->map_product_attribute( $data['key'], $sanitized_value );
					}
				}
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
