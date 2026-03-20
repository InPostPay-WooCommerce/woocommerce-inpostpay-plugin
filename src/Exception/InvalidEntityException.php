<?php
/**
 * Exception for invalid entity state or data.
 *
 * @package Ilabs\WpEntityLayer\Exception
 */

declare( strict_types=1 );

namespace Ilabs\Inpost_Pay\Exception;

use Throwable;
use function Ilabs\Inpost_Pay\EntityLayer\Exception\gettype;

/**
 * Thrown when entity validation fails or data is invalid.
 */
class InvalidEntityException extends RepositoryException {

	/**
	 * Constructor.
	 *
	 * @param string         $message  Exception message.
	 * @param int            $code     Exception code.
	 * @param Throwable|null $previous Previous exception instance.
	 */
	public function __construct(
		string $message = 'Entity data is invalid or incomplete.',
		int $code = 400,
		?Throwable $previous = null
	) {
		parent::__construct( $message, $code, $previous );
	}

	/**
	 * Create exception for missing required field.
	 *
	 * @param string         $field    Field name.
	 * @param string         $entity   Entity class name.
	 * @param Throwable|null $previous Previous exception.
	 * @return self
	 */
	public static function missing_required_field(
		string $field,
		string $entity,
		?Throwable $previous = null
	): self {
		return new self(
			sprintf( 'Required field "%s" is missing in entity "%s".', $field, $entity ),
			400,
			$previous
		);
	}

	/**
	 * Create exception for invalid field type.
	 *
	 * @param string         $field    Field name.
	 * @param string         $expected Expected type.
	 * @param mixed          $actual   Actual value.
	 * @param Throwable|null $previous Previous exception.
	 * @return self
	 */
	public static function invalid_field_type(
		string $field,
		string $expected,
		$actual,
		?Throwable $previous = null
	): self {
		$actual_type = is_object( $actual ) ? get_class( $actual ) : gettype( $actual );

		return new self(
			sprintf(
				'Field "%s" expects type "%s", got "%s".',
				$field,
				$expected,
				$actual_type
			),
			400,
			$previous
		);
	}

	/**
	 * Create exception for invalid table name.
	 *
	 * @param string         $entity   Entity class name.
	 * @param Throwable|null $previous Previous exception.
	 * @return self
	 */
	public static function missing_table_name(
		string $entity,
		?Throwable $previous = null
	): self {
		return new self(
			sprintf( 'Entity "%s" does not define a table name.', $entity ),
			500,
			$previous
		);
	}
}
