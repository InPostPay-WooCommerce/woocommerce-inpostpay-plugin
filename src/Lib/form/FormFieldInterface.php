<?php

namespace Ilabs\Inpost_Pay\Lib\form;

interface FormFieldInterface {
	public function print_label(): void;

	public function print_field(): void;
}
