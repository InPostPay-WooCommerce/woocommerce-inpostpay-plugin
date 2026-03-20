<?php

namespace Ilabs\Inpost_Pay\Lib\form;

use Ilabs\Inpost_Pay\Lib\form\exception\IsNotArrayException;

abstract class AbstractArrayOption extends AbstractOption {

	/**
	 * @throws IsNotArrayException
	 */
	public function checkValueInArray( $value ): bool {
		$arr = $this->get();

		if ( ! is_array( $arr ) ) {
			throw new IsNotArrayException( $this->get_field_name(), $arr );
		}

		return in_array( $value, $arr, false );
	}


	public function count(): int {
		$arr = $this->get();

		return is_array( $arr ) ? count( $arr ) : 0;
	}

	public function addValue( $value ): void {
		$arr = $this->get();
		if ( is_array( $arr ) ) {
			$arr[] = $value;
		} else {
			$arr = [ $value ];
		}
		update_option( $this->get_field_name(), $arr );
	}

}
