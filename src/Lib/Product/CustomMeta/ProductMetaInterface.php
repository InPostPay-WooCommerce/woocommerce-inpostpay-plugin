<?php
namespace Ilabs\Inpost_Pay\Lib\Product\CustomMeta;

use Ilabs\Inpost_Pay\Lib\form\error\ValidationError;

interface ProductMetaInterface {
	public static function get_config();


	public static function get_slug(): string;

	public static function get_type(): string;

	public static function get_group(): string;

	public static function get_label(): string;

	public static function get_help(): string;

	public static function get( $post_ID );

	public static function validate( $post_ID ): bool;

	public static function get_validation_error(): ?ValidationError;
}
