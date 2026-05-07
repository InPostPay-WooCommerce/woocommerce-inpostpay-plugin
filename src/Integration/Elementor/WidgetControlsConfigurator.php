<?php

namespace Ilabs\Inpost_Pay\Integration\Elementor;

use Elementor\Controls_Manager;
use Elementor\Widget_Base;
use Ilabs\Inpost_Pay\Lib\config\widget_v2\WidgetV2SizeConfigInterface;

class WidgetControlsConfigurator {
	public function register_controls( Widget_Base $widget ): void {
		$widget->start_controls_section(
			'section_content',
			array(
				'label' => __( 'Settings', 'inpost-pay' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			)
		);

		$widget->add_control(
			'select_option',
			array(
				'label'   => __( 'Select', 'inpost-pay' ),
				'type'    => Controls_Manager::SELECT,
				'options' => array(
					'PRODUCT_CARD'   => __( 'Product card', 'inpost-pay' ),
					'BASKET_SUMMARY' => __( 'Basket summary', 'inpost-pay' ),
					'ORDER_CREATE'   => __( 'Order create', 'inpost-pay' ),
					'CHECKOUT_PAGE'  => __( 'Checkout page', 'inpost-pay' ),
					'LOGIN_PAGE'     => __( 'Login page', 'inpost-pay' ),
					'BASKET_POPUP'   => __( 'Basket popup', 'inpost-pay' ),
					'THANK_YOU_PAGE' => __( 'Thank you page', 'inpost-pay' ),
					'MINICART_PAGE'  => __( 'Minicart page', 'inpost-pay' ),
				),
				'default' => 'PRODUCT_CARD',
			)
		);

		$widget->add_control(
			'background',
			array(
				'label'   => __( 'Background', 'inpost-pay' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'bright',
				'options' => array(
					'bright' => __( 'Bright', 'inpost-pay' ),
					'dark'   => __( 'Dark', 'inpost-pay' ),
				),
			)
		);

		$widget->add_control(
			'variant',
			array(
				'label'   => __( 'Variant', 'inpost-pay' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'primary',
				'options' => array(
					'primary'   => __( 'Yellow', 'inpost-pay' ),
					'secondary' => __( 'Black', 'inpost-pay' ),
				),
			)
		);

		$widget->add_control(
			'size',
			array(
				'label'   => __( 'Widget size', 'inpost-pay' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'size-sm',
				'options' => WidgetV2SizeConfigInterface::IZI_WIDGET_V2_SIZE_OPTIONS,
			)
		);

		$widget->add_control(
			'frame_style',
			array(
				'label'   => __( 'Round style', 'inpost-pay' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'none',
				'options' => array(
					'none'    => __( 'No round', 'inpost-pay' ),
					'round'   => __( 'Big round', 'inpost-pay' ),
					'rounded' => __( 'Small round', 'inpost-pay' ),
				),
			)
		);

		$widget->end_controls_section();
	}
}
