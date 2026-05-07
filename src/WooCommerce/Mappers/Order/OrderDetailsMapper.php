<?php

namespace Ilabs\Inpost_Pay\WooCommerce\Mappers\Order;

use Ilabs\Inpost_Pay\EntityLayer\Entity\OrderAliasEntity;
use Ilabs\Inpost_Pay\EntityLayer\Repository\OrderAliasRepository;
use Ilabs\Inpost_Pay\Lib\item\order\OrderDetails;
use Ilabs\Inpost_Pay\Lib\item\Price;
use Ilabs\Inpost_Pay\Lib\helpers\HPOSHelper;
use Ilabs\Inpost_Pay\Lib\Analytics\Analytics;
use Ilabs\Inpost_Pay\Logger;
use Ilabs\Inpost_Pay\models\CartSessionService;
use function Ilabs\Inpost_Pay\inpost_pay_container;

class OrderDetailsMapper {
	private \WC_Order $order;
	private HPOSHelper $HPOSHelper;
	private $orderId;
	private array $priceTotals;

	private CartSessionService $cart_session;

	public function __construct( $order, HPOSHelper $HPOSHelper, $orderId, array $priceTotals ) {
		/**
		 * Get from container DI.
		 *
		 * @var CartSessionService $cart_session
		 */
		$this->cart_session = inpost_pay_container()->get( CartSessionService::SERVICE_KEY );
		$this->order        = $order;
		$this->HPOSHelper   = $HPOSHelper;
		$this->orderId      = $orderId;
	}

	public function map( array $delivery_price, array $delivery_options ): OrderDetails {
		$delivery_vat = $delivery_price['vat'];
		$orderDetails = new OrderDetails();

		$orderDetails->set_order_comments( $this->readComments() );
		$orderDetails->set_order_id( $this->orderId );
		$orderDetails->set_customer_order_id( $this->orderId );
		$orderDetails->set_pos_id( esc_attr( get_option( 'izi_pos_id' ) ) ?: '0' );

		$orderDetails->set_order_creation_date(
			date( 'Y-m-d\TH:i:s.000\Z', strtotime( $this->order->get_date_created() ) )
		);
		$orderDetails->set_order_update_date(
			date( 'Y-m-d\TH:i:s.000\Z', strtotime( $this->order->get_date_modified() ) )
		);
		$orderDetails->set_merchant_id( esc_attr( get_option( 'izi_client_id' ) ) );

		$status        = $this->order->get_status();
		$status_labels = get_option( 'izi_status_map' );

		$orderDetails->set_payment_status( $this->HPOSHelper->get_meta( 'izi_payment_status' ) );
		$orderDetails->set_order_status( $this->HPOSHelper->get_meta( 'izi_order_status' ) );

		$trackingNumber     = $this->HPOSHelper->get_meta( '_easypack_parcel_tracking' );
		$status_description = ( ! empty( $status_labels[ 'wc-' . $status ] ) )
			? $status_labels[ 'wc-' . $status ]
			: $status;

		$orderDetails->set_order_merchant_status_description( $status_description );

		$summary_order_promo_price = $this->readSummaryOrderPromoPrice();
		$orderDetails->set_order_base_price( $summary_order_promo_price );
		$orderDetails->set_order_discount( $this->readOrderDiscountTotal() );
		$orderDetails->set_order_final_price(
			$this->readSummaryOrderFinalPrice(
				$summary_order_promo_price,
				array(
					'delivery_price'   => $delivery_price,
					'delivery_options' => $delivery_options,
				)
			)
		);

		$basket_id = $this->cart_session->get_cart_id_by_order_id(
			$this->find_real_order_id_if_is_aliased( $this->orderId )
		);

		$orderDetails->set_basket_id( $basket_id );
		$orderDetails->set_delivery_references_list( array( $trackingNumber ) );
		$orderDetails->set_currency( $this->order->get_currency() );
		$orderDetails->set_payment_type( $this->readPaymentType() );
		$orderDetails->set_order_additional_parameters( $this->mapOrderAdditionalParameters() );

		return $orderDetails;
	}

