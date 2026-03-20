<?php
use Ilabs\Inpost_Pay\Lib\config\product\HotProductsConfig;
$locale = get_locale();
$lang = strpos($locale, 'pl') === 0 ? 'pl' : (strpos($locale, 'en') === 0 ? 'en' : 'pl');
?>



<div class="unavailable-wrapper" data-lang="<?php echo esc_attr($lang); ?>" data-plugin-url="<?php echo plugin_dir_url( __FILE__ ); ?>" data-api-error-title="<?php _e( 'An error occurred', 'inpost-pay' ); ?>" data-api-error-message="<?php _e( 'Error while loading', 'inpost-pay' ); ?>" data-no-results-message="<?php _e( 'No results found', 'inpost-pay' ); ?>" data-products-limit-reached="<?php echo _e( 'Hot products limit reached', 'inpost-pay' ); ?>" data-pagination-text="<?php _e( 'of', 'inpost-pay' ); ?>" data-accepted-text="<?php _e( 'Accepted', 'inpost-pay' ); ?>" data-rejected-text="<?php _e( 'Rejected', 'inpost-pay' ); ?>" data-remove-text="<?php _e( 'Remove', 'inpost-pay' ); ?>" data-highlight-period-not-selected="<?php _e( 'Not selected', 'inpost-pay' ); ?>" data-availability-set-text="<?php _e( 'Highlight period set', 'inpost-pay' ); ?>" data-api-success-title="<?php _e( 'Success', 'inpost-pay' ); ?>" data-courier-text="<?php _e( 'Exclude courier', 'inpost-pay' ); ?>" data-parcel-locker-text="<?php _e( 'Exclude parcel locker', 'inpost-pay' ); ?>">
    <div class="overlay"></div>
    <div class="unavailable">
        <div class="unavailable__header">
            <div class="unavailable__heading-wrapper">
                <h2 class="unavailable__title"><?php _e( 'Unavailable categories', 'inpost-pay' ); ?></h2>
            </div>
            <div class="unavailable__actions unavailable__actions--end">
                <button class="unavailable__remove-all inpost-btn inpost-btn--remove" style="display: none;">
                    <img src="<?php echo plugin_dir_url( __FILE__ ) . "../../../assets/img/hp-remove.svg"; ?>" alt="">
                    <span><?php _e( 'Remove All', 'inpost-pay' ); ?></span>
                </button>
            </div>
        </div>
        <div class="unavailable__search-wrapper">
            <div class="unavailable__search-input-wrapper">
                <input type="text" class="unavailable__search-main" placeholder="<?php _e( 'Search in the list', 'inpost-pay' ); ?>">
                <img class="unavailable__search-icon" src="<?php echo plugin_dir_url( __FILE__ ) . "../../../assets/img/hp-search.svg"; ?>" alt="">
            </div>
        </div>
        <div class="unavailable__table-container">
            <table class="unavailable__table unavailable__table--unavailable">
                <thead>
                    <tr>
                        <th></th>
                        <th><?php _e( 'Category', 'inpost-pay' ); ?></th>
                        <th><?php _e( 'Category ID', 'inpost-pay' ); ?></th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
        <div class="unavailable__pagination" id="unavailable-pagination">
            <div class="unavailable__pagination-info">
                <span class="unavailable__pagination-total-items">0</span> <?php _e( 'results', 'inpost-pay' ); ?>
            </div>
            <div class="unavailable__pagination-buttons">
                <button class="unavailable__pagination-btn unavailable__pagination-btn--first" disabled>
                    «
                </button>
                <button class="unavailable__pagination-btn unavailable__pagination-btn--prev" disabled>
                    ‹
                </button>
                <div class="unavailable__pagination-pages">1 <?php _e( 'of', 'inpost-pay' ); ?> 1</div>
                <button class="unavailable__pagination-btn unavailable__pagination-btn--next">
                    ›
                </button>
                <button class="unavailable__pagination-btn unavailable__pagination-btn--last">
                    »
                </button>
            </div>
        </div>
    </div>
</div>

<div class="unavailable-confirmation">
    <div class="unavailable-confirmation__content">
        <img class="unavailable-confirmation__close" src="<?php echo plugin_dir_url( __FILE__ ) . "../../../assets/img/hp-close.svg"; ?>" alt="">
        <h3 class="unavailable-confirmation__title"><?php _e('Confirmation', 'inpost-pay'); ?></h3>
        <p class="unavailable-confirmation__text"><?php _e('Do you want to save the selected products?', 'inpost-pay'); ?></p>
        <div class="unavailable-confirmation__buttons">
            <button class="unavailable-confirmation__button unavailable-confirmation__button--cancel inpost-btn inpost-btn--primary inpost-btn--large">
                <?php _e('Cancel', 'inpost-pay'); ?>
            </button>
            <button class="unavailable-confirmation__button unavailable-confirmation__button--confirm inpost-btn inpost-btn--primary-bg inpost-btn--large">
                <?php _e('Save', 'inpost-pay'); ?>
            </button>
        </div>
    </div>
</div>
