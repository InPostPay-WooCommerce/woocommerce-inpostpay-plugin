<?php

namespace Ilabs\Inpost_Pay\Integration\Elementor;

use Elementor\Widget_Base;
use Ilabs\Inpost_Pay\Integration\Currency\CurrencyHelper;
use Ilabs\Inpost_Pay\Lib\InPostIzi;

class ElementorInpostWidget extends Widget_Base {
	public function get_name() {
		return 'inpost_widget';
	}

	public function get_title() {
		return 'InPost Pay Widget';
	}

	public function get_icon() {
		return 'eicon-code';
	}

	public function get_categories() {
		return [ 'general' ]; //woocommerce
	}

	protected function _register_controls() {
		$configurator = new WidgetControlsConfigurator();
		$configurator->register_controls($this);
	}

	protected function render() {
		if (!CurrencyHelper::isCurrencyAllowed()) {
			return;
		}

		global $post;

		$settings        = $this->get_settings_for_display();
		$selected_option = $settings['select_option'];
		$background      = $settings['background'];
		$variant         = $settings['variant'];
		$size            = $settings['size'];
		$frameStyle      = $settings['frame_style'];

		if ( $selected_option === 'BASKET_SUMMARY' ) {
			if ( ! function_exists( 'WC' ) || ! WC()->cart || WC()->cart->is_empty() ) {
				return;
			}
		}

		$product_id = $selected_option === 'PRODUCT_CARD' ? $post->ID : null;

		$lib = InPostIzi::get_instance();
		$lib::render(
			$product_id,
			true,
			$background === 'dark',
			$variant === 'primary',
			$selected_option,
			$frameStyle,
			$size,
			true
		);
	}
}
