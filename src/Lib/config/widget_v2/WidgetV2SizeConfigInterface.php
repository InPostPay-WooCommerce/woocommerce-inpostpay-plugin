<?php
/**
 * Widget V2 size configuration interface.
 *
 * @package Ilabs\Inpost_Pay\Lib\config\widget_v2
 */

declare( strict_types=1 );

namespace Ilabs\Inpost_Pay\Lib\config\widget_v2;

/**
 * Interface WidgetV2SizeConfigInterface
 *
 * Defines constants for the InPost Pay widget V2 size configuration option.
 */
interface WidgetV2SizeConfigInterface {

	public const IZI_WIDGET_V2_SIZE = 'izi_widget_v2_size';

	public const IZI_WIDGET_V2_SIZE_LABEL = 'Widget size';

	public const IZI_WIDGET_V2_SIZE_DEFAULT = array( 'size-sm' );

	public const IZI_WIDGET_V2_SIZE_OPTIONS = array(
		'size-xs' => 'size-xs',
		'size-sm' => 'size-sm',
		'size-md' => 'size-md',
		'size-lg' => 'size-lg',
		'size-xl' => 'size-xl',
	);

	public const IZI_WIDGET_V2_SIZE_DESCRIPTION = 'Defines the size of the widget';
}
