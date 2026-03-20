<?php

namespace Ilabs\Inpost_Pay\Lib\VirtualPage;

class TemplateLoaderInpostPay extends TemplateLoader {
	public function inpost_pay_locate_template( $template_names, $load = false, $require_once = true ) {
		$located = '';

		foreach ( (array) $template_names as $template_name ) {
			if ( ! $template_name ) {
				continue;
			}
			if ( file_exists( __DIR__ . '/templates/' . $template_name ) ) {
				$located = __DIR__ . '/templates/' . $template_name;
				break;
			}
		}

		if ( $load && '' !== $located ) {
			load_template( $located, $require_once );
		}

		return $located;
	}

	public function load(): void {

		do_action( 'template_redirect' );
		$template = $this->inpost_pay_locate_template(array_filter($this->templates), TRUE);

		$filtered = apply_filters( 'template_include',
			apply_filters( 'virtual_page_template', $template )
		);
		if ( empty( $filtered ) || file_exists( $filtered ) ) {
			$template = $filtered;
		}
		if ( ! empty( $template ) &&file_exists( $template ) ) {
			require_once $template;
		}
	}
}
