<?php

namespace Ilabs\Inpost_Pay\Integration\Basket;

use Ilabs\Inpost_Pay\Lib\item\Quantity;
use WC_Product;

class ProductThirdPartyFilter {

	public function quantityModificationLockIsRequired(
		WC_Product $product
	): bool {
		foreach ( $this->getProductFilterInterfaces() as $productFilterInterface ) {
			return $productFilterInterface->quantityModificationLockIsRequired( $product );
		}

		return false;
	}

	/**
	 * @return ProductFilterInterface[]
	 */
	private function getProductFilterInterfaces(): array {
		return [
			new ExtendonsCompositeProductFilter(),
			new SmartCompositeProductFilter(),
		];
	}
}
