<div id='izi_thank_you_page_id' class='input-wrapper mt-2 mb-2'
	 style='display: flex; flex-direction: column; gap: 4px;'>
	<label class='label-black'>
		<?php _e( 'Custom thank you page (optional)', 'inpost-pay' ); ?>
	</label>

	<div class='input-tooltip'>
		<?php
		$selected_page_id = (int) get_option( 'izi_thank_you_page_id', 0 );
		wp_dropdown_pages(
			array(
				'name'              => 'izi_thank_you_page_id',
				'selected'          => $selected_page_id,
				'show_option_none'  => __( '– Use default virtual page –', 'inpost-pay' ),
				'option_none_value' => 0,
			)
		);
		?>
		<div class="input-tooltip-wrapper">
			<img src="<?php echo plugin_dir_url( __FILE__ ) . '../../../assets/img/tooltip.svg'; ?>" alt="">
			<div class="input-tooltip-box">
				<p>
					<?php
					_e(
						'Select a WordPress page containing the [inpost_thank_you] shortcode. '
						. 'If not selected, the default virtual page will be used. '
						. 'Use this option if your theme does not support virtual pages (e.g. Sage/Blade themes).',
						'inpost-pay'
					);
					?>
				</p>
			</div>
		</div>
	</div>
</div>

<div id='izi_refresh_after_add_to_cart' class='input-wrapper mt-2 mb-2'>
	<div class='form-group form-group--row start-container'>
		<div class='input-tooltip'>
			<label class='label-gray'>
				<?php

				use Ilabs\Inpost_Pay\Lib\Utils\HotProductUtils;

				_e( 'Refresh page after adding product to cart', 'inpost-pay' );
				?>
			</label>
			<div class="input-tooltip-wrapper">
				<img src="<?php echo plugin_dir_url( __FILE__ ) . '../../../assets/img/tooltip.svg'; ?>" alt="">
				<div class="input-tooltip-box">
					<p>
						<?php
						_e(
							'Enable this option to refresh the page after cart changes. '
							. 'This may be required depending on how your theme handles cart messages '
							. '(e.g. if the “product added to cart” notice only appears after a refresh).',
							'inpost-pay'
						);
						?>
					</p>
				</div>
			</div>
		</div>
		<?php
		$refresh_page_enabled = (bool) get_option( 'izi_refresh_after_add_to_cart', true );
		?>
		<input <?= $refresh_page_enabled ? 'checked' : '' ?>
			type="checkbox"
			name="izi_refresh_after_add_to_cart"
			value="1">
	</div>
</div>

<div id='izi_main_image_only' class='input-wrapper mt-1 mb-1'>
	<?php
		$global_main_image_only_raw = get_option( 'izi_main_image_only', false );
		$is_main_image_only         = filter_var( $global_main_image_only_raw, FILTER_VALIDATE_BOOLEAN );

		$has_hot_products   = HotProductUtils::has_hot_products();
		$hp_count           = HotProductUtils::count_hot_products();
	?>

	<div class='form-group form-group--row start-container'>
		<div class='input-tooltip'>
			<label class='label-gray'>
				<?php _e('Show only main Hot Product image in the InPost Pay app', 'inpost-pay'); ?>
			</label>

			<div class="input-tooltip-wrapper">
				<img src="<?php echo plugin_dir_url(__FILE__) . '../../../assets/img/tooltip.svg'; ?>" alt="">
				<div class="input-tooltip-box">
					<p>
						<?php
						_e(
							'Enable this option if you want customers using the InPost Pay app to see only the main product image. '
							. 'If disabled, the full product gallery will be shown (recommended default).',
							'inpost-pay'
						);
						?>
					</p>
				</div>
			</div>
		</div>

		<?php if ($has_hot_products): ?>
			<div class="input-tooltip-wrapper">
				<input type="checkbox"
					<?= $is_main_image_only ? 'checked' : '' ?>
					   disabled
					   style="cursor: not-allowed; opacity: 0.6;">
				<div class="input-tooltip-box input-tooltip-box--error">
					<p>
						<?php
							printf(
							/* translators: %d - number of configured Hot Products */
								__(
									'Number of configured Hot Products: %d. Remove all Hot Products to change this setting.',
									'inpost-pay'
								),
								$hp_count
							);
						?>
					</p>
				</div>
			</div>
		<?php else: ?>
			<input type="checkbox"
				<?= $is_main_image_only ? 'checked' : '' ?>
				   name="izi_main_image_only"
				   value="1">
		<?php endif; ?>
	</div>
