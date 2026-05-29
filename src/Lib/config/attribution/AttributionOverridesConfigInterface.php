<?php
/**
 * Attribution overrides configuration interface.
 *
 * @package Ilabs\Inpost_Pay\Lib\config\attribution
 */

declare( strict_types=1 );

namespace Ilabs\Inpost_Pay\Lib\config\attribution;

/**
 * Interface AttributionOverridesConfigInterface
 *
 * Defines constants for the order attribution overrides configuration option.
 */
interface AttributionOverridesConfigInterface {

	public const IZI_ATTRIBUTION_OVERRIDES = 'izi_attribution_overrides';

	public const IZI_ATTRIBUTION_OVERRIDES_LABEL = 'Order Attribution Overrides by InPost';

	public const IZI_ATTRIBUTION_OVERRIDES_DEFAULT = 'no';

	public const IZI_ATTRIBUTION_OVERRIDES_DESCRIPTION = 'Overwrites the original attribution to the InpostPay attribution';
}
