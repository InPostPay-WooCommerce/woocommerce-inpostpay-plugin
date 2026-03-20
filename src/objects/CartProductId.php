<?php

namespace Ilabs\Inpost_Pay\objects;

class CartProductId {

	private ?int $id = null;

	private ?string $key = null;

	public function __construct( string $cartId ) {
		if ( strpos( $cartId, ':' ) !== false ) {
			list( $this->id, $this->key ) = explode( ':', $cartId );
		} else {
			$this->id = (int) $cartId;
		}
	}

	public function getId(): ?int {
		return $this->id;
	}

	public function getKey(): ?string {
		return $this->key;
	}

	public function hasKey(): bool {
		return $this->key !== null;
	}
}
