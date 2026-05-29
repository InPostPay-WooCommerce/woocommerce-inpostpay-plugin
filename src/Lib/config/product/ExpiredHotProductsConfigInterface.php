<?php
/**
 * Expired hot products configuration interface.
 *
 * @package Ilabs\Inpost_Pay\Lib\config\product
 */

declare( strict_types=1 );

namespace Ilabs\Inpost_Pay\Lib\config\product;

/**
 * Interface ExpiredHotProductsConfigInterface
 *
 * Defines constants for the expired hot products configuration option.
 */
interface ExpiredHotProductsConfigInterface {

	public const IZI_EXPIRED_HOT_PRODUCTS = 'izi_expired_hot_products';

	public const IZI_EXPIRED_HOT_PRODUCTS_LABEL = 'Expired Hot Products';

	public const IZI_EXPIRED_HOT_PRODUCTS_DEFAULT = array();

	public const IZI_EXPIRED_HOT_PRODUCTS_DESCRIPTION = 'Collection of expired hot products displayed in the Inpost APP';
}
