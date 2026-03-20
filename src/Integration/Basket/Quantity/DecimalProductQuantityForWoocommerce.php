<?php

namespace Ilabs\Inpost_Pay\Integration\Basket\Quantity;

final class DecimalProductQuantityForWoocommerce extends AbstractQuantityIntegration {


	public function __construct( \WC_Product $product ) {
		parent::__construct( $product );

		$decimal_product_quantity_for_woocommerce = WooDecimalProduct_Get_QuantityData_by_ProductID( $product->get_id() );

		$this->quantity_type = ( $this->is_type_decimal( $decimal_product_quantity_for_woocommerce['stp_qnt'] ) ) ? 'DECIMAL' : 'INTEGER';

		$this->step_quantity = ( $this->is_type_decimal( $decimal_product_quantity_for_woocommerce['stp_qnt'] ) ) ? $decimal_product_quantity_for_woocommerce['stp_qnt'] : intval( $decimal_product_quantity_for_woocommerce['stp_qnt'] );

		$this->min_quantity = ( $this->is_type_decimal( $decimal_product_quantity_for_woocommerce['min_qnt'] ) ) ? $decimal_product_quantity_for_woocommerce['min_qnt'] : intval( $decimal_product_quantity_for_woocommerce['min_qnt'] );

		$this->max_quantity = ( $this->is_type_decimal( $decimal_product_quantity_for_woocommerce['max_qnt'] ) ) ? $decimal_product_quantity_for_woocommerce['max_qnt'] : intval( $decimal_product_quantity_for_woocommerce['max_qnt'] );

		$this->quantity_unit = $this->fetch_quantity_unit();
	}

	private function is_type_decimal( $number ): bool {
		return ! ( intval( $number ) == $number );
	}

	private function fetch_quantity_unit(): string {
		if ( ! get_post_meta( $this->get_product()->get_id(), 'woodecimalproduct_pice_unit_disable', true ) ) {
			$price_unit_label = get_post_meta( $this->get_product()->get_id(), 'woodecimalproduct_pice_unit_label', true );
			if ( $price_unit_label ) {
				return $price_unit_label;
			} else {
				$term_quantity_data = WooDecimalProduct_Get_Term_QuantityData_by_ProductID( $this->get_product()->get_id() );

				if ( $term_quantity_data
				) {
					$price_unit_label = $term_quantity_data['price_unit'];

					if ( $price_unit_label ) {
						return $price_unit_label;
					}
				}
			}
		}

		return $this->get_quantity_unit();
	}
}
