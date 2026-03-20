<?php

namespace Ilabs\Inpost_Pay\Lib\Attribution;

class Attribution {

	private ?string $source_type = null;
	private ?string $referrer = null;


	private ?string $utm_campaign = null;
	private ?string $utm_source = null;
	private ?string $utm_medium = null;
	private ?string $utm_content = null;
	private ?string $utm_id = null;
	private ?string $utm_term = null;
	private ?string $utm_source_platform = null;
	private ?string $utm_creative_format = null;
	private ?string $utm_marketing_tactic = null;


	private ?string $session_entry = null;
	private ?string $session_start_time = null;
	private ?string $session_pages = null;
	private ?string $session_count = null;
	private ?string $user_agent = null;

	public function get_source_type(): ?string {
		return $this->source_type;
	}

	public function set_source_type( ?string $source_type ): void {
		if ( $source_type ) {
			$this->source_type = sanitize_text_field( wp_unslash( $source_type ) );
		}
	}

	public function get_referrer(): ?string {
		return $this->referrer;
	}

	public function set_referrer( ?string $referrer ): void {
		if ( $referrer ) {
			$this->referrer = sanitize_text_field( wp_unslash( $referrer ) );
		}
	}

	public function get_utm_campaign(): ?string {
		return $this->utm_campaign;
	}

	public function set_utm_campaign( ?string $utm_campaign ): void {
		if ( $utm_campaign ) {
			$this->utm_campaign = sanitize_text_field( wp_unslash( $utm_campaign ) );
		}
	}

	public function get_utm_source(): ?string {
		return $this->utm_source;
	}

	public function set_utm_source( ?string $utm_source ): void {
		if ( $utm_source ) {
			$this->utm_source = sanitize_text_field( wp_unslash( $utm_source ) );
		}
	}

	public function get_utm_medium(): ?string {
		return $this->utm_medium;
	}

	public function set_utm_medium( ?string $utm_medium ): void {
		if ( $utm_medium ) {
			$this->utm_medium = sanitize_text_field( wp_unslash( $utm_medium ) );
		}
	}

	public function get_utm_content(): ?string {
		return $this->utm_content;
	}

	public function set_utm_content( ?string $utm_content ): void {
		if ( $utm_content ) {
			$this->utm_content = sanitize_text_field( wp_unslash( $utm_content ) );
		}
	}

	public function get_utm_id(): ?string {
		return $this->utm_id;
	}

	public function set_utm_id( ?string $utm_id ): void {
		if ( $utm_id ) {
			$this->utm_id = sanitize_text_field( wp_unslash( $utm_id ) );
		}
	}

	public function get_utm_term(): ?string {
		return $this->utm_term;
	}

	public function set_utm_term( ?string $utm_term ): void {
		if ( $utm_term ) {
			$this->utm_term = sanitize_text_field( wp_unslash( $utm_term ) );
		}
	}

	public function get_utm_source_platform(): ?string {
		return $this->utm_source_platform;
	}

	public function set_utm_source_platform( ?string $utm_source_platform ): void {
		if ( $utm_source_platform ) {
			$this->utm_source_platform = sanitize_text_field( wp_unslash( $utm_source_platform ) );
		}
	}

	public function get_utm_creative_format(): ?string {
		return $this->utm_creative_format;
	}

	public function set_utm_creative_format( ?string $utm_creative_format ): void {
		if ( $utm_creative_format ) {
			$this->utm_creative_format = sanitize_text_field( wp_unslash( $utm_creative_format ) );
		}
	}

	public function get_utm_marketing_tactic(): ?string {
		return $this->utm_marketing_tactic;
	}

	public function set_utm_marketing_tactic( ?string $utm_marketing_tactic ): void {
		if ( $utm_marketing_tactic ) {
			$this->utm_marketing_tactic = sanitize_text_field( wp_unslash( $utm_marketing_tactic ) );
		}
	}

	public function get_session_entry(): ?string {
		return $this->session_entry;
	}

	public function set_session_entry( ?string $session_entry ): void {
		if ( $session_entry ) {
			$this->session_entry = sanitize_text_field( wp_unslash( $session_entry ) );
		}
	}

	public function get_session_start_time(): ?string {
		return $this->session_start_time;
	}

	public function set_session_start_time( ?string $session_start_time ): void {
		if ( $session_start_time ) {
			$this->session_start_time = sanitize_text_field( wp_unslash( $session_start_time ) );
		}
	}

	public function get_session_pages(): ?string {
		return $this->session_pages;
	}

	public function set_session_pages( ?string $session_pages ): void {
		if ( $session_pages ) {
			$this->session_pages = sanitize_text_field( wp_unslash( $session_pages ) );
		}
	}

	public function get_session_count(): ?string {
		return $this->session_count;
	}

	public function set_session_count( ?string $session_count ): void {
		if ( $session_count ) {
			$this->session_count = sanitize_text_field( wp_unslash( $session_count ) );
		}
	}

	public function get_user_agent(): ?string {
		return $this->user_agent;
	}

	public function set_user_agent( ?string $user_agent ): void {
		if ( $user_agent ) {
			$this->user_agent = sanitize_text_field( wp_unslash( $user_agent ) );
		}
	}

	public function to_array(): array {
		return array(
			'source_type' => $this->source_type,
			'referrer' => $this->referrer,
			'utm_campaign' => $this->utm_campaign,
			'utm_source' => $this->utm_source,
			'utm_medium' => $this->utm_medium,
			'utm_content' => $this->utm_content,
			'utm_id' => $this->utm_id,
			'utm_term' => $this->utm_term,
			'utm_source_platform' => $this->utm_source_platform,
			'utm_creative_format' => $this->utm_creative_format,
			'utm_marketing_tactic' => $this->utm_marketing_tactic,
			'session_entry' => $this->session_entry,
			'session_start_time' => $this->session_start_time,
			'session_pages' => $this->session_pages,
			'session_count' => $this->session_count,
			'user_agent' => $this->user_agent,
		);
	}


}
