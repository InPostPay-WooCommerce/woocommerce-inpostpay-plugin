<?php

declare(strict_types=1);

namespace Ilabs\Inpost_Pay\Lib\interfaces;

use Ilabs\Inpost_Pay\EntityLayer\Entity\CartSessionEntity;

interface CartSessionInterface {

	public function store_current(): void;

	public function set_session_by_cart_id( string $cart_id ): void;

	public function delete_by_cart_id( string $cart_id ): void;

	public function get_redirected_by_id( string $cart_id ): ?int;

	public function set_redirected_by_id( string $cart_id, int $value ): void;

	public function set_order_to_cart( string $cart_id, int $order_id, string $redirect_url ): void;

	public function get_cart_id_by_order_id( $order_id ): ?string;

	public function set_confirmation_to_cart( string $cart_id, ?string $confirmation ): void;

	public function get_cart_order_redirect_url( string $cart_id ): ?string;

	public function get_cart_confirmation( string $cart_id ): ?string;

	public function initiate_wc_cart(): void;

	public function set_cart_cache_by_id( string $cart_id, string $data ): void;

	public function get_cart_cache_by_id( string $cart_id ): ?string;

	public function set_wc_cart_snapshot( string $cart_id ): void;

	public function get_wc_cart_snapshot( string $cart_id ): ?string;

	public function get_object_by_id( string $cart_id ): ?CartSessionEntity;

	public function set_cart_coupons_by_id( string $cart_id, string $data ): void;

	public function set_cart_delivery_cache_by_id( string $cart_id, array $data ): void;

	public function get_cart_delivery_cache_by_id( string $cart_id ): array;

	public function get_wc_cart_session( string $cart_id ): ?string;

	public function set_action_by_id( string $cart_id, string $data ): void;

	public function get_session_id( string $cart_id ): array;

	public function basket_binding_api_key( string $cart_id ): ?string;

	public function set_basket_binding_api_key( string $cart_id, string $basket_binding_api_key ): void;

	public function get_entity_by_cart_id( string $cart_id ): ?CartSessionEntity;

	public function get_session_by_wc_session_id( string $session_id ): ?string;

	public function get_analytics( string $cart_id ): array;

	public function store_analytics( string $cart_id, array $data ): void;

	public function get_order_id_by_cart_id( string $cart_id ): ?int;

	public function remove_basket_binding_api_key( string $cart_id ): void;

	public function should_redirect( string $cart_id ): bool;

	public function get_redirect_url_for_template( string $cart_id ): ?string;

	public function reset_after_order( string $cart_id ): void;
}
