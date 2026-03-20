<?php

namespace Ilabs\Inpost_Pay\WooCommerce\Mappers\Basket;

use Ilabs\Inpost_Pay\Lib\helpers\Woo_Commerce_Session_Helper;
use Ilabs\Inpost_Pay\Logger;
use Ilabs\Inpost_Pay\WooCommerce\Price\BasketPriceCalculator;
use Ilabs\Inpost_Pay\WooCommerce\WooCommerceBasket;
use Ilabs\Inpost_Pay\Lib\config\payment\PaymentMethodsInterface;
use Ilabs\Inpost_Pay\Lib\config\payment\PaymentMethodsOptions;
use Ilabs\Inpost_Pay\Lib\helpers\CookieHelper;
use Ilabs\Inpost_Pay\Lib\item\Summary;

class SummaryMapper {
	private BasketPriceCalculator $priceCalculator;

	public function __construct(BasketPriceCalculator $priceCalculator) {
		$this->priceCalculator = $priceCalculator;
	}

	/**
	 * Maps basket summary data
	 *
	 * @return Summary The mapped summary
	 */
	public function mapSummary(): Summary {
		$summary = new Summary();

		$summary->set_basket_base_price( $this->priceCalculator->readSummaryBasketBasePrice() );
		$summary->set_basket_promo_price( $this->priceCalculator->readSummaryBasketPromoPrice() );
		$summary->set_basket_final_price( $this->priceCalculator->readSummaryBasketFinalPrice( $this->priceCalculator->readSummaryBasketPromoPrice() ) );
		$summary->set_currency( get_woocommerce_currency() );
		$summary->set_basket_expiration_date( $this->readBasketExpirationDate() );
		$summary->set_basket_additional_information( '' );
		$summary->set_payment_type( $this->readPaymentType() );

		// Add notice if coupons are applied
		if ( WooCommerceBasket::$hasCoupons ) {
			if ( WooCommerceBasket::$couponError ) {
				$summary->basket_notice = [
					'type'        => 'ERROR',
					'description' => 'Kod jest nieaktywny lub nieprawidłowy',
				];
			} else {
				$summary->basket_notice = [
					'type'        => 'ATTENTION',
					'description' => 'Kod został aktywowany',
				];
			}
		}

		return $summary;
	}

	/**
	 * Reads the basket expiration date
	 *
	 * @return string The basket expiration date
	 */
	public function readBasketExpirationDate(): string {
		return Woo_Commerce_Session_Helper::get_session_expiation_date();
	}

	/**
	 * Reads the payment type options
	 *
	 * @return array The payment type options
	 */
	public function readPaymentType(): array {
		$methods = [];

		if ( (int) esc_attr( get_option( 'izi_payment_aion', 1 ) ) ) {
			$methods = ( new PaymentMethodsOptions() )->get_payment_methods();
			if ( ! is_array( $methods ) && count( $methods ) === 0 ) {
				$methods = PaymentMethodsInterface::IZI_PAYMENT_METHODS;
			}
		}

		$methods                = (array) $methods;
		$payment_inpost_enabled = (int) esc_attr( get_option( 'izi_payment_inpost' ) );

		if ( $payment_inpost_enabled && ! in_array( 'CASH_ON_DELIVERY', $methods, true ) ) {
			$methods[] = 'CASH_ON_DELIVERY';
		}

		return $methods;
	}
}
