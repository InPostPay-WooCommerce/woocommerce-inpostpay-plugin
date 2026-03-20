<?php

namespace Ilabs\Inpost_Pay;

class HotProductsPage {
	public function register(): void {
	}

	public function displayHotProductsPage(): void {
		require_once inpost_pay()->get_plugin_dir() . 'src/views/hot-products/hot-products-page.php';
	}
}
