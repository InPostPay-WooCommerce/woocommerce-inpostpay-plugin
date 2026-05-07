<?php
/**
 * Agreements configuration section view.
 *
 * @package InpostPay
 */

$consents = get_option( 'izi_consents' );

if ( ! is_array( $consents ) ) {
	$consents = array();
}

$filtered = array();

foreach ( $consents as $consent_id => $consent_item ) {
	if ( isset( $consent_item['text'] ) && $consent_item['text'] ) {
		if ( empty( $consent_item['additional_consent_links'] ) ) {
			$consent_item['additional_consent_links'] = array(
				array(
					'id'    => '',
					'label' => '',
					'url'   => '',
				),
			);
		}

		if ( isset( $consent_item['additional_consent_links'] ) && count( $consent_item['additional_consent_links'] ) > 0 ) {
			$additional_consent_links = $consent_item['additional_consent_links'];
			unset( $consent_item['additional_consent_links'] );

			$link_index = 0;

			foreach ( $additional_consent_links as $additional_link ) {
				$consent_item['additional_consent_links'][ $link_index ] = $additional_link;
				++$link_index;
			}
		}

		$filtered[ $consent_id ] = $consent_item;
	}
}

$consents = $filtered;

if ( count( $consents ) < 1 ) {
	$consents[] = array(
		'url'                      => '',
		'text'                     => '',
		'required'                 => '',
		'additional_consent_links' => array(
			array(
				'id'    => '',
				'label' => '',
				'url'   => '',
			),
		),
	);
}
?>

