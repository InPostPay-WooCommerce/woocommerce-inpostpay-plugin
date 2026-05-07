<?php

namespace Ilabs\Inpost_Pay\Lib;

class IdentificationGenerator {

	public static function generate(): string {
		$id = implode(
			'-',
			array(
				self::random( 8 ),
				self::random( 4 ),
				self::random( 4 ),
				self::random( 4 ),
				self::random( 12 ),
			)
		);

		return $id;
	}

	public static function random( $size ) {
		return bin2hex( random_bytes( $size / 2 ) );
	}
}
