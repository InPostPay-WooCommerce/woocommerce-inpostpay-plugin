<?php

namespace Ilabs\Inpost_Pay\rest;

class RestRequest {

	private static bool $is_requested = false;

	public static function isRequested(): bool {
		return self::$is_requested;
	}

	public static function setRequested(): void {
		self::$is_requested = true;
	}
}
