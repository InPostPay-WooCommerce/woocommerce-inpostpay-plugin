<div id='additionalLinkTemplate'>
	<div class='d-flex-align-center justify-content-between additional-link-container'>
		<div style='flex: 25%'>
			<label><?php _e( 'Link identifier', 'inpost-pay' ); ?></label>
			<br/>
			<input type="text" name="none" class="additional-link-identifier">
		</div>
		<div style="flex: 25%">
			<label><?php _e( 'Link label', 'inpost-pay' ); ?></label>
			<br/>
			<input type="text" name="none" class="additional-link-label">
		</div>
		<div style="flex: 25%">
			<label for="consentLink" class="consent-label"><?php _e(
					'Agreement address',
					'inpost-pay'
				); ?></label>
			<br/>
			<?php wp_dropdown_pages( [
				'name'             => 'none',
				'show_option_none' => __(
					'Select',
					'inpost-pay'
				),
				'class'            => 'additional-consent-link',
			] ); ?>
		</div>
		<div style="flex: 25%">
			<button type="button" class="remove-additional-link-btn">
				<img src="<?php echo plugin_dir_url(
										 __FILE__
									 ) .
									 '../../../assets/img/remove.svg'; ?>" alt="">
				<?php _e( 'Remove', 'inpost-pay' ); ?>
			</button>
		</div>
	</div>
</div>


<div id="consentTemplate">
	<div class="consent-item" data-consent-id="1">
		<div class="d-flex-align-center">

			<div style="flex:50%" class="flex-50">


				<label><?php _e(
						'Descriptions visible in application',
						'inpost-pay'
					); ?></label>
				<textarea class="consentDescription" rows="2" cols="50" maxlength="500"></textarea>
				<div class="input-tooltip-wrapper">
					<img src="<?php echo plugin_dir_url(
											 __FILE__
										 ) .
										 '../../assets/img/tooltip.svg'; ?>" alt="">
					<div class="input-tooltip-box">
						<p><?php _e(
								'Add a description to be displayed with the agreement in the InPost mobile application',
								'inpost-pay'
							); ?></p>
					</div>
				</div>


			</div>
			<div style="flex:30%" class="flex-30">


				<div class="input-tooltip d-flex-align-center">
					<label><?php _e(
							'Is it required',
							'inpost-pay'
						); ?></label>

					<div class="input-tooltip-wrapper">
						<img src="<?php echo plugin_dir_url(
												 __FILE__
											 ) .
											 '../../../assets/img/tooltip.svg'; ?>" alt="">
						<div class="input-tooltip-box">
							<p><?php _e(
									'Specify whether the agreement is required or optional',
									'inpost-pay'
								); ?></p>
						</div>
					</div>
					<br/>
					<select class="requirementType" name="izi_consents[<?= $id ?>][required]">
						<?php
						foreach (
							$consentRequirement
							as $value => $label
						) {
							$selected =
								$value == $selectedOption
									? 'selected'
									: '';
							echo "<option value='{$value}'>{$label}</option>";
						}
						?>
					</select>
				</div>


			</div>
			<div style="flex:20%" class="flex-20">


				<button type="button" class="remove-btn">
					<img src="<?php echo plugin_dir_url(
											 __FILE__
										 ) .
										 '../../../assets/img/remove.svg'; ?>" alt="">
					<?php _e( 'Remove', 'inpost-pay' ); ?>
				</button>


			</div>
		</div>

		<input type="hidden" name="none" class="consentLink" value=""/>

		<div class="additional-consent-links">
			<label><?php _e( 'Additional Consent Links:', 'inpost-pay' ); ?></label>
			<div class="justify-content-between additional-links-container" data-last-key="0">
				<div class="d-flex-align-center additional-link-container default-link">
					<div style="flex: 25%">
						<label><?php _e( 'Link identifier', 'inpost-pay' ); ?></label>
						<input type="text" name=""
							   class="additional-link-identifier">
					</div>
					<div style="flex: 25%">
						<label><?php _e( 'Link label', 'inpost-pay' ); ?></label>
						<input type="text" name=""
							   class="additional-link-label">
					</div>
					<div style="flex: 25%;">
						<label><?php _e( 'Agreement address', 'inpost-pay' ); ?></label>
						<?php wp_dropdown_pages( [
							'name'             => '',
							'show_option_none' => __( 'Select', 'inpost-pay' ),
							'class'            => 'additional-consent-link',
						] ); ?>
					</div>
					<div style="flex: 25%">
					</div>
				</div>
			</div>
			<hr>
			<button type="button"
					class="add-additional-link-btn">+ <?php _e( 'Add additional link', 'inpost-pay' ); ?></button>
		</div>
	</div>
</div>
