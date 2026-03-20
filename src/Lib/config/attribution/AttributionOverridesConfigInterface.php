<?php

namespace Ilabs\Inpost_Pay\Lib\config\attribution;

interface AttributionOverridesConfigInterface {

	public const IZI_ATTRIBUTION_OVERRIDES = 'izi_attribution_overrides';

	public const IZI_ATTRIBUTION_OVERRIDES_LABEL = 'Order Attribution Overrides by InPost';

	public const IZI_ATTRIBUTION_OVERRIDES_DEFAULT = 'no';

	public const IZI_ATTRIBUTION_OVERRIDES_DESCRIPTION = 'Overwrites the original attribution to the InpostPay attribution';
}
