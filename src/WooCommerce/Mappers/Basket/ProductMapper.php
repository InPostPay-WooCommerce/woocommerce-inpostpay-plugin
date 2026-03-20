<?php

namespace Ilabs\Inpost_Pay\WooCommerce\Mappers\Basket;

use Ilabs\Inpost_Pay\Lib\interfaces\ProductInterface;
use Ilabs\Inpost_Pay\Lib\Transformers\BasketProductTransformer;
use Ilabs\Inpost_Pay\Logger;
use Ilabs\Inpost_Pay\Lib\Shipping\ProductDeliveryChecker;
use Ilabs\Inpost_Pay\Lib\helpers\WooProductHelper;
use Ilabs\Inpost_Pay\WooCommerce\Cart\CartValidator;
use Ilabs\Inpost_Pay\WooCommerce\Price\BasketPriceCalculator;
use Ilabs\Inpost_Pay\WooCommerce\Price\OmnibusPrice;
use WC_Product;
use function Ilabs\Inpost_Pay\inpost_pay_container;

class ProductMapper {
	private BasketPriceCalculator $price_calculator;
	private CartValidator $cart_validator;
	private array $relatedProductIds = [];

	public function __construct(
		BasketPriceCalculator $price_calculator,
		CartValidator $cart_validator
	) {
		$this->price_calculator = $price_calculator;
		$this->cart_validator   = $cart_validator;
	}

	/**
	 * Map cart products to product interfaces
	 *
	 * @param array $cartContents The cart contents
	 *
	 * @return array The mapped products
	 */
	public function mapProducts( array $cartContents ): array {
		if ( empty( $cartContents ) ) {
			return [];
		}

		$mappedProducts = $this->mapValidCartProducts( $cartContents );
		$this->updateRelatedProductIds( $mappedProducts );

		return $mappedProducts;
	}

	/**
	 * Maps only valid products from the cart contents
	 *
	 * @param array $cartContents The cart contents to map
	 *
	 * @return array The mapped products
	 */
	private function mapValidCartProducts( array $cartContents ): array {
		$mappedProducts = [];

		foreach ( $cartContents as $cartItem ) {
			if ( $this->cart_validator->canAddProduct( $cartItem ) ) {
				$mappedProducts[] = $this->mapCartProduct( $cartItem );
			}
		}

		return $mappedProducts;
	}

	/**
	 * Map a single cart product
	 *
	 * @param array $cartContent The cart content
	 *
	 * @return ProductInterface The mapped product
	 */
	public function mapCartProduct( array $cartContent ): ProductInterface {
		/** @var WC_Product $wcProduct */
		$wcProduct                = $cartContent['data'];
		$basketProductTransformer = new BasketProductTransformer( $wcProduct, $cartContent );
		$product                  = $basketProductTransformer->mapProductData();

		// Collect related product IDs
		$this->collectRelatedProductIds( $wcProduct );

		// Set variation ID
		$variationId = $this->determineVariationId( $wcProduct, $cartContent );


		// Set quantity and prices
		$product->set_quantity( $basketProductTransformer->readQuantity( $variationId ) );

		$basePrice  = $basketProductTransformer->readCartProductBasePrice();
		$promoPrice = $basketProductTransformer->readCartProductPromoPrice();

		Logger::log( 'BasePriceObject: ' . var_export( $basePrice, true ) );
		Logger::log( 'PromoPriceObject: ' . var_export( $promoPrice, true ) );

		$product->set_base_price( $basePrice );
		$product->set_promo_price( $promoPrice );

		// Accumulate product prices
		$this->price_calculator->accumulateProductPrices( $basketProductTransformer );

		// Add delivery product
		$this->addDeliveryProduct( $product, $wcProduct, $cartContent );

		// Add omnibus price
		$this->addOmnibusPrice( $product, $cartContent );

		Logger::log( $product );

		return $product;
	}

	/**
	 * Determines the variation ID for a product
	 *
	 * @param WC_Product $product The WooCommerce product
	 * @param array $cartContent The cart content data
	 *
	 * @return int|null The variation ID or null if not applicable
	 */
	private function determineVariationId( WC_Product $product, array $cartContent ): ?int {
		if ( $product instanceof \WC_Product_Variation ) {
			return $product->get_id();
		}

		if ( ! empty( $cartContent['variation_id'] ) ) {
			return $cartContent['variation_id'];
		}

		return null;
	}

	/**
	 * Adds delivery product to the product if available
	 *
	 * @param ProductInterface $product The product to update
	 * @param WC_Product $wcProduct The WooCommerce product
	 * @param array $cartContent The cart content data
	 */
	private function addDeliveryProduct( ProductInterface $product, WC_Product $wcProduct, array $cartContent ): void {
		$deliveryProduct = ProductDeliveryChecker::get_delivery_options(
			$wcProduct,
			false,
			(int) $cartContent['quantity']
		);

		if ( $deliveryProduct ) {
			$product->set_delivery_product( $deliveryProduct );
		}
	}

	/**
	 * Adds omnibus price information to the product if applicable
	 *
	 * @param ProductInterface $product The product to update
	 * @param array $cartContent The cart content data
	 */
	private function addOmnibusPrice( ProductInterface $product, array $cartContent ): void {
		$omnibusPrice    = new OmnibusPrice();
		$promoCodesAdded = $omnibusPrice->hasOmnibusCoupons();

		if ( $promoCodesAdded && inpost_pay()->omnibus_enabled() ) {
			$lowestPrice = $omnibusPrice->readCartProductLowestPrice( $cartContent );
			if ( $lowestPrice ) {
				$product->set_lowest_price( $lowestPrice );
			}
		}
	}

	/**
	 * Collects related product IDs for a product
	 *
	 * @param WC_Product $productSimple The WooCommerce product
	 */
	public function collectRelatedProductIds( WC_Product $productSimple ): void {
		$parentId      = $productSimple->get_parent_id();
		$parentProduct = wc_get_product( $parentId );
		if ( $parentId && $parentProduct ) {
			$relatedIds = array_merge(
				$parentProduct->get_cross_sell_ids(),
				$parentProduct->get_upsell_ids()
			);
		} else {
			$relatedIds = array_merge(
				$productSimple->get_cross_sell_ids(),
				$productSimple->get_upsell_ids()
			);
		}

		/**
		 * Get from container DI.
		 *
		 * @var WooProductHelper $product_helper
		 */
		$product_helper = inpost_pay_container()->get( WooProductHelper::SERVICE_KEY );
		$products       = $product_helper->load_products_safe( $relatedIds );

		foreach ( $products as $id => $product ) {
			if ( $product ) {
				$this->relatedProductIds[] = $id;
			} else {
				Logger::log( '[PRODUCT_MAPPER] Invalid related product ID: ' . $id );
			}
		}
	}

	/**
	 * Updates the related product IDs by removing products that are already in the cart
	 *
	 * @param array $mappedProducts The mapped products in the cart
	 */
	private function updateRelatedProductIds( array $mappedProducts ): void {
		$this->relatedProductIds = array_unique( $this->relatedProductIds );

		foreach ( $mappedProducts as $product ) {
			$index = array_search( $product->product_id, $this->relatedProductIds, true );
			if ( $index !== false ) {
				unset( $this->relatedProductIds[ $index ] );
				$this->relatedProductIds = array_values( $this->relatedProductIds );
			}
		}
	}

	/**
	 * Gets the related product IDs
	 *
	 * @return array The related product IDs
	 */
	public function getRelatedProductIds(): array {
		return $this->relatedProductIds;
	}
}
