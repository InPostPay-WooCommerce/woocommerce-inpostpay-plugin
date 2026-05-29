<?php
/**
 * Analytics configuration interface.
 *
 * @package Ilabs\Inpost_Pay\Lib\config\analytics
 */

declare( strict_types=1 );

namespace Ilabs\Inpost_Pay\Lib\config\analytics;

/**
 * Interface AnalyticsConfigInterface
 *
 * Defines constants for the analytics configuration option.
 */
interface AnalyticsConfigInterface {

	public const IZI_ANALYTICS = 'izi_analytics';

	public const IZI_ANALYTICS_LABEL = 'Analytics';

	public const IZI_ANALYTICS_DEFAULT = 'no';

	public const IZI_ANALYTICS_DESCRIPTION = 'Enable the collection of analytics identifiers. For more information about this integration, refer to the documentation: ';
}
