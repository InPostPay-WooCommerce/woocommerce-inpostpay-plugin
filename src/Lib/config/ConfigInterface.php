<?php

namespace Ilabs\Inpost_Pay\Lib\config;

use Ilabs\Inpost_Pay\Lib\form\FormFieldInterface;

interface ConfigInterface {

	public const OPTION_GROUP = 'inpost-izi';

	public const TRANSLATION_DOMAIN = 'inpost-pay';

	public function register();

	public function get();

	public function update( $value ): bool;

	public function get_label(): string;

	public function get_field_name(): string;

	public function get_form_field(): FormFieldInterface;

	public function get_description(): ?string;

	public function get_tooltip(): ?string;
}
