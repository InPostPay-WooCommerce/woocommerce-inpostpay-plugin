<?php

namespace Ilabs\Inpost_Pay\Integration\Basket;

use Ilabs\Inpost_Pay\Lib\helpers\JsonSerializationHelper;
use Ilabs\Inpost_Pay\Lib\item\Basket;
use JsonSerializable;

class CustomBasketResponseMapper {
	use JsonSerializationHelper;

	public function map( Basket $basket ): array {
		$response = array(
			'basket_id'        => $basket->get_basket_id(),
			'summary'          => $this->mapSummary( $basket ),
			'delivery'         => $this->mapDelivery( $basket ),
			'promo_codes'      => $this->mapPromoCodes( $basket ),
			'products'         => $this->mapProducts( $basket ),
			'related_products' => $this->mapRelatedProducts( $basket ),
			'consents'         => $this->mapConsents( $basket ),
		);

		if ( ! empty( $basket->get_promotions_available() ) ) {
			$response['promotions_available'] = $this->mapPromotionsAvailable( $basket );
		}

		return $response;
	}

	protected function mapSummary( Basket $basket ): array {
		return $this->serialize_item( $basket->get_summary() );
	}

	protected function mapDelivery( Basket $basket ): array {
		return $this->serialize_array( $basket->get_delivery() );
	}

	protected function mapPromoCodes( Basket $basket ): array {
		return $this->serialize_array( $basket->get_promo_codes() );
	}

	protected function mapConsents( Basket $basket ): array {
		return $this->serialize_array( $basket->get_consents() );
	}

	protected function mapProducts( Basket $basket ): array {
		$lowest_price_required = ! empty( $basket->get_promo_codes() );

		return array_map(
			function ( $product ) use ( $lowest_price_required ) {
				$productArray = $this->serialize_item( $product );

				return $this->prepareProductResponse( $productArray, $lowest_price_required, true );
			},
			$basket->get_products()
		);
	}

	protected function mapRelatedProducts( Basket $basket ): array {
		return array_map(
			function ( $relatedProduct ) {
				$productArray = $this->serialize_item( $relatedProduct );

				return $this->prepareProductResponse( $productArray, false, false );
			},
			$basket->get_related_products()
		);
	}

	protected function mapPromotionsAvailable( Basket $basket ): array {
		return $this->serialize_array( $basket->get_promotions_available() );
	}

	private function prepareProductResponse( array $product, bool $lowest_price_required = false, bool $is_main_product = true ): array {
		unset( $product['additional_product_images'], $product['variants'] );

		if ( ! isset( $product['quantity'] ) ) {
			$product['quantity'] = array();
		}

		unset( $product['quantity']['quantity_jump'] );
		$product['quantity']['quantity_unit'] = 'pcs';

		$product['lowest_price'] = $lowest_price_required ? ( $product['promo_price'] ?? null ) : null;

		if ( $is_main_product && ( ! array_key_exists( 'delivery_product', $product ) || empty( $product['delivery_product'] ) ) ) {
			$product['delivery_product'] = null;
		}

		if ( ! $is_main_product && ! array_key_exists( 'delivery_related_products', $product ) ) {
			$product['delivery_related_products'] = array();
		}

		return $product;
	}
}
