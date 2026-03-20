<?php
namespace Ilabs\Inpost_Pay\rest\product;

use Ilabs\Inpost_Pay\Lib\Product\HotProduct;
use Ilabs\Inpost_Pay\rest\Base;
use WP_REST_Request;

class Get extends Base
{
    public function __construct()
    {
        $this->restricted = true;
    }

    protected function describe()
    {
        $this->get['/inpost/v1/izi/products'] = function (WP_REST_Request $request) {

            $this->check_signature($request);

            $page_index = $request->get_param('page_index') ?? 1;
			$page_size = $request->get_param('page_size') ?? 10;
			$product_ids = $request->get_param('product_ids') ?? [];

			$hot_products = ( new HotProduct() )->getList( $page_index, $page_size, $product_ids );

			$response = $hot_products->toArray();

            header('content-type: application/json');

	        // plugin-version-header.
	        $current_plugin_version = inpost_pay()->get_plugin_version();
	        header('inpay-plugin-version: ' . $current_plugin_version );

            return rest_ensure_response($response);
        };
    }
}
