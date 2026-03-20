<?php
/**
 * Admin EAN Validator Hook.
 *
 * @package InpostPay
 * @subpackage Hooks/Admin
 */

declare(strict_types=1);

namespace Ilabs\Inpost_Pay\hooks\admin;

use Ilabs\Inpost_Pay\hooks\Base;
use Ilabs\Inpost_Pay\Lib\Api\v1\Products;
use Ilabs\Inpost_Pay\Lib\config\product\HotProductsConfig;
use Ilabs\Inpost_Pay\Lib\helpers\EANHelper;
use Ilabs\Inpost_Pay\Lib\Product\CustomMeta\HotProductPublishedMeta;

/**
 * Class AdminEANValidatorHook
 *
 * Validates EAN after product save.
 */
class AdminEANValidatorHook extends Base {

	/**
	 * Attach hooks.
	 *
	 * @return void
	 */
	public function attach_hook(): void {
		add_action(
			'woocommerce_process_product_meta',
			array( $this, 'validate_ean_after_product_save' ),
			20,
			2
		);
	}

	/**
	 * Validate EAN after product save.
	 *
	 * @param int      $post_id The post ID.
	 * @param \WP_Post $post    The post object.
	 *
	 * @return void
	 */
	public function validate_ean_after_product_save( int $post_id, \WP_Post $post ): void {
		$product = wc_get_product( $post_id );
		if ( ! $product || $product->get_type() !== 'simple' ) {
			return;
		}

		$ean = $product->get_meta( '_global_unique_id' );

		if ( EANHelper::isValid( $ean ) ) {
			return;
		}

		$hot_products_config = new HotProductsConfig();
		$hot_products_list   = $hot_products_config->get();

		$hot_products_list_int = array_map( 'intval', $hot_products_list );

		if ( ! in_array( $product->get_id(), $hot_products_list_int, true ) ) {
			return;
		}

		$hot_products_list = array_values(
			array_filter(
				$hot_products_list,
				static function ( $id ) use ( $post_id ) {
					return (int) $id !== $post_id;
				}
			)
		);

		$products_api = new Products();
		if ( ! $products_api->delete( $post_id ) ) {
			return;
		}

		delete_post_meta( $post_id, HotProductPublishedMeta::INPOST_PAY_HOT_PRODUCT_PUBLISHED );
		delete_post_meta( $post_id, 'hot_product_start_date' );
		delete_post_meta( $post_id, 'hot_product_end_date' );

		$hot_products_config->update( $hot_products_list );

		set_transient(
			'inpost_pay_hot_product_removed_' . get_current_user_id(),
			array(
				'product_id' => $post_id,
				'reason'     => __( 'Product has been removed from Hot Products due to missing or invalid EAN.', 'inpost-pay' ),
			),
			60
		);
	}
}
