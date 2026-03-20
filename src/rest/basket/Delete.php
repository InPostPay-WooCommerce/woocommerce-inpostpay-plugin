<?php

namespace Ilabs\Inpost_Pay\rest\basket;

use Ilabs\Inpost_Pay\Lib\BasketIdentification;
use Ilabs\Inpost_Pay\Lib\BindingProvider;
use Ilabs\Inpost_Pay\Logger;
use Ilabs\Inpost_Pay\models\CartSessionService;
use Ilabs\Inpost_Pay\rest\Base;
use function Ilabs\Inpost_Pay\inpost_pay_container;

class Delete extends Base
{
	private CartSessionService $cart_session;

    public function __construct()
    {
		/**
		 * Get from container DI.
		 *
		 * @var CartSessionService $cart_session
		 */
		$this->cart_session = inpost_pay_container()->get( CartSessionService::SERVICE_KEY );
        $this->restricted   = true;
    }

    protected function describe()
    {
        $this->delete['/inpost/v1/izi/basket/(?P<id>[a-zA-Z0-9-]+)/binding'] = function ($request) {

            $this->check_signature($request);

            try {
                $id = $request->get_param('id');
                Logger::response('200');
                $this->cart_session->delete_by_cart_id($id);
            } catch (\Exception $e) {
            }
            return json_encode(['success' => true]);
        };
    }
}
