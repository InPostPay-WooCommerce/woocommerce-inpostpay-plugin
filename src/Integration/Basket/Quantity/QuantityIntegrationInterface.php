<?php

namespace Ilabs\Inpost_Pay\Integration\Basket\Quantity;

interface QuantityIntegrationInterface {

	public function get_quantity();

	public function get_min_quantity();

	public function get_max_quantity();

	public function get_step_quantity();

	public function get_quantity_type(): string;

	public function get_quantity_unit(): string;
}
