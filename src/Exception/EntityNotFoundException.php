<?php
/**
 * Exception for entity not found in database.
 *
 * @package Ilabs\WpEntityLayer\Exception
 */

declare( strict_types=1 );

namespace Ilabs\Inpost_Pay\Exception;

use Throwable;

/**
 * Thrown when an entity cannot be found by given criteria.
 */
class EntityNotFoundException extends RepositoryException {

	/**
	 * Constructor.
	 *
	 * @param string         $entity_class Entity class name.
	 * @param mixed          $identifier   ID or criteria used for lookup.
	 * @param int            $code         Exception code.
	 * @param Throwable|null $previous     Previous exception instance.
	 */
	public function __construct(
		string $entity_class,
		$identifier,
		int $code = 404,
		?Throwable $previous = null
	) {
		$identifier_string = is_array( $identifier )
			? json_encode( $identifier )
			: (string) $identifier;

		$message = sprintf(
			'Entity "%s" not found with identifier: %s',
			$entity_class,
			$identifier_string
		);

		parent::__construct( $message, $code, $previous );
	}
}
