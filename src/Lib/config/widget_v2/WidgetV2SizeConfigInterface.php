<?php

namespace Ilabs\Inpost_Pay\Lib\config\widget_v2;

interface WidgetV2SizeConfigInterface {

	public const IZI_WIDGET_V2_SIZE = 'izi_widget_v2_size';

	public const IZI_WIDGET_V2_SIZE_LABEL = 'Widget size';

	public const IZI_WIDGET_V2_SIZE_DEFAULT = [ 'size-sm' ];

	public const IZI_WIDGET_V2_SIZE_OPTIONS = [
		'size-xs' => 'size-xs',
		'size-sm' => 'size-sm',
		'size-md' => 'size-md',
		'size-lg' => 'size-lg',
		'size-xl' => 'size-xl',
	];

	public const IZI_WIDGET_V2_SIZE_DESCRIPTION = 'Defines the size of the widget';
}
