<?php

namespace Ilabs\Inpost_Pay\Lib\Transformers;

use Ilabs\Inpost_Pay\Integration\Basket\Quantity\QuantityIntegrationFactory;
use Ilabs\Inpost_Pay\Lib\interfaces\ProductInterface;
use Ilabs\Inpost_Pay\Lib\item\Price;
use Ilabs\Inpost_Pay\Lib\item\Quantity;
use WC_Product;

final class BasketProductTransformer extends ProductTransformer {

	private array $cart_item;
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


	public function __construct( WC_Product $product, $cart_item ) {
		parent::__construct( $product );

		$this->cart_item = $cart_item;
	}

	public function mapProductData( bool $isRelatedProduct = false ): ProductInterface {
		$product_data = parent::mapProductData();

		$product_data->set_variants( $this->mapProductVariables() );

		return $product_data;
	}

	public function getProductIdentifier(): string {
		if ( isset( $this->cart_item['key'] ) ) {
			return $this->cart_item['data']->get_id() . ':' . $this->cart_item['key'];
		}

		return parent::getProductIdentifier();
	}

	public function readQuantity( $variation_id = null ): Quantity {
		$quantity = $this->readStockQuantity( false, $variation_id );

		$quantity->set_quantity( ( (int) $this->cart_item['quantity'] === $this->cart_item['quantity'] )
			? $this->cart_item['quantity']
			: number_format( $this->cart_item['quantity'], 2, '.', '' )
		);

		return $quantity;
	}

	public function readCartProductBasePrice(): Price {
		$quantity = $this->cart_item['quantity'];
		$price    = new Price();

		$priceIncludingTax = wc_get_price_including_tax( $this->wc_product, [ "price" => $this->wc_product->get_regular_price() ] );
		$priceExcludingTax = wc_get_price_excluding_tax( $this->wc_product, [ "price" => $this->wc_product->get_regular_price() ] );
		$vat               = $priceIncludingTax - $priceExcludingTax;

		$price->set_gross( number_format( $priceIncludingTax, 2, '.', '' ) );
		$price->set_net( $priceExcludingTax );
		$price->set_vat( number_format( $vat, 2, '.', '' ) );

		$this->itemPriceNet   = $priceExcludingTax * $quantity;
		$this->itemPriceGross = $priceIncludingTax * $quantity;
		$this->itemPriceVat   = $vat * $quantity;

		return $price;
	}

	public function readCartProductPromoPrice(): Price {
		$quantity = $this->cart_item['quantity'];


		if ( $this->wc_product->is_type( 'compositepro' ) ) {
			//Store YES or NO
			$compositepro_per_item_shipping = get_post_meta( $this->wc_product->get_id(), '_compositepro_per_item_shipping' );

			$compositepro_per_item_pricing = get_post_meta( $this->wc_product->get_id(), '_compositepro_per_item_pricing' );

			if ( $compositepro_per_item_pricing === 'no' && $compositepro_per_item_shipping === 'no' ) {
				$price = new Price();

				$priceIncludingTax = wc_get_price_including_tax( $this->wc_product );
				$priceExcludingTax = wc_get_price_excluding_tax( $this->wc_product );
				$vat               = $priceIncludingTax - $priceExcludingTax;

				$price->set_net( $priceExcludingTax );
				$price->set_gross( number_format( $priceIncludingTax, 2, '.', '' ) );
				$price->set_vat( number_format( $vat, 2, '.', '' ) );


				return $price;
			}

		}

		$lineTotalKey = 'subtotal';

		if ( ! isset( $this->cart_item['line_tax_data'][ $lineTotalKey ] ) ) {
			$this->cart_item['line_tax_data'][ $lineTotalKey ] = [];
		}

		$price = new Price();

		$tax_total = \WC_Tax::get_tax_total( $this->cart_item['line_tax_data'][ $lineTotalKey ] );

		$productQuantity = ( new QuantityIntegrationFactory() )->create( $this->wc_product );

		if ( $productQuantity->get_quantity_type() === 'DECIMAL' ) {
			$priceIncludingTax = ( $this->cart_item["line_$lineTotalKey"] + $tax_total );
			$priceExcludingTax = $this->cart_item["line_$lineTotalKey"];
			$vat               = $tax_total;
		} else {
			$priceIncludingTax = ( $this->cart_item["line_$lineTotalKey"] + $tax_total ) / $quantity;
			$priceExcludingTax = $this->cart_item["line_$lineTotalKey"] / $quantity;
			$vat               = $tax_total / $quantity;
		}


		$this->itemPromoPriceNet   = $this->cart_item["line_$lineTotalKey"];
		$this->itemPromoPriceGross = $this->cart_item["line_$lineTotalKey"] + $tax_total;
		$this->itemPromoPriceVat   = $tax_total;

		$price->set_gross( number_format( $priceIncludingTax, 2, '.', '' ) );
		$price->set_net( $priceExcludingTax );
		$price->set_vat( number_format( $vat, 2, '.', '' ) );

		return $price;
	}

	public function mapProductAttributes(): array {
		$array     = [];
		$item_data = [];

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
					if ( strlen( strip_tags( wp_kses_post( $data['display'] ) ) ) > 1 ) {
						$array[] = $this->mapProductAttribute( $data['key'], wp_kses_post( $data['display'] ) );
					}
				}
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
