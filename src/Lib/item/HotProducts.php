<?php

declare( strict_types=1 );

namespace Ilabs\Inpost_Pay\Lib\item;

use Ilabs\Inpost_Pay\Lib\Item;
use Ilabs\Inpost_Pay\Lib\helpers\JsonSerializationHelper;
use JsonSerializable;

class HotProducts extends Item implements JsonSerializable {

	use JsonSerializationHelper;

	protected int $page_size   = 10;
	protected int $total_items = 0;
	protected int $page_index  = 1;
	protected array $content   = array();

	public function jsonSerialize(): array {
		return $this->autoSerialize();
	}

	public function get_page_size(): int {
		return $this->page_size;
	}

	public function set_page_size( int $page_size ): self {
		$this->page_size = $page_size;

		return $this;
	}

	public function get_total_items(): int {
		return $this->total_items;
	}

	public function set_total_items( int $total_items ): self {
		$this->total_items = $total_items;

		return $this;
	}

	public function get_page_index(): int {
		return $this->page_index;
	}

	public function set_page_index( int $page_index ): self {
		$this->page_index = $page_index;

		return $this;
	}

	public function get_content(): array {
		return $this->content;
	}

	public function set_content( array $content ): self {
		$this->content = $content;

		return $this;
	}
}
