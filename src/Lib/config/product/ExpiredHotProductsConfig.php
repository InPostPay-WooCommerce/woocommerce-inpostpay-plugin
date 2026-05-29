<?php
/**
 * Expired hot products configuration.
 *
 * @package Ilabs\Inpost_Pay\Lib\config\product
 */

declare( strict_types=1 );

namespace Ilabs\Inpost_Pay\Lib\config\product;

use Ilabs\Inpost_Pay\Lib\form\AbstractArrayOption;
use Ilabs\Inpost_Pay\Lib\form\exception\NotAllowedConfigOptionException;
use Ilabs\Inpost_Pay\Lib\form\exception\RequiredConfigOptionException;
use Ilabs\Inpost_Pay\Lib\form\FormFieldInterface;
use Ilabs\Inpost_Pay\Lib\form\Hidden;
use Ilabs\Inpost_Pay\Logger;

/**
 * Class ExpiredHotProductsConfig
 *
 * WordPress option storing the list of expired hot product IDs displayed in the InPost app.
 */
final class ExpiredHotProductsConfig extends AbstractArrayOption implements ExpiredHotProductsConfigInterface {

	/**
	 * Constructor.
	 */
	public function __construct() {
		parent::__construct(
			self::IZI_EXPIRED_HOT_PRODUCTS,
			self::IZI_EXPIRED_HOT_PRODUCTS_LABEL,
			self::IZI_EXPIRED_HOT_PRODUCTS_DESCRIPTION
		);
	}

	/**
	 * Registers the option with WordPress settings API.
	 *
	 * @param array $args Optional registration arguments.
	 *
	 * @return void
	 */
	public function register( array $args = array() ): void {
		parent::register(
			array(
				'type'    => 'string',
				'default' => self::IZI_EXPIRED_HOT_PRODUCTS_DEFAULT,
			)
		);
	}

	/**
	 * Returns the current list of expired hot product IDs.
	 *
	 * @param mixed $default_value Default value when the option is absent.
	 *
	 * @return array
	 */
	public function get( $default_value = false ): array {
		$hot_products = parent::get( self::IZI_EXPIRED_HOT_PRODUCTS_DEFAULT );

		if ( ! is_array( $hot_products ) ) {
			$hot_products = json_decode( $hot_products, true ) ?? array();
		}

		return $hot_products;
	}

	/**
	 * Adds a product ID to the list or replaces it when an array is given.
	 *
	 * @param mixed $value Product ID string or full replacement array.
	 *
	 * @return bool
	 */
	public function update( $value ): bool {
		if ( is_string( $value ) ) {
			$hot_products = $this->get();
			if ( ! in_array( $value, $hot_products, true ) ) {
				$hot_products[] = $value;
				$value          = $hot_products;
			}
		}

		parent::update( wp_json_encode( $value ) );

		return true;
	}

	/**
	 * Returns the hidden form field for this option.
	 *
	 * @throws RequiredConfigOptionException   When required option data is missing.
	 * @throws NotAllowedConfigOptionException When the option value is not allowed.
	 *
	 * @return FormFieldInterface
	 */
	public function get_form_field(): FormFieldInterface {
		return new Hidden(
			wp_json_encode( $this->get() ),
			array(
				'label'       => $this->get_label(),
				'name'        => $this->get_field_name(),
				'label_class' => 'label-gray',
				'class'       => 'mobileToggle',
			)
		);
	}
}
