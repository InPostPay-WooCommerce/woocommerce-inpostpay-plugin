<?php

namespace Ilabs\Inpost_Pay\WooCommerce;

use Ilabs\Inpost_Pay\Integration\PPOM\PpomCartHelper;
use Ilabs\Inpost_Pay\IziJsonResponse;
use Ilabs\Inpost_Pay\Lib\BasketIdentification;
use Ilabs\Inpost_Pay\Lib\BrowserIdStorage;
use Ilabs\Inpost_Pay\Lib\Coupons\PromotionsAvailable;
use Ilabs\Inpost_Pay\Lib\item\Basket;
use Ilabs\Inpost_Pay\Logger;
use Ilabs\Inpost_Pay\LoggerTrace;
use Ilabs\Inpost_Pay\models\CartSessionService;
use Ilabs\Inpost_Pay\WooCommerce\Cart\CartContentManager;
use Ilabs\Inpost_Pay\WooCommerce\Cart\CartValidator;
use Ilabs\Inpost_Pay\WooCommerce\Mappers\Basket\ConsentsMapper;
use Ilabs\Inpost_Pay\WooCommerce\Mappers\Basket\ProductMapper;
use Ilabs\Inpost_Pay\WooCommerce\Mappers\Basket\PromoCodeMapper;
use Ilabs\Inpost_Pay\WooCommerce\Mappers\Basket\RelatedProductMapper;
use Ilabs\Inpost_Pay\WooCommerce\Mappers\Basket\SummaryMapper;
use Ilabs\Inpost_Pay\WooCommerce\Price\BasketPriceCalculator;
use WooCommerce;
use function Ilabs\Inpost_Pay\inpost_pay_container;

class WooCommerceBasket extends IziJsonResponse {
	public static bool $hasCoupons  = false;
	public static bool $couponError = false;

	private CartSessionService $cart_session;

	public function __construct() {
		/**
		 * Get from container DI.
		 *
		 * @var CartSessionService $cart_session
		 */
		$this->cart_session = inpost_pay_container()->get( CartSessionService::SERVICE_KEY );
	}

	/**
	 * Gets the basket data
	 *
	 * @param bool $refresh Whether to refresh the cart contents
	 *
	 * @return Basket The basket object
	 */
	public static function getBasket( bool $refresh = true, ?string $cart_id = null ): Basket {
		Logger::log( 'creating basket' );
		$wooCommerceBasket = new self();

		return $wooCommerceBasket->mapBasket( WC(), $refresh, $cart_id );
	}

	/**
	 * Maps the WooCommerce cart data to a Basket object
	 *
	 * @param WooCommerce $wooCommerce The WooCommerce instance
	 * @param bool        $refresh Whether to refresh the cart contents
	 *
	 * @return Basket The mapped basket
	 */
	public function mapBasket( WooCommerce $wooCommerce, bool $refresh = true, ?string $cart_id = null ): Basket {
		global $wp_actions;

		if ( $refresh ) {
			BasketPriceCalculator::$totalsCalculatedInThisRequest = false;
		}

		// Prepare shipping defaults
		$this->prepareShippingDefaults();

		// Get cart contents
		$cartContentManager = new CartContentManager();
		$cartContents       = $cartContentManager->getCartContents( $wooCommerce );

		if ( $refresh && ! empty( $cartContents ) ) {
			$cartContents = $cartContentManager->refreshCartContents( $cartContents, $wp_actions );
			WC()->cart->calculate_totals();
			$cartContents = PpomCartHelper::maybe_fix_cart_contents_after_totals( $cartContents );
		}

		if ( null === $cart_id ) {
			try {
				$this->cart_session->store_current();
				$basket_id = BasketIdentification::get();
			} catch ( \JsonException $e ) {
				Logger::error( 'WooCommerceBasket.php: ' . $e->getMessage() );
			}
		} else {
			$basket_id = $cart_id;
		}

		// Add mapping
		$calculator           = new BasketPriceCalculator();
		$cart_validator       = new CartValidator();
		$productMapper        = new ProductMapper( $calculator, $cart_validator );
		$relatedProductMapper = new RelatedProductMapper();
		$promoCodeMapper      = new PromoCodeMapper();
		$summaryMapper        = new SummaryMapper( $calculator );
		$consentsMapper       = new ConsentsMapper();
		$wooDeliveryPrice     = new WooDeliveryPrice();
		$basket               = new Basket();

		$products         = $productMapper->mapProducts( $cartContents );
		$products         = PpomCartHelper::maybe_split_product_attributes( $products );
		$summary          = $summaryMapper->mapSummary();
		$promo_codes      = $promoCodeMapper->mapPromoCodes( $wooCommerce->cart );
		$delivery         = $wooDeliveryPrice->mapDelivery();
		$consents         = $consentsMapper->mapConsents();
		$related_products = $relatedProductMapper->mapRelatedProducts( $productMapper );

		$basket
			->set_basket_id( $basket_id )
			->set_products( $products )
			->set_summary( $summary )
			->set_promo_codes( $promo_codes )
			->set_delivery( $delivery )
			->set_consents( $consents )
			->set_related_products( $related_products );

		if ( ! empty( $wooDeliveryPrice->getIsTaxable() ) ) {
			try {
				$this->cart_session->set_cart_delivery_cache_by_id(
					BasketIdentification::get(),
					$wooDeliveryPrice->getIsTaxable()
				);
			} catch ( \JsonException $e ) {
				Logger::error( $e->getMessage() );
			}
		}

		// Add promotions available
		$promotions_available = ( new PromotionsAvailable() )->get_coupons();
		if ( $promotions_available ) {
			$basket->set_promotions_available( $promotions_available );
		}

		Logger::log( $basket );

		return $basket;
	}

	/**
	 * Prepares shipping defaults for the WooCommerce cart
	 */
	private function prepareShippingDefaults(): void {
		if ( ! WC()->customer instanceof \WC_Customer ) {
			return;
		}

		$customer = WC()->customer;
		if ( empty( $customer->get_shipping_country() ) ) {
			$customer->set_shipping_country( 'PL' );
		}
		if ( empty( $customer->get_shipping_postcode() ) ) {
			$customer->set_shipping_postcode( '00-000' );
		}
		if ( empty( $customer->get_shipping_city() ) ) {
			$customer->set_shipping_city( 'Warszawa' );
		}
	}
}
