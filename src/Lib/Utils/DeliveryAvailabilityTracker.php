<?php

declare( strict_types=1 );

namespace Ilabs\Inpost_Pay\Lib\Utils;

use Ilabs\Inpost_Pay\Logger;

/**
 * Class DeliveryAvailabilityTracker
 *
 * Collects and tracks reasons for delivery availability decisions.
 * Prevents log spam by aggregating all decisions and logging once at the end.
 */
class DeliveryAvailabilityTracker {

	/**
	 * Product being checked
	 *
	 * @var \WC_Product
	 */
	private \WC_Product $product;

	/**
	 * Collected decisions and reasons
	 *
	 * @var array
	 */
	private array $decisions = array();

	/**
	 * Whether this is a related product check
	 *
	 * @var bool
	 */
	private bool $is_related_product;

	/**
	 * Final availability result
	 *
	 * @var array|null
	 */
	private ?array $final_result = null;

	/**
	 * Constructor
	 *
	 * @param \WC_Product $product Product to track
	 * @param bool        $is_related_product Whether this is a related product
	 */
	public function __construct( \WC_Product $product, bool $is_related_product = false ) {
		$this->product            = $product;
		$this->is_related_product = $is_related_product;
	}

	/**
	 * Adds a decision point with reason
	 *
	 * @param string $checkpoint Decision checkpoint name
	 * @param string $delivery_type Delivery type (APM/COURIER)
	 * @param bool   $is_available Whether delivery is available
	 * @param string $reason Reason for the decision
	 * @param array  $context Additional context data
	 *
	 * @return void
	 */
	public function add_decision(
		string $checkpoint,
		string $delivery_type,
		bool $is_available,
		string $reason,
		array $context = array()
	): void {
		$this->decisions[] = array(
			'checkpoint'    => $checkpoint,
			'delivery_type' => $delivery_type,
			'is_available'  => $is_available,
			'reason'        => $reason,
			'context'       => $context,
			'timestamp'     => microtime( true ),
		);
	}

	/**
	 * Adds a global decision (affects all delivery types)
	 *
	 * @param string $checkpoint Decision checkpoint name
	 * @param string $reason Reason for the decision
	 * @param array  $context Additional context data
	 *
	 * @return void
	 */
	public function add_global_decision( string $checkpoint, string $reason, array $context = array() ): void {
		$this->decisions[] = array(
			'checkpoint'    => $checkpoint,
			'delivery_type' => 'ALL',
			'is_available'  => null,
			'reason'        => $reason,
			'context'       => $context,
			'timestamp'     => microtime( true ),
		);
	}

	/**
	 * Sets the final result
	 *
	 * @param array|null $result Final delivery options result
	 *
	 * @return void
	 */
	public function set_final_result( ?array $result ): void {
		$this->final_result = $result;
	}

	/**
	 * Generates a summary of all decisions
	 *
	 * @return array Summary data
	 */
	private function generate_summary(): array {
		$summary = array(
			'product_id'           => $this->product->get_id(),
			'product_name'         => $this->product->get_name(),
			'is_related_product'   => $this->is_related_product,
			'total_checkpoints'    => count( $this->decisions ),
			'final_result'         => $this->final_result,
			'availability_summary' => array(),
			'decisions_timeline'   => array(),
		);

		// Group decisions by delivery type
		$by_type = array(
			'APM'     => array(
				'available' => true,
				'reasons'   => array(),
			),
			'COURIER' => array(
				'available' => true,
				'reasons'   => array(),
			),
		);

		foreach ( $this->decisions as $decision ) {
			$type = $decision['delivery_type'];

			// Add to timeline
			$summary['decisions_timeline'][] = sprintf(
				'[%s] %s: %s',
				$type,
				$decision['checkpoint'],
				$decision['reason']
			);

			// Track availability changes
			if ( 'ALL' === $type ) {
				continue;
			}

			if ( false === $decision['is_available'] ) {
				$by_type[ $type ]['available'] = false;
				$by_type[ $type ]['reasons'][] = sprintf(
					'%s: %s',
					$decision['checkpoint'],
					$decision['reason']
				);
			}
		}

		$summary['availability_summary'] = $by_type;

		return $summary;
	}

	/**
	 * Logs the complete tracking summary
	 *
	 * @return void
	 */
	public function log_summary(): void {
		if ( empty( $this->decisions ) ) {
			return;
		}

		$summary = $this->generate_summary();

		Logger::log(
			sprintf(
				'[DELIVERY_TRACKER] Product: %s (ID: %d)',
				$summary['product_name'],
				$summary['product_id']
			)
		);

		// Log availability summary
		foreach ( $summary['availability_summary'] as $type => $data ) {
			if ( $data['available'] ) {
				Logger::log( sprintf( '[DELIVERY_TRACKER] %s: [OK] AVAILABLE', $type ) );
			} else {
				Logger::log(
					sprintf(
						'[DELIVERY_TRACKER] %s: [NOK] UNAVAILABLE - Reasons:',
						$type
					)
				);
				foreach ( $data['reasons'] as $reason ) {
					Logger::log( sprintf( '[DELIVERY_TRACKER]   • %s', $reason ) );
				}
			}
		}

		// Log full timeline for complex cases
		if ( count( $summary['decisions_timeline'] ) > 1 ) {
			Logger::log( '[DELIVERY_TRACKER] Full decision timeline:' );
			foreach ( $summary['decisions_timeline'] as $timeline_entry ) {
				Logger::log( sprintf( '[DELIVERY_TRACKER]   %s', $timeline_entry ) );
			}
		}

		Logger::log( '[DELIVERY_TRACKER] Final result: ' . var_export( $this->final_result, true ) );
		Logger::log( '[DELIVERY_TRACKER] ─────────────────────────────────────' );
	}
}
