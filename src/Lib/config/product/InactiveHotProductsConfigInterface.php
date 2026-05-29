<?php
/**
 * Inactive hot products configuration interface.
 *
 * @package Ilabs\Inpost_Pay\Lib\config\product
 */

declare( strict_types=1 );

namespace Ilabs\Inpost_Pay\Lib\config\product;

/**
 * Interface InactiveHotProductsConfigInterface
 *
 * Defines constants for the inactive hot products configuration option.
 */
interface InactiveHotProductsConfigInterface {

	public const IZI_INACTIVE_HOT_PRODUCTS = 'izi_inactive_hot_products';

	public const IZI_INACTIVE_HOT_PRODUCTS_LABEL = 'Inactive Hot Products';

	public const IZI_INACTIVE_HOT_PRODUCTS_DEFAULT = array();

	public const IZI_INACTIVE_HOT_PRODUCTS_DESCRIPTION = 'Collection of inactive hot products displayed in the Inpost APP';
}
