<?php
/**
 * Hot products collection item.
 *
 * @package Ilabs\Inpost_Pay\Lib\item
 */

declare( strict_types=1 );

namespace Ilabs\Inpost_Pay\Lib\item;

use Ilabs\Inpost_Pay\Lib\Item;
use Ilabs\Inpost_Pay\Lib\helpers\JsonSerializationHelper;
use JsonSerializable;

/**
 * Represents a paginated collection of hot products.
 */
class HotProducts extends Item implements JsonSerializable {

	use JsonSerializationHelper;

	protected int $page_size   = 10;
	protected int $total_items = 0;
	protected int $page_index  = 1;
	protected array $content   = array();

	/**
	 * Serializes object to JSON-compatible array.
	 *
	 * @return array
	 */
	public function jsonSerialize(): array {
		return $this->auto_serialize();
	}

	/**
	 * Returns page size.
	 *
	 * @return int
	 */
	public function get_page_size(): int {
		return $this->page_size;
	}

	/**
	 * Sets page size.
	 *
	 * @param int $page_size Page size.
	 *
	 * @return self
	 */
	public function set_page_size( int $page_size ): self {
		$this->page_size = $page_size;

		return $this;
	}

	/**
	 * Returns total items count.
	 *
	 * @return int
	 */
	public function get_total_items(): int {
		return $this->total_items;
	}

	/**
	 * Sets total items count.
	 *
	 * @param int $total_items Total items count.
	 *
	 * @return self
	 */
	public function set_total_items( int $total_items ): self {
		$this->total_items = $total_items;

		return $this;
	}

	/**
	 * Returns current page index.
	 *
	 * @return int
	 */
	public function get_page_index(): int {
		return $this->page_index;
	}

	/**
	 * Sets current page index.
	 *
	 * @param int $page_index Page index.
	 *
	 * @return self
	 */
	public function set_page_index( int $page_index ): self {
		$this->page_index = $page_index;

		return $this;
	}

	/**
	 * Returns page content.
	 *
	 * @return array
	 */
	public function get_content(): array {
		return $this->content;
	}

	/**
	 * Sets page content.
	 *
	 * @param array $content Page content.
	 *
	 * @return self
	 */
	public function set_content( array $content ): self {
		$this->content = $content;

		return $this;
	}
}
