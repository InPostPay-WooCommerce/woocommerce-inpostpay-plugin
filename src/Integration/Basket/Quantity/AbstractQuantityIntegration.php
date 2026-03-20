<?php

namespace Ilabs\Inpost_Pay\Integration\Basket\Quantity;

use WC_Product;

abstract class AbstractQuantityIntegration implements QuantityIntegrationInterface {

	protected $quantity = 1;

	protected $min_quantity = 1;

	protected $max_quantity = 1;

	protected $step_quantity = 1;

	protected string $quantity_type = 'INTEGER';

	protected string $quantity_unit = 'szt';

	protected ?WC_Product $product = null;

	/**
	 * @param WC_Product $product
	 */
	public function __construct( WC_Product $product ) {
		$this->product = $product;

		$this->quantity = $this->get_product()->get_stock_quantity();
		$this->min_quantity = $this->get_product()->get_min_purchase_quantity();
		$this->max_quantity = $this->get_product()->get_max_purchase_quantity();
		$this->step_quantity = 1;

	}


	public function get_quantity() {
		return $this->quantity;
	}

	public function get_min_quantity() {
		return $this->min_quantity;
	}

	public function get_max_quantity() {
		return $this->max_quantity;
	}

	public function get_step_quantity() {
		return $this->step_quantity;
	}

	public function get_quantity_type(): string {
		return $this->quantity_type;
	}

	public function get_quantity_unit(): string {
		return $this->quantity_unit;
	}

	protected function get_product(): ?WC_Product {
		return $this->product;
	}


}
