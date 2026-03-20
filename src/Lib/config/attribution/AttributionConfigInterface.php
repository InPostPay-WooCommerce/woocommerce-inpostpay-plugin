<?php

namespace Ilabs\Inpost_Pay\Lib\config\attribution;

interface AttributionConfigInterface {

	public const IZI_ATTRIBUTION = 'izi_attribution';

	public const IZI_ATTRIBUTION_LABEL = 'Order Attribution';

	public const IZI_ATTRIBUTION_DEFAULT = 'no';

	public const IZI_ATTRIBUTION_DESCRIPTION = 'Enables collection and saving of attribution data during purchases made via InpostPay. For more information on attribution, see the woocommerce documentation: https://woocommerce.com/document/order-attribution-tracking/';
}
