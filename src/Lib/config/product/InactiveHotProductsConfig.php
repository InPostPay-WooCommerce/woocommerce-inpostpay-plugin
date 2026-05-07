<?php

namespace Ilabs\Inpost_Pay\Lib\config\product;

use Ilabs\Inpost_Pay\Lib\form\AbstractArrayOption;
use Ilabs\Inpost_Pay\Lib\form\exception\NotAllowedConfigOptionException;
use Ilabs\Inpost_Pay\Lib\form\exception\RequiredConfigOptionException;
use Ilabs\Inpost_Pay\Lib\form\FormFieldInterface;
use Ilabs\Inpost_Pay\Lib\form\Hidden;
use Ilabs\Inpost_Pay\Logger;

final class InactiveHotProductsConfig extends AbstractArrayOption implements InactiveHotProductsConfigInterface {


	public function __construct() {
		parent::__construct(
			self::IZI_INACTIVE_HOT_PRODUCTS,
			self::IZI_INACTIVE_HOT_PRODUCTS_LABEL,
			self::IZI_INACTIVE_HOT_PRODUCTS_DESCRIPTION
		);
	}

	public function register( array $args = array() ): void {
		parent::register(
			array(
				'type'    => 'string',
				'default' => self::IZI_INACTIVE_HOT_PRODUCTS_DEFAULT,
			)
		);
	}

	public function get( $default = false ): array {
		$hot_products = parent::get( self::IZI_INACTIVE_HOT_PRODUCTS_DEFAULT );

		if ( ! is_array( $hot_products ) ) {
			$hot_products = json_decode( $hot_products, true ) ?? array();
		}

		return $hot_products;
	}

	public function update( $value ): bool {
		if ( is_string( $value ) ) {
			$hot_products = $this->get();
			if ( ! in_array( $value, $hot_products, true ) ) {
				$hot_products[] = $value;
				$value          = $hot_products;
			}
		}

		parent::update( json_encode( $value ) );

		return true;
	}


	/**
	 * @throws RequiredConfigOptionException
	 * @throws NotAllowedConfigOptionException
	 */
	public function get_form_field(): FormFieldInterface {
		return new Hidden(
			json_encode( $this->get() ),
			array(
				'label'       => $this->get_label(),
				'name'        => $this->get_field_name(),
				'label_class' => 'label-gray',
				'class'       => 'mobileToggle',
			)
		);
	}
}
