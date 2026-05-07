<?php

namespace Ilabs\Inpost_Pay\Lib\Product\CustomMeta;

use Ilabs\Inpost_Pay\Lib\config\product\HotProductsConfig;
use Ilabs\Inpost_Pay\Lib\form\Checkbox;
use Ilabs\Inpost_Pay\Lib\form\error\ValidationError;
use Ilabs\Inpost_Pay\Lib\form\exception\IsNotArrayException;
use Ilabs\Inpost_Pay\Lib\form\exception\NotAllowedConfigOptionException;
use Ilabs\Inpost_Pay\Lib\form\exception\RequiredConfigOptionException;
use Ilabs\Inpost_Pay\Lib\form\FormFieldInterface;

class HotProductMeta extends AbstractProductMeta implements ProductMetaInterface {

	public const INPOST_PAY_HOT_PRODUCT = '_izi_hot_product';

	private const CONFIG = array(
		'slug'    => self::INPOST_PAY_HOT_PRODUCT,
		'type'    => 'boolean',
		'single'  => true,
		'group'   => 'inpost_pay',
		'default' => 'yes',
		'label'   => 'Hot Product',
		'help'    => 'Enable this option if you want to show this product in Hot Products list.',
	);

	private static ?ValidationError $validation_error = null;

	public static function get_config(): array {
		return self::CONFIG;
	}

	public static function get_slug(): string {
		return self::CONFIG['slug'];
	}

	public static function get_type(): string {
		return self::CONFIG['type'];
	}

	public static function get_group(): string {
		return self::CONFIG['group'];
	}

	public static function get_label(): string {
		return self::CONFIG['label'];
	}

	public static function get_help(): string {
		return self::CONFIG['help'];
	}

	/**
	 * @throws RequiredConfigOptionException
	 * @throws NotAllowedConfigOptionException
	 */
	public static function get_form_field( $post_ID ): FormFieldInterface {
		return new Checkbox(
			self::get( $post_ID ),
			array(
				'label'       => self::get_label(),
				'name'        => self::get_slug(),
				'label_class' => 'label-gray',
				'class'       => 'mobileToggle',
			)
		);
	}

	public static function get( $post_ID ): bool {
		return parent::get_meta( $post_ID, self::get_slug(), self::CONFIG['single'], self::CONFIG['default'] );
	}

	public static function is_available( $post_ID ): bool {
		if ( self::get( $post_ID ) === self::CONFIG['default'] ) {
			return true;
		}

		return false;
	}

	public static function validate( $post_ID ): bool {
		$hot_product_config = new HotProductsConfig();
		try {
			if ( $hot_product_config->checkValueInArray( $_POST[ self::INPOST_PAY_HOT_PRODUCT ] ) ) {
				return true;
			}

			// if ( $hot_product_config->count() < HotProductsConfig::IZI_HOT_PRODUCTS_LIMIT ) {
			// $hot_product_config->addValue( $_POST[ self::INPOST_PAY_HOT_PRODUCT ] );
			// } else {
			// self::$validation_error = new ValidationError(
			// 'You can add only %s hot products',
			// [ HotProductsConfig::IZI_HOT_PRODUCTS_LIMIT ]
			// );
			//
			// }
		} catch ( IsNotArrayException $e ) {
			return true;
		}

		return true;
	}

	public static function get_validation_error(): ValidationError {
		return self::$validation_error;
	}
}
