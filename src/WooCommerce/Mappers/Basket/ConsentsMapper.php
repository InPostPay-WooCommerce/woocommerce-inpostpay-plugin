<?php

namespace Ilabs\Inpost_Pay\WooCommerce\Mappers\Basket;

use Ilabs\Inpost_Pay\Lib\item\order\Consent;
use Ilabs\Inpost_Pay\Logger;

class ConsentsMapper {
	/**
	 * Maps consent data from options to an array of Consent objects
	 *
	 * @return array An array of serialized Consent objects
	 */
	public function mapConsents(): array {
		$consents = get_option( 'izi_consents' );
		$response = array();

		if ( ! is_array( $consents ) ) {
			return array();
		}

		foreach ( $consents as $key => $consent ) {
			if ( count( $response ) >= 10 ) {
				break;
			}

			if ( ! isset( $consent['required'] ) || ! $consent['required'] ) {
				continue;
			}

			$consentObject = new Consent();

			if ( ! empty( $consent['url'] ) && ! empty( $consent['text'] ) ) {
				$consentObject->set_consent_id( $key + 1 );
				$consentObject->set_consent_link( get_permalink( (int) $consent['url'] ) );
				$consentObject->set_label_link( get_the_title( (int) $consent['url'] ) );
				$consentObject->set_additional_consent_links( $this->map_additional_consents_links( $consent['additional_consent_links'] ?? null ) );
				$consentObject->set_consent_description( $consent['text'] );
				$consentObject->set_consent_version( count( wp_get_post_revisions( $consent['url'] ) ) + 1 );
				$consentObject->set_requirement_type( $consent['required'] );

				$serialized = $consentObject->jsonSerialize();
				$response[] = $serialized;
			} elseif ( ! empty( $consent['additional_consent_links'] ) ) {
				$first_key  = array_key_first( $consent['additional_consent_links'] );
				$consent_id = $consent['additional_consent_links'][ $first_key ]['id'] ?? $key + 1;
				$url        = $consent['additional_consent_links'][ $first_key ]['url'];
				$label      = $consent['additional_consent_links'][ $first_key ]['label'] ?? get_the_title( (int) $url );

				// Remove the first link since we're using it as the main consent
				$additional_links = $consent['additional_consent_links'];
				unset( $additional_links[ $first_key ] );

				$consentObject->set_consent_id( (string) $consent_id );
				$consentObject->set_consent_link( get_permalink( (int) $url ) );
				$consentObject->set_label_link( $label );
				$consentObject->set_additional_consent_links( $this->map_additional_consents_links( $additional_links ) );
				$consentObject->set_consent_description( $consent['text'] );
				$consentObject->set_consent_version( count( wp_get_post_revisions( $url ) ) + 1 );
				$consentObject->set_requirement_type( $consent['required'] );

				$serialized = $consentObject->jsonSerialize();

				$response[] = $serialized;
			}
		}

		return $response;
	}

	/**
	 * Maps additional consent links data to the required format
	 *
	 * @param array|null $additional_consent_links Array of additional consent links
	 *
	 * @return array Mapped additional consent links
	 */
	private function map_additional_consents_links( ?array $additional_consent_links ): array {
		if ( ! is_array( $additional_consent_links ) || count( $additional_consent_links ) === 0 ) {
			return array();
		}

		$links = array();

		foreach ( $additional_consent_links as $key => $additional_content_link ) {
			$links[] = array(
				'id'           => $additional_content_link['id'] ?? $key,
				'consent_link' => get_permalink( (int) $additional_content_link['url'] ),
				'label_link'   => $additional_content_link['label'] ?? get_the_title( (int) $additional_content_link['url'] ),
			);
		}

		return $links;
	}
}