</div>


<div class='input-wrapper mt-2 mb-2'>
	<div class='form-group form-group--row start-container'>
		<div class='input-tooltip'>
			<label class='label-gray'>
				<?php
				_e( 'Enable custom basket response (for troubleshooting only)', 'inpost-pay' );
				?>
			</label>
			<div class="input-tooltip-wrapper">
				<img src="<?php echo plugin_dir_url( __FILE__ ) . '../../../assets/img/tooltip.svg'; ?>" alt="">
				<div class="input-tooltip-box">
					<p>
						<?php
						_e(
							'This option enables an alternative basket response structure required by some versions of the InPost Pay. Activate only if instructed by InPost technical support. Do not enable unless necessary – using this option may cause inconsistencies in basket calculations, shipping costs, or discount handling.',
							'inpost-pay'
						);
						?>
					</p>
				</div>
			</div>
		</div>
		<?php
		$is_custom_mapper_enabled = (bool) get_option( 'izi_custom_basket_response_enabled', false );
		?>

		<input <?= $is_custom_mapper_enabled ? 'checked' : '' ?> type="checkbox"
																 name="izi_custom_basket_response_enabled"
																 value="1">
	</div>
</div>

<div class='input-wrapper mt-2 mb-2'>
	<div class='form-group form-group--row start-container'>
		<div class='input-tooltip'>
			<label class='label-gray'>
				<?php
				_e( 'Use modern response format (recommended)', 'inpost-pay' );
				?>
			</label>
			<div class="input-tooltip-wrapper">
				<img src="<?php echo plugin_dir_url( __FILE__ ) . '../../../assets/img/tooltip.svg'; ?>" alt="">
				<div class="input-tooltip-box">
					<p>
						<?php
						_e(
							'This option enables a modern and more compatible response format for integrations and WooCommerce behavior. It is currently being tested and will soon become the default across all environments. We recommend keeping it enabled to ensure the best experience and future compatibility.',
							'inpost-pay'
						);
						?>
					</p>
				</div>
			</div>
		</div>
		<?php
		$is_custom_response_enabled = get_option( 'izi_custom_response_enabled', true );
		?>

		<input <?= $is_custom_response_enabled ? 'checked' : '' ?> type="checkbox"
																   name="izi_custom_response_enabled"
																   value="1">
	</div>
</div>

<div class='input-wrapper mt-2 mb-2'>
	<div class='form-group form-group--row start-container'>
		<div class='input-tooltip'>
			<label class='label-gray'>
				<?php
				_e( 'Early basket update response (bypass shutdown hook)', 'inpost-pay' );
				?>
			</label>
			<div class="input-tooltip-wrapper">
				<img src="<?php echo plugin_dir_url( __FILE__ ) . '../../../assets/img/tooltip.svg'; ?>" alt="">
				<div class="input-tooltip-box">
					<p>
						<?php
						_e(
							'Enable this option if the mobile app experiences issues with basket updates returning empty responses. '
							. 'This mode bypasses the shutdown hook mechanism for basket update requests from the app, '
							. 'returning responses immediately. This helps with caching plugins or server configurations '
							. 'that flush responses early. Only affects basket updates from the mobile app.',
							'inpost-pay'
						);
						?>
					</p>
				</div>
			</div>
		</div>
		<?php
		$is_early_response_enabled = filter_var( get_option( 'izi_early_update_response_enabled', false ), FILTER_VALIDATE_BOOLEAN );
		?>

		<input <?= $is_early_response_enabled ? 'checked' : '' ?> type="checkbox"
		   name="izi_early_update_response_enabled"
		   value="1">
	</div>
</div>

