<?php
/**
 * Admin Post Updated Hook.
 *
 * @package InpostPay
 * @subpackage Hooks/Admin
 */

namespace Ilabs\Inpost_Pay\hooks\admin;

use Ilabs\Inpost_Pay\hooks\Base;
use Ilabs\Inpost_Pay\Lib\Api\v1\Products;
use Ilabs\Inpost_Pay\Lib\config\product\HotProductsConfig;
use Ilabs\Inpost_Pay\Lib\form\exception\IsNotArrayException;
use Ilabs\Inpost_Pay\Lib\Utils\HotProductUtils;
use Ilabs\Inpost_Pay\Logger;

/**
 * Class AdminPostUpdated
 *
 * Handles the post_updated hook to detect when the URL of a product changes.
 */
class AdminPostUpdated extends Base {

	/**
	 * Flag to prevent recursive execution.
	 *
	 * @var bool
	 */
	public static bool $is_start = false;
	/**
	 * Attach hooks.
	 *
	 * @return void
	 */
	public function attach_hook(): void {
		add_action(
			'post_updated',
			array(
				$this,
				'detect_product_url_change',
			),
			10,
			3
		);
	}

	/**
	 * Handles the post_updated hook to detect when the URL of a product changes, and
	 * updates the URL of the product in the InPost API.
	 *
	 * @param int   $post_id   The ID of the updated post.
	 * @param mixed $post_after The post object after the update.
	 * @param mixed $post_before The post object before the update.
	 *
	 * @return void
	 */
	public function detect_product_url_change( $post_id, $post_after, $post_before ): void {
		if ( self::$is_start ) {
			return;
		}

		if ( get_post_type( $post_id ) !== 'product' ) {
			return;
		}

		if ( $post_before->post_name === $post_after->post_name ) {
			return;
		}

		$product    = wc_get_product( $post_id );
		$product_id = $product->get_id();

		$hot_products_config = new HotProductsConfig();
		$izi_product_api     = new Products();

		try {
			if ( ! $hot_products_config->checkValueInArray( $product_id ) ) {
				self::$is_start = false;

				return;
			}
		} catch ( IsNotArrayException $e ) {
			Logger::log( 'Error checking hot products array: ' . $e->getMessage() );
			self::$is_start = false;

			return;
		}

		$response = $izi_product_api->put( $product );

		if ( ! $response || isset( $response->error_code ) ) {
			self::$is_start = false;

			return;
		}

		HotProductUtils::handlePutResponse( $response );

		self::$is_start = false;
	}
}