<div class="agreements-container">
	<div id="consentList">
		<?php foreach ( $consents as $consent_id => $consent ) : ?>
			<div class="consent-item" data-consent-id="<?php echo esc_attr( $consent_id ); ?>">
				<div class="d-flex-align-center">
					<div style="flex:50%" class="flex-50">
						<label>
							<?php esc_html_e( 'Descriptions visible in application', 'inpost-pay' ); ?>
						</label>
						<br/>
						<textarea
							class="consentDescription"
							rows="2"
							cols="50"
							maxlength="500"
							name="izi_consents[<?php echo esc_attr( $consent_id ); ?>][text]"
						><?php echo esc_textarea( $consent['text'] ?? '' ); ?></textarea>
						<div class="input-tooltip-wrapper">
							<img
								src="<?php echo esc_url( plugin_dir_url( __FILE__ ) . '../../assets/img/tooltip.svg' ); ?>"
								alt=""
							>
							<div class="input-tooltip-box">
								<p>
									<?php esc_html_e( 'Add a description to be displayed with the agreement in the InPost mobile application', 'inpost-pay' ); ?>
								</p>
							</div>
						</div>
					</div>

					<div style="flex:30%">
						<div class="input-tooltip d-flex-align-center">
							<label class="consent-label">
								<?php esc_html_e( 'Is it required', 'inpost-pay' ); ?>
							</label>
							<div class="input-tooltip-wrapper">
								<img
									src="<?php echo esc_url( plugin_dir_url( __FILE__ ) . '../../../assets/img/tooltip.svg' ); ?>"
									alt=""
								>
								<div class="input-tooltip-box">
									<p>
										<?php esc_html_e( 'Specify the page to which your customer will be redirected when clicking on a specific agreement in the InPost mobile application', 'inpost-pay' ); ?>
									</p>
								</div>
							</div>
						</div>
						<select class="requirementType"
								name="izi_consents[<?php echo esc_attr( $consent_id ); ?>][required]">
							<?php
							$selected_option           = $consent['required'] ?? '';
							$consent_requirement_array = $consent_requirement ?? array();

							foreach ( $consent_requirement_array as $value => $label ) {
								$selected = ( $value === $selected_option ) ? 'selected' : '';
								echo '<option ' . esc_attr( $selected ) . ' value="' . esc_attr( $value ) . '">' . esc_html( $label ) . '</option>';
							}
							?>
						</select>
					</div>

					<div style="flex:20%">
						<button type="button" class="remove-btn" onclick="removeConsentItem( this )">
							<img
								src="<?php echo esc_url( plugin_dir_url( __FILE__ ) . '../../../assets/img/remove.svg' ); ?>"
								alt=""
							>
							<?php esc_html_e( 'Remove', 'inpost-pay' ); ?>
						</button>
					</div>
				</div>

				<?php if ( ! empty( $consent['url'] ) && empty( $consent['additional_consent_links'] ) ) : ?>
					<input type="hidden" name="izi_consents[<?php echo esc_attr( $consent_id ); ?>][url]"
							value="<?php echo esc_url( $consent['url'] ); ?>"/>
				<?php else : ?>
					<input type="hidden" name="izi_consents[<?php echo esc_attr( $consent_id ); ?>][url]" value=""/>
				<?php endif; ?>

				<?php
				if ( ! empty( $consent['url'] ) && empty( $consent['additional_consent_links'] ) ) {
					$slug                                  = get_post_field( 'post_name', (int) $consent['url'] );
					$consent['additional_consent_links'][] = array(
						'id'    => $slug,
						'label' => get_the_title( (int) $consent['url'] ),
						'url'   => $consent['url'],
					);
				}

				if ( ! empty( $consent['additional_consent_links'] ) ) {
					$last_key = key( array_slice( array_keys( $consent['additional_consent_links'] ), - 1, 1, true ) );
				} else {
					$last_key = 0;
				}
				?>

				<div class="additional-consent-links">
					<div
						id="additionalLinks<?php echo esc_attr( $consent_id ); ?>"
						class="justify-content-between additional-links-container"
						data-last-key="<?php echo esc_attr( (string) $last_key ); ?>"
					>
						<?php if ( isset( $consent['additional_consent_links'] ) && count( $consent['additional_consent_links'] ) > 0 ) : ?>
							<?php foreach ( $consent['additional_consent_links'] as $additional_link_id => $additional_link ) : ?>
								<div class="d-flex-align-center additional-link-container">
									<div>
										<label><?php esc_html_e( 'Link identifier', 'inpost-pay' ); ?></label>
										<br/>
										<input
											type="text"
											name="izi_consents[<?php echo esc_attr( $consent_id ); ?>][additional_consent_links][<?php echo esc_attr( $additional_link_id ); ?>][id]"
											class="additional-link-identifier"
											value="<?php echo isset( $additional_link['id'] ) && '' !== $additional_link['id'] ? esc_attr( $additional_link['id'] ) : ''; ?>"
										>
									</div>

									<div>
										<label><?php esc_html_e( 'Link label', 'inpost-pay' ); ?></label>
										<br/>
										<input
											type="text"
											name="izi_consents[<?php echo esc_attr( $consent_id ); ?>][additional_consent_links][<?php echo esc_attr( $additional_link_id ); ?>][label]"
											class="additional-link-label"
											value="<?php echo esc_attr( $additional_link['label'] ?? '' ); ?>"
											placeholder="<?php echo esc_attr( get_the_title( (int) ( $additional_link['url'] ?? 0 ) ) ); ?>"
										>
									</div>

									<div>
										<label for="consentLink" class="consent-label">
											<?php esc_html_e( 'Agreement address', 'inpost-pay' ); ?>
										</label>
										<br/>
										<?php
										wp_dropdown_pages(
											array(
												'name'     => sprintf(
													'izi_consents[%1$s][additional_consent_links][%2$s][url]',
													esc_attr( (string) $consent_id ),
													esc_attr( (string) $additional_link_id )
												),
												'selected' => isset( $additional_link['url'] ) ? absint( $additional_link['url'] ) : 0,
												'show_option_none' => esc_html__( 'Select', 'inpost-pay' ),
												'class'    => 'additional-consent-link',
											)
										);
										?>
									</div>

									<div>
										<?php if ( $additional_link_id > 0 ) : ?>
											<button
												type="button"
												class="remove-additional-link-btn"
												onclick="removeAdditionalLink( this )"
											>
												<?php esc_html_e( 'Remove', 'inpost-pay' ); ?>
											</button>
										<?php endif; ?>
									</div>
								</div>
							<?php endforeach; ?>
						<?php endif; ?>
					</div>

					<hr>

					<?php
					$display_button = '';

					if ( isset( $consent['additional_consent_links'] ) && count( $consent['additional_consent_links'] ) >= 3 ) {
						$display_button = 'style="display: none;"';
					}
					?>

					<button
						type="button"
						class="add-additional-link-btn"
						<?php echo wp_kses_post( $display_button ); ?>
						onclick="addAdditionalLink(<?php echo esc_attr( $consent_id ); ?>)"
					>
						+ <?php esc_html_e( 'Add additional link', 'inpost-pay' ); ?>
					</button>
				</div>
			</div>
		<?php endforeach; ?>
	</div>

	<?php
	$display_button_add_consent_button = '';

	if ( count( $consents ) > 9 ) {
		$display_button_add_consent_button = 'style="display: none;"';
	}
	?>

	<button
		id="addConsentButton"
		type="button"
		onclick="addConsentItem()"
		<?php echo wp_kses_post( $display_button_add_consent_button ); ?>
	>
		+ <?php esc_html_e( 'Add Consent', 'inpost-pay' ); ?>
	</button>
</div>
