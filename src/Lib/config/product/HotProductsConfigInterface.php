<?php
/**
 * Hot products configuration interface.
 *
 * @package Ilabs\Inpost_Pay\Lib\config\product
 */

declare( strict_types=1 );

namespace Ilabs\Inpost_Pay\Lib\config\product;

/**
 * Interface HotProductsConfigInterface
 *
 * Defines constants for the hot products configuration option.
 */
interface HotProductsConfigInterface {

	public const IZI_HOT_PRODUCTS = 'izi_hot_products';

	public const IZI_HOT_PRODUCTS_LABEL = 'Hot Products';

	public const IZI_HOT_PRODUCTS_DEFAULT = array();

	public const IZI_HOT_PRODUCTS_DESCRIPTION = 'Collection of hot products displayed in the Inpost APP';
}
