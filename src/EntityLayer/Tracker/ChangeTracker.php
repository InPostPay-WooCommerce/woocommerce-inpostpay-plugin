<?php
/**
 * Change tracker for entities.
 *
 * @package Ilabs\WpEntityLayer\Tracker
 */

declare( strict_types=1 );

namespace Ilabs\Inpost_Pay\EntityLayer\Tracker;

use Ilabs\Inpost_Pay\EntityLayer\Entity\BaseEntity;
use Ilabs\Inpost_Pay\EntityLayer\Helpers\ArrayUtils;
use Ilabs\Inpost_Pay\Logger;

/**
 * Tracks changes to entity instances.
 *
 * Stores original state of entities and computes diffs.
 */
class ChangeTracker {

	public const SERVICE_KEY = 'entity_layer.tracker.change_tracker';

	/**
	 * Storage for original entity states.
	 *
	 * @var array<string, array>
	 */
	private array $original_states = array();

	/**
	 * Track an entity's current state as original.
	 *
	 * @param BaseEntity $entity Entity to track.
	 * @return void
	 */
	public function track( BaseEntity $entity ): void {
		$hash                           = $this->get_entity_hash( $entity );
		$this->original_states[ $hash ] = $entity->to_array();
	}

	/**
	 * Check if entity has been tracked.
	 *
	 * @param BaseEntity $entity Entity to check.
	 * @return bool
	 */
	public function is_tracked( BaseEntity $entity ): bool {
		$hash = $this->get_entity_hash( $entity );
		return isset( $this->original_states[ $hash ] );
	}

	/**
	 * Get changes for a tracked entity.
	 *
	 * @param BaseEntity $entity Entity to check.
	 * @return array Diff array: ['field' => ['old' => x, 'new' => y]].
	 */
	public function get_changes( BaseEntity $entity ): array {
		$hash = $this->get_entity_hash( $entity );

		if ( ! isset( $this->original_states[ $hash ] ) ) {
			return array();
		}

		$original = $this->original_states[ $hash ];
		$current  = $entity->to_array();

		return ArrayUtils::diff( $original, $current );
	}

	/**
	 * Check if entity has any changes.
	 *
	 * @param BaseEntity $entity Entity to check.
	 * @return bool
	 */
	public function has_changes( BaseEntity $entity ): bool {
		return ! empty( $this->get_changes( $entity ) );
	}

	/**
	 * Get only changed values (for UPDATE queries).
	 *
	 * @param BaseEntity $entity Entity to check.
	 * @return array Array with only new values: ['field' => new_value].
	 */
	public function get_changed_values( BaseEntity $entity ): array {
		$changes = $this->get_changes( $entity );
		return ArrayUtils::extract_new_values( $changes );
	}

	/**
	 * Stop tracking an entity.
	 *
	 * @param BaseEntity $entity Entity to untrack.
	 * @return void
	 */
	public function untrack( BaseEntity $entity ): void {
		$hash = $this->get_entity_hash( $entity );
		unset( $this->original_states[ $hash ] );
	}

	/**
	 * Clear all tracked entities.
	 *
	 * @return void
	 */
	public function clear(): void {
		$this->original_states = array();
	}

	/**
	 * Get unique hash for entity instance.
	 *
	 * @param BaseEntity $entity Entity instance.
	 * @return string
	 */
	private function get_entity_hash( BaseEntity $entity ): string {
		return spl_object_hash( $entity );
	}

	/**
	 * Get original state of a tracked entity.
	 *
	 * @param BaseEntity $entity Entity to check.
	 * @return array|null Original state or null if not tracked.
	 */
	public function get_original_state( BaseEntity $entity ): ?array {
		$hash = $this->get_entity_hash( $entity );
		return $this->original_states[ $hash ] ?? null;
	}

	/**
	 * Re-track entity with its current state.
	 *
	 * Useful after saving to reset "original" to current values.
	 *
	 * @param BaseEntity $entity Entity to re-track.
	 * @return void
	 */
	public function refresh( BaseEntity $entity ): void {
		$this->track( $entity );
	}
}
