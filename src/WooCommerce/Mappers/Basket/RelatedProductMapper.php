<?php

namespace Ilabs\Inpost_Pay\WooCommerce\Mappers\Basket;

use Ilabs\Inpost_Pay\Lib\helpers\EANHelper;
use Ilabs\Inpost_Pay\Lib\interfaces\ProductInterface;
use Ilabs\Inpost_Pay\Lib\item\Quantity;
use Ilabs\Inpost_Pay\Lib\item\RelatedProduct;
use Ilabs\Inpost_Pay\Lib\Transformers\ProductTransformer;
use Ilabs\Inpost_Pay\Logger;
use Ilabs\Inpost_Pay\Lib\Shipping\ProductDeliveryChecker;
use Ilabs\Inpost_Pay\Lib\helpers\WooProductHelper;
use Ilabs\Inpost_Pay\WooCommerce\Cart\CartValidator;
use WC_Product;
use function Ilabs\Inpost_Pay\inpost_pay_container;

class RelatedProductMapper {
	/**
	 * Map related products for the basket
	 *
	 * @param ProductMapper $productMapper Product mapper.
	 *
	 * @return array The mapped related products
	 */
	public function mapRelatedProducts( ProductMapper $productMapper ): array {
		$array = array();
		$max   = (int) esc_attr( get_option( 'izi_related_count' ) );

		if ( ! $max ) {
			return $array;
		}

		$count             = 0;
		$relatedProductIds = $productMapper->getRelatedProductIds();

		if ( empty( $relatedProductIds ) ) {
			return $array;
		}

		/**
		 * Get from container DI.
		 *
		 * @var WooProductHelper $product_helper
		 */
		$product_helper = inpost_pay_container()->get( WooProductHelper::SERVICE_KEY );
		$products       = $product_helper->load_products_safe( $relatedProductIds );

		foreach ( $products as $product ) {
			if ( ! $product ) {
				continue;
			}

			if ( ! CartValidator::canBeSuggestedProduct( $product ) ) {
				continue;
			}

			++$count;
			$array[] = $this->mapRelatedProduct( $product );

			if ( $count >= $max ) {
				break;
			}
		}

		return $array;
	}


	/**
	 * Map a single related product
	 *
	 * @param WC_Product $productSimple The product
	 *
	 * @return ProductInterface The mapped related product
	 */
	public function mapRelatedProduct( WC_Product $productSimple ): ProductInterface {
		$productTransformer = new ProductTransformer( $productSimple );

		$product = $productTransformer->map_product_data( true );
		$product->set_base_price( $productTransformer->read_product_base_price() );
		$product->set_promo_price( $productTransformer->read_product_promo_price() );

		$quantity = $productTransformer->read_stock_quantity();
		$quantity->set_quantity( 1 );
		$product->set_quantity( $quantity );

		$delivery_related_products = ProductDeliveryChecker::get_delivery_options(
			$productSimple,
			true,
		);

		$product->set_delivery_related_products( $delivery_related_products );

		return $product;
	}

	/**
	 * Map custom related products (static method)
	 *
	 * @param array $cartContents The cart contents
	 *
	 * @return array The mapped custom related products
	 */
	public static function mapCustomRelatedProducts( array $cartContents ): array {
		$relatedProducts = array();

		if ( empty( $cartContents ) ) {
			return $relatedProducts;
		}

		$firstProduct   = reset( $cartContents );
		$firstProductId = $firstProduct['product_id'] ?? 0;
		$max            = (int) get_option( 'izi_related_count', 2 );

		$relatedIds = wc_get_related_products( $firstProductId, $max );

		if ( empty( $relatedIds ) ) {
			return $relatedProducts;
		}

		/**
		 * Get from container DI.
		 *
		 * @var WooProductHelper $product_helper
		 */
		$product_helper  = inpost_pay_container()->get( WooProductHelper::SERVICE_KEY );
		$relatedProducts = $product_helper->load_products_safe( $relatedIds );
		$instance        = new self();
		$hasCoupons      = ! empty( WC()->cart->get_applied_coupons() );

		foreach ( $relatedProducts as $product ) {

			if ( ! CartValidator::canBeSuggestedProduct( $product ) ) {
				continue;
			}

			$transformer = new ProductTransformer( $product );
			$base_price  = $transformer->read_product_base_price();
			$promo_price = $transformer->read_product_promo_price();

			$available_quantity = $product->get_stock_quantity() ?: 999;

			$terms = get_the_terms( $product->get_id(), 'product_cat' );

			$related = new RelatedProduct();
			$related->set_product_id( (int) $product->get_id() );
			$related->set_product_category( $terms ? $terms[0]->term_id : 0 );
			$related->set_ean( EANHelper::prepareEan( $product ) );
			$related->set_product_name( $product->get_name() );
			$related->set_product_description( $product->get_description() );
			$related->set_product_link( $product->get_permalink() );
			$related->set_product_image( wp_get_attachment_url( $product->get_image_id() ) );
			$related->set_base_price( $base_price );
			$related->set_promo_price( $promo_price );

			$quantity = new Quantity();
			$quantity->set_quantity( 1 );
			$quantity->set_quantity_type( 'INTEGER' );
			$quantity->set_quantity_unit( 'pcs' );
			$quantity->set_available_quantity( $available_quantity );
			$quantity->set_max_quantity( $available_quantity );

			$related->set_quantity( $quantity );

			$related->set_product_attributes( $instance->getProductAttributes( $product ) );
			$related->set_lowest_price( $hasCoupons ? $promo_price : null );

			$delivery_related_products = ProductDeliveryChecker::get_delivery_options(
				$product,
				true,
			);

			$related->set_delivery_related_products( $delivery_related_products );

			$relatedProducts[] = $related;
		}

		Logger::log( 'RELATED AMOUNT : ' . count( $relatedProducts ) );

		return $relatedProducts;
	}

	public function getProductAttributes( WC_Product $product ): array {
		$attributes = array();

		$prod_atts = $product->get_attributes();
		foreach ( $prod_atts as $attribute ) {
			$attributes[] = array(
				'attribute_name'  => wc_attribute_label( $attribute->get_name() ),
				'attribute_value' => $product->get_attribute( $attribute->get_name() ),
			);
		}

		return $attributes;
	}
}
