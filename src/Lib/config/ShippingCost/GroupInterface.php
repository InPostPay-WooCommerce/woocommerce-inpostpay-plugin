<?php

namespace Ilabs\Inpost_Pay\Lib\config\ShippingCost;

use Ilabs\Inpost_Pay\Lib\form\AbstractOption;

interface GroupInterface {

	const DELIVERY_OPTION_CODE_PWW = 'PWW';

	const DELIVERY_OPTION_CODE_COD = 'COD';

	const DELIVERY_OPTION_CODE_PWW_COD = 'PWW_COD';

	const DELIVERY_OPTION_CODE_NONE = 'NONE';




	const DELIVERY_TYPE_CODE_APM = 'APM';

	const DELIVERY_TYPE_CODE_COURIER = 'COURIER';


	public function registerGroup(): void;

	public function initIsActiveField(): void;

	public function initOptionCostMappingApproach(): void;

	/**
	 * @return ShippingMappingFieldInterface[]
	 */
	public function getFields(): array;

	public function getDeliveryOptionCode(): string;

	public function getDeliveryTypeCode(): string;

	public function getDeliveryOptionName(): ?string;

	public function getApiDeliveryOptionsMap(): ?array;

	public function getAvailableFromDayField(): ?AbstractOption;

	public function getAvailableFromHourField(): ?AbstractOption;

	public function getAvailableToDayField(): ?AbstractOption;

	public function getAvailableToHourField(): ?AbstractOption;

	public function getPriceField(): ?AbstractPriceField;

	public function getShippingMethodField(): ?AbstractShippingMethodField;

	public function getIsActiveField(): BoolField;

	public function getOptionCostMappingApproach(): string;

	public function getGroupId(): string;

	/**
	 * @return GroupInterface[]|null
	 */
	public function getOptionSubGroups( ?int $zone_id = null ): ?array;
}
