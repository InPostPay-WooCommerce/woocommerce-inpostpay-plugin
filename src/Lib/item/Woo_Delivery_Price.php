<?php
/**
 * InpostPay Woocommerce delivery pricing class.
 *
 * @package Ilabs\Inpost_Pay\Lib\item
 */

declare( strict_types=1 );

namespace Ilabs\Inpost_Pay\Lib\item;

/**
 * Class for managing WooCommerce delivery pricing including base totals and options totals with VAT.
 * This class handles the calculation and storage of delivery prices, separating base costs
 * from additional options costs, both with their respective VAT amounts.
 */
class Woo_Delivery_Price {
	protected float $base_total;
	protected float $base_total_vat;
	protected float $options_total;
	protected float $options_total_vat;

	/**
	 * Retrieves the base total.
	 *
	 * @return float The base total.
	 */
	public function get_base_total(): float {
		return $this->base_total;
	}

	/**
	 * Sets the base total.
	 *
	 * @param float $base_total The base total to set.
	 */
	public function set_base_total( float $base_total ): void {
		$this->base_total = $base_total;
	}

	/**
	 * Retrieves the base total VAT.
	 *
	 * @return float The base total VAT.
	 */
	public function get_base_total_vat(): float {
		return $this->base_total_vat;
	}

	/**
	 * Sets the base total VAT.
	 *
	 * @param float $base_total_vat The base total VAT to set.
	 */
	public function set_base_total_vat( float $base_total_vat ): void {
		$this->base_total_vat = $base_total_vat;
	}

	/**
	 * Retrieves the total options cost.
	 *
	 * @return float The total options cost.
	 */
	public function get_options_total(): float {
		return $this->options_total;
	}

	/**
	 * Sets the total options cost.
	 *
	 * @param float $options_total The total options cost to set.
	 */
	public function set_options_total( float $options_total ): void {
		$this->options_total = $options_total;
	}

	/**
	 * Retrieves the total options VAT.
	 *
	 * @return float The total options VAT.
	 */
	public function get_options_total_vat(): float {
		return $this->options_total_vat;
	}

	/**
	 * Sets the total options VAT.
	 *
	 * @param float $options_total_vat The total options VAT to set.
	 */
	public function set_options_total_vat( float $options_total_vat ): void {
		$this->options_total_vat = $options_total_vat;
	}
}