	private function readComments(): string {
		return $this->order->get_customer_note();
	}

	private function readSummaryOrderPromoPrice(): Price {
		$price = new Price();

		$price->set_gross(
			number_format(
				$this->order->get_total() - $this->order->get_shipping_total() - $this->order->get_shipping_tax(),
				2,
				'.',
				''
			)
		);
		$price->set_net(
			$this->order->get_total() - $this->order->get_total_tax() - $this->order->get_shipping_total()
		);
		$price->set_vat(
			number_format(
				$this->order->get_total_tax() - $this->order->get_shipping_tax(),
				2,
				'.',
				''
			)
		);

		return $price;
	}

	private function readSummaryOrderFinalPrice( Price $order_base_price, array $delivery_data ): Price {
		$price = new Price();

		$delivery_price = $delivery_data['delivery_price'] ?? array();

		$gross = (float) $order_base_price->get_gross() + (float) ( $delivery_price['gross'] ?? 0 );
		$net   = (float) $order_base_price->get_net() + (float) ( $delivery_price['net'] ?? 0 );
		$vat   = (float) $order_base_price->get_vat() + (float) ( $delivery_price['vat'] ?? 0 );

		if ( ! empty( $delivery_data['delivery_options'] ) ) {
			Logger::log( '[DEBUG_SUMMARY] delivery_options: ' . var_export( $delivery_data['delivery_options'], true ) );
			foreach ( $delivery_data['delivery_options'] as $option ) {
				$gross += (float) ( $option['delivery_option_price']['gross'] ?? 0 );
				$net   += (float) ( $option['delivery_option_price']['net'] ?? 0 );
				$vat   += (float) ( $option['delivery_option_price']['vat'] ?? 0 );

				Logger::log( '[DEBUG_SUMMARY] Added option price: ' . var_export( $option['delivery_option_price'], true ) );
			}
		}

		$price->set_gross( number_format( $gross, 2, '.', '' ) );
		$price->set_net( $net );
		$price->set_vat( number_format( $vat, 2, '.', '' ) );

		Logger::log( '[DEBUG_SUMMARY] Final Price object: ' . var_export( $price, true ) );

		return $price;
	}

	private function readOrderDiscountTotal(): string {
		$discountTotal = (float) $this->order->get_discount_total( false ) + (float) $this->order->get_discount_tax( false );

		return number_format( $discountTotal, 2, '.', '' );
	}

	private function readPaymentType() {
		return $this->HPOSHelper->get_meta( 'izi_payment_type' );
	}

	private function mapOrderAdditionalParameters(): array {
		$order_additional_parameters  = array();
		$order_additional_parameters += ( new Analytics() )->get_as_order_additional_parameters( $this->order );

		return $order_additional_parameters;
	}

	/**
	 * Resolves the real WooCommerce order ID in cases where the provided ID
	 * is an alias created by the InPost Pay integration.
	 *
	 * If the given order ID matches an alias stored in the system,
	 * the method returns the underlying original order ID.
	 * Otherwise, it returns the ID unchanged, also null.
	 *
	 * @param int|string|null $order_id Potential aliased order ID.
	 *
	 * @return int|null Real order ID or null.
	 */
	private function find_real_order_id_if_is_aliased( $order_id ): ?int {
		/**
		 * Get repository from DI container.
		 *
		 * @var OrderAliasRepository $alias_repo
		 */
		$alias_repo = inpost_pay_container()->get( OrderAliasRepository::SERVICE_KEY );

		/**
		 * Order alias entity.
		 *
		 * @var OrderAliasEntity $order_alias_entity
		 */
		$order_alias_entity = $alias_repo->find_by_alias( $order_id );

		return $order_alias_entity ? $order_alias_entity->get_order_id() : (int) $order_id;
	}
}
