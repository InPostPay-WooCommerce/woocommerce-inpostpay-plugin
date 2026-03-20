<?php

namespace Ilabs\Inpost_Pay;

abstract class IziJsonResponse {
	protected function mapConsents(): array {
		$consents = get_option( 'izi_consents' );
		$response = [];
		if ( ! is_array( $consents ) ) {
			return [];
		}
		foreach ( $consents as $key => $consent ) {
			if ( count( $response ) >= 10 ) {
				break;
			}
			if (
				! isset( $consent['required'] )  || ! $consent['required']
			) {
				continue;
			}

			if (!empty($consent['url']) && !empty($consent['text'])) {
				$response[] = [
					'consent_id'               => $key + 1,
					'consent_link'             => get_permalink( (int) $consent['url'] ),
					'label_link'               => get_the_title( (int) $consent['url'] ),
					'additional_consent_links' => $this->map_additional_consents_links( $consent['additional_consent_links'] ?? null ),
					'consent_description'      => $consent['text'],
					'consent_version'          => count( wp_get_post_revisions( $consent['url'] ) ) + 1,
					'requirement_type'         => $consent['required']
				];
			} else if (!empty($consent['additional_consent_links'])) {
				$first_key = array_key_first($consent['additional_consent_links']);
				$consent['id'] = $consent['additional_consent_links'][$first_key]['id'];
				$consent['url'] = $consent['additional_consent_links'][$first_key]['url'];
				$consent['label'] = $consent['additional_consent_links'][$first_key]['label'];

				unset($consent['additional_consent_links'][$first_key]);
				$response[] = [
					'consent_id'               => $consent['id'] ?? $key+1,
					'consent_link'             => get_permalink( (int) $consent['url'] ),
					'label_link'               => $consent['label'] ?? get_the_title( (int) $consent['url'] ),
					'additional_consent_links' => $this->map_additional_consents_links( $consent['additional_consent_links'] ),
					'consent_description'      => $consent['text'],
					'consent_version'          => count( wp_get_post_revisions( $consent['url'] ) ) + 1,
					'requirement_type'         => $consent['required']
				];
			}

		}

		return $response;
	}

	private function map_additional_consents_links( $additional_consent_links ): array {
		if ( ! is_array( $additional_consent_links ) || count( $additional_consent_links ) === 0 ) {
			return [];
		}

		$links = [];


		foreach ( $additional_consent_links as $key => $additional_content_link ) {
			$links[] = [
				'id'           => $additional_content_link['id'] ?? $key,
				'consent_link' => get_permalink( (int) $additional_content_link['url'] ),
				'label_link'   => $additional_content_link['label'] ?? get_the_title( (int) $additional_content_link['url'] ),
			];
		}

		return $links;
	}
}
