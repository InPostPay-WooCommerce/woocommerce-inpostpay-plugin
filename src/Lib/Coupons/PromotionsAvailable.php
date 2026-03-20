<?php

namespace Ilabs\Inpost_Pay\Lib\Coupons;

use Ilabs\Inpost_Pay\EntityLayer\Repository\CouponRepository;
use Ilabs\Inpost_Pay\Lib\helpers\DateHelper;
use Ilabs\Inpost_Pay\Lib\helpers\WooProductHelper;
use WC_Coupon;
use function Ilabs\Inpost_Pay\inpost_pay_container;

class PromotionsAvailable {

	private const FETCH_LIMIT = 500;
	private const RETURN_LIMIT = 5;

	public function get_coupons(): array {
		/**
		 * Get from DI container.
		 *
		 * @var CouponRepository $coupon_repo
		 */
		$coupon_repo     = inpost_pay_container()->get( CouponRepository::SERVICE_KEY );
		$visible_coupons = $coupon_repo->find_visible_in_app( self::FETCH_LIMIT );
		$coupon_ids      = array_map( static fn( $coupon ) => $coupon->get_id(), $visible_coupons );

		$available_coupons = array();

		if ( ! empty( $coupon_ids ) ) {

			$applied_coupons = WC()->cart->get_applied_coupons() ?: array();
			$cart_subtotal   = WC()->cart->get_subtotal();
			$cart_item       = WC()->cart->get_cart();
			$now             = current_time( 'timestamp' );
			$products        = array();

			if ( ! empty( $cart_item ) ) {
				$product_ids = array_filter(
					array_unique(
						array_map(
							static fn( $item ) => (int) ( $item['variation_id'] ?: $item['product_id'] ),
							$cart_item
						)
					)
				);

				if ( $product_ids ) {
					/**
					 * Get from container DI.
					 *
					 * @var WooProductHelper $product_helper
					 */
					$product_helper = inpost_pay_container()->get( WooProductHelper::SERVICE_KEY );
					$products       = $product_helper->load_products_safe( $product_ids );
					$products       = array_filter( $products );
				}
			}

			foreach ( $coupon_ids as $coupon_id ) {
				if ( count( $available_coupons ) >= self::RETURN_LIMIT ) {
					break;
				}

				$coupon         = new WC_Coupon( $coupon_id );
				$date_expire    = $this->get_date_expire( $coupon );
				$coupon_message = '';

				// Skip coupon if it has expired.
				if ( '' !== $date_expire && $now > $date_expire ) {
					continue;
				}

				// Skip coupon if it applied in cart.
				if ( in_array( $coupon->get_code(), $applied_coupons, true ) ) {
					continue;
				}

				// Check coupons have limitation to user.
				$restrictions = $coupon->get_email_restrictions();
				if ( is_array( $restrictions ) && 0 < count( $restrictions ) ) {
					$current_user = wp_get_current_user();
					$user_email   = $current_user && $current_user->exists() ? $current_user->user_email : '';

					if ( empty( $user_email ) || ! in_array( $user_email, $restrictions, true ) ) {
						continue;
					}
				}

				// Skip coupon if products in cart not fit with usage restriction.
				if ( ! empty( $products ) ) {
					$continue = false;


					if ( $coupon->get_exclude_sale_items() ) {
						if ( ! $coupon->is_type( wc_get_product_coupon_types() ) ) {
							foreach ( $products as $product ) {
								if ( $product->is_on_sale() ) {
									continue 2;
								}
							}
						} else {
							$non_sale_found = false;

							foreach ( $products as $product ) {
								if ( ! $product->is_on_sale() ) {
									$non_sale_found = true;
									break;
								}
							}

							if ( ! $non_sale_found ) {
								continue;
							}
						}
					}

					if ( count( $coupon->get_excluded_product_ids() ) > 0 ) {
						foreach ( $products as $product ) {
							if ( in_array(
								     $product->get_id(),
								     $coupon->get_excluded_product_ids(),
								     true
							     ) || in_array(
								     $product->get_parent_id(),
								     $coupon->get_excluded_product_ids(),
								     true
							     ) ) {
								$continue = true;
								break;
							}
						}

						if ( $continue ) {
							continue;
						}
					}

					if ( count( $coupon->get_excluded_product_categories() ) > 0 ) {
						$has_excluded = false;

						foreach ( $products as $product ) {
							$product_cats = wc_get_product_cat_ids(
								$product->is_type( 'variation' ) ? $product->get_parent_id() : $product->get_id()
							);

							if ( count( array_intersect( $product_cats, $coupon->get_excluded_product_categories() ) ) > 0 ) {
								$has_excluded = true;
								break;
							}
						}

						if ( $has_excluded ) {
							if ( ! $coupon->is_type( wc_get_product_coupon_types() ) ) {
								continue;
							}

							$has_valid = false;
							foreach ( $products as $product ) {
								$product_cats = wc_get_product_cat_ids(
									$product->is_type( 'variation' ) ? $product->get_parent_id() : $product->get_id()
								);

								if ( count( array_intersect( $product_cats, $coupon->get_excluded_product_categories() ) ) === 0 ) {
									$has_valid = true;
									break;
								}
							}

							if ( ! $has_valid ) {
								continue;
							}
						}
					}

					if ( count( $coupon->get_product_ids() ) > 0 ) {
						$cart_product_ids = array();

						foreach ( $products as $product ) {
							$cart_product_ids[] = $product->get_id();

							$parent_id = $product->get_parent_id();
							if ( $parent_id && $parent_id > 0 ) {
								$cart_product_ids[] = $parent_id;
							}
						}

						$eligible_products = array_intersect( $cart_product_ids, $coupon->get_product_ids() );

						if ( empty( $eligible_products ) ) {
							continue;
						}
					}

					if ( $coupon->is_type( wc_get_product_coupon_types() ) ) {
						$product_ids = $coupon->get_product_ids();

						if ( ! empty( $product_ids ) ) {
							$valid_for_any_product = false;

							foreach ( $products as $product ) {
								if ( $coupon->is_valid_for_product( $product ) ) {
									$valid_for_any_product = true;
									break;
								}
							}

							if ( ! $valid_for_any_product ) {
								continue;
							}
						} else {
							$valid_for_any_product = false;

							foreach ( $products as $product ) {
								if ( $coupon->is_valid_for_product( $product ) ) {
									$valid_for_any_product = true;
									break;
								}
							}

							if ( ! $valid_for_any_product ) {
								continue;
							}
						}
					}

					$included_brand_ids = $coupon->get_meta( 'product_brands' );
					$excluded_brand_ids = $coupon->get_meta( 'exclude_product_brands' );

					$included_brand_ids = is_array( $included_brand_ids )
						? array_map( 'absint', $included_brand_ids )
						: array();
					$excluded_brand_ids = is_array( $excluded_brand_ids )
						? array_map( 'absint', $excluded_brand_ids )
						: array();

					if ( ! empty( $excluded_brand_ids ) ) {
						$has_excluded_brand = false;

						foreach ( $products as $product ) {
							$brand_ids = wp_get_post_terms( $product->get_id(), 'product_brand', array( 'fields' => 'ids' ) );

							if ( count( array_intersect( $brand_ids, $excluded_brand_ids ) ) > 0 ) {
								$has_excluded_brand = true;
								break;
							}
						}

						if ( $has_excluded_brand && ! $coupon->is_type( wc_get_product_coupon_types() ) ) {
							continue;
						}
					}

					if ( ! empty( $included_brand_ids ) ) {
						$has_valid_brand = false;

						foreach ( $products as $product ) {
							$brand_ids = wp_get_post_terms( $product->get_id(), 'product_brand', array( 'fields' => 'ids' ) );

							if ( count( array_intersect( $brand_ids, $included_brand_ids ) ) > 0 ) {
								$has_valid_brand = true;
								break;
							}
						}

						if ( ! $has_valid_brand ) {
							continue;
						}
					}

					if ( ! empty( $product_ids ) ) {
						$valid_via_filter = false;

						foreach ( $products as $product ) {
							$is_valid = apply_filters(
								'woocommerce_coupon_is_valid_for_product',
								true,
								$product,
								$coupon,
								array()
							);

							if ( $is_valid ) {
								$valid_via_filter = true;
								break;
							}
						}

						if ( ! $valid_via_filter ) {
							continue;
						}
					} elseif ( empty( $included_brand_ids ) && empty( $excluded_brand_ids ) ) {
						$all_valid_via_filter = true;

						foreach ( $products as $product ) {
							$is_valid = apply_filters(
								'woocommerce_coupon_is_valid_for_product',
								true,
								$product,
								$coupon,
								array()
							);

							if ( ! $is_valid ) {
								$all_valid_via_filter = false;
								break;
							}
						}

						if ( ! $all_valid_via_filter ) {
							continue;
						}
					}
				}

				$minimum_amount = $coupon->get_minimum_amount();
				$maximum_amount = $coupon->get_maximum_amount();

				// Disable coupon if cart subtotal spent lest than minimum amount required.
				if ( $minimum_amount > 0 && apply_filters(
						'woocommerce_coupon_validate_minimum_amount',
						$minimum_amount > $cart_subtotal,
						$coupon,
						$cart_subtotal
					) ) {
					continue;
				}

				if ( $maximum_amount !== '' && apply_filters(
						'woocommerce_coupon_validate_maximum_amount',
						$maximum_amount < $cart_subtotal,
						$coupon
					) ) {
					continue;
				}

				if ( strip_tags( $coupon->get_description() ) === '' ) {
					continue;
				}

				$available_coupons[] = $coupon_id;
			}
		}

		if ( empty( $available_coupons ) ) {
			return array();
		}

		$final_coupon_ids = array_slice( $available_coupons, 0, self::RETURN_LIMIT );

		$meta_rows      = $coupon_repo->find_meta_for_coupons( $final_coupon_ids );
		$meta_by_coupon = array();
		foreach ( $meta_rows as $row ) {
			$meta_by_coupon[ $row->post_id ][ $row->meta_key ] = $row->meta_value;
		}

		$result = array();

		foreach ( $final_coupon_ids as $coupon_id ) {
			$coupon = new WC_Coupon( $coupon_id );

			if ( ! empty( $meta_by_coupon[ $coupon_id ] ) ) {
				foreach ( $meta_by_coupon[ $coupon_id ] as $key => $value ) {
					if ( in_array( $key, ['_used_by', '_usage_limit', '_usage_limit_per_user'], true ) ) {
						continue;
					}

					$coupon->update_meta_data( $key, $value );
				}
			}

			$promotions_available = new \Ilabs\Inpost_Pay\Lib\item\PromotionsAvailable();

			$promotions_available->set_promo_code_value( $coupon->get_code() );
			$promotions_available->set_description( strip_tags( $coupon->get_description() ) );
			$promotions_available->set_type( $coupon->get_discount_type() );
			$promotions_available->set_start_date(
				$coupon->get_date_created() ?
					gmdate( DateHelper::DATE_API_FORMAT, $coupon->get_date_created()->getTimestamp() ) : ''
			);
			$promotions_available->set_end_date(
				$coupon->get_date_expires() ?
					gmdate( DateHelper::DATE_API_FORMAT, $coupon->get_date_expires()->getTimestamp() ) : ''
			);

			$url = $meta_by_coupon[ $coupon_id ][ Coupon::META_PROMOTION_URL ]
			       ?? $coupon->get_meta( Coupon::META_PROMOTION_URL );

			if ( $url === '' ) {
				$url = get_permalink( wc_get_page_id( 'shop' ) );
			}

			$promotions_available->details->set_link( $url );

			$result[] = $promotions_available;
		}

		return $result;
	}

	private function get_date_expire( WC_Coupon $coupon ): ?string {
		if ( $date_expire_edit = $coupon->get_date_expires( 'edit' ) ) {
			return $date_expire_edit->date( 'Y-m-d' );
		}

		return '';
	}
}
