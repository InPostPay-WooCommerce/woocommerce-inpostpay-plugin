<?php

namespace Ilabs\Inpost_Pay\Integration\Basket\Quantity;

class QuantityIntegrationFactory {
	public function create( \WC_Product $product ): QuantityIntegrationInterface {
		if ( is_plugin_active( 'decimal-product-quantity-for-woocommerce/decimal-product-quantity-for-woocommerce.php' ) ) {
			return new DecimalProductQuantityForWoocommerce( $product );
		}

		return new GenericQuantityIntegration( $product );
	}
}
