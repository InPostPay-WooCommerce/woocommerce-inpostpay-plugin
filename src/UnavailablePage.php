<?php

namespace Ilabs\Inpost_Pay;

class UnavailablePage {
	public function register(): void {
	}

	public function displayUnavailableProductsPage(): void {
		require_once inpost_pay()->get_plugin_dir() . 'src/views/unavailable/unavailable-products-page.php';
	}

	public function displayUnavailableCategoriesPage(): void {
		require_once inpost_pay()->get_plugin_dir() . 'src/views/unavailable/unavailable-categories-page.php';
	}
}
