<?php
use Ilabs\Inpost_Pay\Lib\config\product\HotProductsConfig;
$locale = get_locale();
$lang   = strpos( $locale, 'pl' ) === 0 ? 'pl' : ( strpos( $locale, 'en' ) === 0 ? 'en' : 'pl' );
?>
<link rel="stylesheet" href="<?php echo plugin_dir_url( __FILE__ ); ?>../../../assets/css/flatpickr/flatpickr.min.css">
<script src="<?php echo plugin_dir_url( __FILE__ ); ?>../../../assets/js/flatpickr/flatpickr.js"></script>
<script src="<?php echo plugin_dir_url( __FILE__ ); ?>../../../assets/js/flatpickr/pl.js"></script>


<div class="hot-products-wrapper" data-lang="<?php echo esc_attr( $lang ); ?>" data-plugin-url="<?php echo plugin_dir_url( __FILE__ ); ?>" data-api-error-title="<?php _e( 'An error occurred', 'inpost-pay' ); ?>" data-api-error-message="<?php _e( 'Error while loading', 'inpost-pay' ); ?>" data-no-results-message="<?php _e( 'No results found', 'inpost-pay' ); ?>" data-products-limit-reached="<?php echo _e( 'Hot products limit reached', 'inpost-pay' ); ?>" data-pagination-text="<?php _e( 'of', 'inpost-pay' ); ?>" data-accepted-text="<?php _e( 'Accepted', 'inpost-pay' ); ?>" data-rejected-text="<?php _e( 'Rejected', 'inpost-pay' ); ?>" data-remove-text="<?php _e( 'Remove', 'inpost-pay' ); ?>" data-highlight-period-not-selected="<?php _e( 'Not selected', 'inpost-pay' ); ?>" data-availability-set-text="<?php _e( 'Highlight period set', 'inpost-pay' ); ?>" data-api-success-title="<?php _e( 'Success', 'inpost-pay' ); ?>">
	<div class="overlay"></div>
	<div class="hot-products">
		<div class="hot-products__header">
			<div class="hot-products__heading-wrapper">
				<h2 class="hot-products__title"><?php _e( 'Hot Products in InPost App', 'inpost-pay' ); ?></h2>
			</div>
			<div class="hot-products__actions">
				<button class="hot-products-select-products inpost-btn inpost-btn--primary"><?php _e( 'Select Products', 'inpost-pay' ); ?></button>
				<button class="hot-product__remove-all inpost-btn inpost-btn--remove" style="display: none;">
					<img src="<?php echo plugin_dir_url( __FILE__ ) . '../../../assets/img/hp-remove.svg'; ?>" alt="">
					<span><?php _e( 'Remove All', 'inpost-pay' ); ?></span>
				</button>
			</div>
		</div>
		<div class="hot-products__table-container">
			<table class="hot-products__table hot-products__table--hot-products">
				<thead>
					<tr>
						<th></th>
						<th><?php _e( 'Product', 'inpost-pay' ); ?></th>
						<th><?php _e( 'Product ID', 'inpost-pay' ); ?></th>
						<th><?php _e( 'Highlight period', 'inpost-pay' ); ?></th>
						<th></th>
					</tr>
				</thead>
				<tbody>
				</tbody>
			</table>
		</div>
	</div>
	<div class="hot-products-popup-table">
		<div class="hot-products hot-products--popup">
			<div class="hot-products__header">
				<h2 class="hot-products__title"><?php _e( 'Highlight products', 'inpost-pay' ); ?></h2>
				<img class="hot-products__popup-close" src="<?php echo plugin_dir_url( __FILE__ ) . '../../../assets/img/hp-close.svg'; ?>" alt="">
			</div>
			<div class="hot-products__table-wrapper hot-products__table-wrapper--categories">
				<div class="hot-products__search-wrapper">
					<div class="hot-products__search-input-wrapper">
						<input type="text" class="hot-products__search hot-products__search--categories" placeholder="<?php _e( 'Search in the list', 'inpost-pay' ); ?>">
						<img class="hot-products__search-icon" src="<?php echo plugin_dir_url( __FILE__ ) . '../../../assets/img/hp-search.svg'; ?>" alt="">
					</div>
					<div class="hot-products__search-info">
						<div class="hot-products__popup-info-wrapper">
							<p class="hot-products__popup-selected-info"><?php _e( 'Selected', 'inpost-pay' ); ?> <span class="hot-products__selected-count">0</span>/<span class="hot-products__selected-count-limit">5</span></p>
						</div>
					</div>
				</div>
				<div class="hot-products__table-container">
					<table class="hot-products__table hot-products__table--categories">
						<thead>
							<tr>
								<th><?php _e( 'Category', 'inpost-pay' ); ?></th>
								<th><?php _e( 'Category ID', 'inpost-pay' ); ?></th>
								<th></th>
							</tr>
						</thead>
						<tbody>
						</tbody>
					</table>
				</div>
				<div class="hot-products__table-actions">
					<button type="button" class="hot-products__table-actions-categories-btn inpost-btn inpost-btn--primary inpost-btn--large"><?php _e( 'Cancel', 'inpost-pay' ); ?></button>
				</div>
			</div>
			<div class="hot-products__table-wrapper hot-products__table-wrapper--products hot-products__table-wrapper--hidden" data-category-id="0" data-category-name="">
				<div class="hot-products__search-wrapper">
					<div class="hot-products__search-input-wrapper">
						<input type="text" class="hot-products__search hot-products__search--products" placeholder="<?php _e( 'Search in the list', 'inpost-pay' ); ?>">
						<img class="hot-products__search-icon" src="<?php echo plugin_dir_url( __FILE__ ) . '../../../assets/img/hp-search.svg'; ?>" alt="">
					</div>
					<div class="hot-products__search-info">
						<div class="hot-products__popup-info-wrapper">
							<p class="hot-products__popup-selected-info hot-products__popup-selected-info--products"><?php _e( 'Selected', 'inpost-pay' ); ?> <span class="hot-products__selected-count--products"></p>
						</div>
					</div>
				</div>
				<div class="hot-products__table-container">
					<table class="hot-products__table hot-products__table--products">
						<thead>
							<tr>
								<th></th>
								<th><?php _e( 'Product', 'inpost-pay' ); ?></th>
								<th><?php _e( 'Product ID', 'inpost-pay' ); ?></th>
							</tr>
						</thead>
						<tbody>
						</tbody>
					</table>
				</div>
				<div class="hot-products__pagination">
					<div class="hot-products__pagination-info">
						<span class="hot-products__pagination-total-items">0</span> <?php _e( 'results', 'inpost-pay' ); ?>
					</div>
					<div class="hot-products__pagination-buttons">
						<button class="hot-products__pagination-btn hot-products__pagination-btn--first" disabled>
							«
						</button>
						<button class="hot-products__pagination-btn hot-products__pagination-btn--prev" disabled>
							‹
						</button>
						<div class="hot-products__pagination-pages">1 <?php _e( 'of', 'inpost-pay' ); ?> 1</div>
						<button class="hot-products__pagination-btn hot-products__pagination-btn--next">
							›
						</button>
						<button class="hot-products__pagination-btn hot-products__pagination-btn--last">
							»
						</button>
					</div>
				</div>
				<div class="hot-products__table-actions">
					<button type="button" class="hot-products__table-actions-products-back-btn inpost-btn inpost-btn--primary inpost-btn--large"><?php _e( 'Back', 'inpost-pay' ); ?></button>
					<button type="button" class="hot-products__table-actions-products-save-btn inpost-btn inpost-btn--primary-bg inpost-btn--large"><?php _e( 'Save', 'inpost-pay' ); ?></button>
				</div>
			</div>
			<div class="hot-products__table-wrapper hot-products__table-wrapper--added-products hot-products__table-wrapper--hidden">
				<div class="hot-products__table-container">
					<table class="hot-products__table hot-products__table--added-products">
						<thead>
							<tr>
								<th></th>
								<th><?php _e( 'Product', 'inpost-pay' ); ?></th>
								<th><?php _e( 'Product ID', 'inpost-pay' ); ?></th>
								<th><?php _e( 'Highlight period', 'inpost-pay' ); ?></th>
							</tr>
						</thead>
						<tbody>
						</tbody>
					</table>
					<div class="hot-products__rejected-products-info">
						<h3><?php _e( 'Products that could not be highlighted', 'inpost-pay' ); ?></h3>
						<p><?php _e( 'Products do not meet the requirements for distinction.', 'inpost-pay' ); ?></p>
					</div>
					<table class="hot-products__table hot-products__table--rejected-products">
						<thead>
							<tr>
								<th></th>
								<th><?php _e( 'Product', 'inpost-pay' ); ?></th>
								<th><?php _e( 'Product ID', 'inpost-pay' ); ?></th>
							</tr>
						</thead>
						<tbody>
						</tbody>
					</table>
				</div>
				<div class="hot-products__table-actions">
					<button type="button" class="hot-products__table-actions-added-products-save-btn inpost-btn inpost-btn--primary-bg inpost-btn--large"><?php _e( 'Close', 'inpost-pay' ); ?></button>
				</div>
			</div>
		</div>
	</div>
</div>

<div class="hot-products-confirmation">
	<div class="hot-products-confirmation__content">
		<img class="hot-products-confirmation__close" src="<?php echo plugin_dir_url( __FILE__ ) . '../../../assets/img/hp-close.svg'; ?>" alt="">
		<h3 class="hot-products-confirmation__title"><?php _e( 'Confirmation', 'inpost-pay' ); ?></h3>
		<p class="hot-products-confirmation__text"><?php _e( 'Do you want to save the selected products?', 'inpost-pay' ); ?></p>
		<div class="hot-products-confirmation__buttons">
			<button class="hot-products-confirmation__button hot-products-confirmation__button--cancel inpost-btn inpost-btn--primary inpost-btn--large">
				<?php _e( 'Cancel', 'inpost-pay' ); ?>
			</button>
			<button class="hot-products-confirmation__button hot-products-confirmation__button--confirm inpost-btn inpost-btn--primary-bg inpost-btn--large">
				<?php _e( 'Save', 'inpost-pay' ); ?>
			</button>
		</div>
	</div>
</div>
