<?php

namespace Ilabs\Inpost_Pay\Lib\Transformers;

use Ilabs\Inpost_Pay\Integration\Basket\Quantity\QuantityIntegrationFactory;
use Ilabs\Inpost_Pay\Lib\helpers\EANHelper;
use Ilabs\Inpost_Pay\Lib\interfaces\ProductInterface;
use Ilabs\Inpost_Pay\Lib\item\AbstractProduct;
use Ilabs\Inpost_Pay\Lib\item\Price;
use Ilabs\Inpost_Pay\Lib\item\Product;
use Ilabs\Inpost_Pay\Lib\item\ProductAttribute;
use Ilabs\Inpost_Pay\Lib\item\Quantity;
use Ilabs\Inpost_Pay\Lib\item\RelatedProduct;
use Ilabs\Inpost_Pay\Lib\item\Variant;
use Ilabs\Inpost_Pay\Logger;
use Ilabs\Inpost_Pay\SettingsPage;
use stdClass;
use WC_Product;

class ProductTransformer {


	protected WC_Product $wc_product;
	private array $parent_cache = [];

	public function __construct( WC_Product $product ) {
		$this->wc_product = $product;
	}

	public function readProductBasePrice(): Price {
		$price = new Price();

		$priceIncludingTax = wc_get_price_including_tax( $this->wc_product, [ 'price' => $this->wc_product->get_regular_price() ] );
		$priceExcludingTax = wc_get_price_excluding_tax( $this->wc_product, [ 'price' => $this->wc_product->get_regular_price() ] );
		$vat               = $priceIncludingTax - $priceExcludingTax;

		$price->set_gross( number_format( $priceIncludingTax, 2, '.', '' ) );
		$price->set_net( $priceExcludingTax );
		$price->set_vat( number_format( $vat, 2, '.', '' ) );

		return $price;
	}

	public function readProductPromoPrice(): Price {
		$price = new Price();

		$priceIncludingTax = wc_get_price_including_tax( $this->wc_product, [ 'price' => $this->wc_product->get_sale_price() ] );
		$priceExcludingTax = wc_get_price_excluding_tax( $this->wc_product, [ 'price' => $this->wc_product->get_sale_price() ] );
		$vat               = $priceIncludingTax - $priceExcludingTax;

		$price->set_gross( number_format( $priceIncludingTax, 2, '.', '' ) );
		$price->set_net( $priceExcludingTax );
		$price->set_vat( number_format( $vat, 2, '.', '' ) );

		return $price;
	}

	public function mapProductData( bool $isRelatedProduct = false ): ProductInterface {

		if ( $isRelatedProduct ) {
			$product = new RelatedProduct();
		} else {
			$product = new Product();
		}

		$product->set_product_id( $this->getProductIdentifier() );
		if ( isset( $this->wc_product->get_category_ids()[0] ) ) {
			$product->set_product_category( $this->wc_product->get_category_ids()[0] );
		}

		$product->set_ean( EANHelper::prepareEan( $this->wc_product ) );
		$product->set_product_name( strip_tags( html_entity_decode( $this->wc_product->get_name() ) ) );
		$product->set_product_description( $this->getDescription() );
		$product->set_product_link( $this->wc_product->get_permalink() );
		$product->set_product_image( $this->getThumbnailImage() );


		$product->set_additional_product_images( $this->getProductImages() );
		$product->set_product_attributes( $this->mapProductAttributes() );
		if ( $this->isProductVirtualOrDigital() ) {
			$product->set_product_type( AbstractProduct::TYPE_DIGITAL );
		}

		return $product;
	}

	protected function getThumbnailImage(): string {
		$image = wp_get_attachment_image_src( get_post_thumbnail_id( $this->wc_product->get_id() ), 'single-post-thumbnail' );
		if ( ! $image && $this->wc_product->get_parent_id() ) {
			$image = wp_get_attachment_image_src( get_post_thumbnail_id( $this->wc_product->get_parent_id() ), 'single-post-thumbnail' );
		}

		if ( $image && $image[0] ) {
			return str_replace( 'http://', 'https://', $image[0] );
		}

		return '';
	}

	protected function getDescription(): string {
		$description = $this->formatProductDescription( $this->getDescriptionByWcProduct() );

		if ( ! $description && $this->wc_product->get_parent_id() ) {
			$parent = $this->getParentProduct();
			if ( $parent ) {
				$description = $this->formatProductDescription( $this->getDescriptionByWcProduct() );
			}
		}

		return $description;
	}

	public function getProductImages(
		bool $should_include_gallery = false,
		array $gallery_image_ids = []
	): array {
		$images = [];
		if ( ! $should_include_gallery ) {
			Logger::log( '[HOT_PRODUCT_DEBUG] Gallery skipped; should_include_gallery=' . var_export( $should_include_gallery, true ) . ')' );

			return [];
		}

		$gallery_image_ids = $gallery_image_ids ?: $this->wc_product->get_gallery_image_ids();

		if ( ! empty( $gallery_image_ids ) ) {
			foreach ( $gallery_image_ids as $gallery_image_id ) {
				$gallery_image_small  = wp_get_attachment_image_src( $gallery_image_id, 'thumbnail' );
				$gallery_image_normal = wp_get_attachment_image_src( $gallery_image_id, 'full' );

				if (
					$gallery_image_small && ! empty( $gallery_image_small[0] ) &&
					$gallery_image_normal && ! empty( $gallery_image_normal[0] )
				) {
					$image              = new \stdClass();
					$image->small_size  = str_replace( 'http://', 'https://', $gallery_image_small[0] );
					$image->normal_size = str_replace( 'http://', 'https://', $gallery_image_normal[0] );
					$images[]           = $image;
				}
			}
		}

		Logger::log( '[HOT_PRODUCT_DEBUG] Gallery count for product #' . $this->wc_product->get_id() . ': ' . count( $images ) );

		return array_slice( $images, 0, 10 );
	}


	protected function formatProductDescription( ?string $description
	): string {
		if ( empty( $description ) ) {
			return '';
		}

		$description = $this->removeUnregisteredShortcodes( $description );
		$description = do_shortcode( $description );

		for ( $i = 1; $i <= 6; $i ++ ) {
			$description = str_replace( '<h' . $i . '>',
				"\r\n<h" . $i . '>',
				$description );
		}

		$description = $this->replaceLiteralNewlines( $description );
		$description = trim( $description );

		return strip_tags( $description );
	}

	private function removeUnregisteredShortcodes( $content ): string {
		global $shortcode_tags;

		$matches_block = [];

		// find block shortcodes
		preg_match_all( '/[\/?\w+[^\]]*].*[\/?\w+[^\]]*]/',
			$content,
			$matches_block,
			PREG_SET_ORDER );


		$to_remove_blocks = [];
		foreach ( $matches_block as $match ) {
			$pattern = '/\[([\w_]+)].*?\[\/\1]/';

			// Use preg_match to find the shortcode
			if ( isset( $match[1] ) && preg_match( $pattern, $match[1], $matches ) ) {
				// Return the ID (which is the first capturing group)

				if ( isset( $matches[1] ) && ! isset( $shortcode_tags[ $matches[1] ] ) ) {
					$to_remove_blocks[] = $matches[1];
				}
			}
		}

		if ( ! empty( $to_remove_blocks ) ) {
			$pattern = get_shortcode_regex( $to_remove_blocks );
			$content = preg_replace_callback( "/$pattern/",
				'strip_shortcode_tag',
				$content );
		}

		// find inline shortcodes
		preg_match_all( '/\[(\w+).*]/',
			$content,
			$matches_inline,
			PREG_SET_ORDER );


		$to_remove_inline = [];
		foreach ( $matches_inline as $match ) {
			if ( isset( $match[1] ) && ! isset( $shortcode_tags[ $match[1] ] ) ) {
				$to_remove_inline[] = $match[1];
			}
		}

		if ( ! empty( $to_remove_inline ) ) {
			$pattern = get_shortcode_regex( $to_remove_inline );
			$content = preg_replace_callback( "/$pattern/",
				'strip_shortcode_tag',
				$content );
		}

		if ( ! is_string( $content ) ) {
			$content = '';
		}

		return $content;
	}

	private function replaceLiteralNewlines( $input ): string {
		$output = preg_replace( '/\\\\n/', "\r\n", $input );

		return is_string( $output ) ? $output : '';
	}

	protected function getDescriptionByWcProduct(): string {

		if ( SettingsPage::OPT_DROPDOWN_ID_SHORT_PRODUCT_DESC_MAP === get_option( SettingsPage::OPT_KEY_PRODUCT_DESC_MAP,
				SettingsPage::OPT_DROPDOWN_ID_DEFAULT_PRODUCT_DESC_MAP ) ) {
			return $this->wc_product->get_short_description();
		}
		$description = $this->wc_product->get_description();

		if ( function_exists( 'has_blocks' ) && has_blocks( $description ) ) {
			$description = $this->parseGutenbergBlocks( $description );
		}

		return '' === $description ? $this->wc_product->get_short_description() : $description;
	}

	public function mapProductVariables(): array {
		$array = [];
		if ( $parent = $this->getParentProduct() ) {
			$this->wc_product = $parent;
		}

		foreach ( $this->wc_product->get_attributes() as $attribute ) {
			if ( $attribute->get_visible() && $attribute->get_variation() === true ) {
				$array[] = $this->mapProductVariable( $attribute );
			}
		}

		return $array;
	}

	public function mapProductVariable( $attribute ): Variant {
		$variant = new Variant();

		$variant->set_variant_id( (int) $attribute->get_id() );
		$variant->set_variant_name( wc_attribute_label( $attribute->get_name() ) ?: $attribute->get_name() );
		$variant->set_variant_values( implode( ', ', $attribute->get_options() ) );

		$variant->set_variant_description( '' );
		$variant->set_variant_type( '' );

		return $variant;
	}

	public function mapProductAttributes(): array {
		$array = [];

		$attributes       = $this->wc_product->get_attributes();
		$attributes_array = [];

		foreach ( $attributes as $attribute ) {
			if ( is_string( $attribute ) && strlen( $attribute ) > 0 ) {
				$attributes_array['Atrybut'] = $attribute;
				Logger::debug( '[ProductTransformer] Attribute mapped as string: ' . $attribute );
				continue;
			}

			if ( ! $attribute instanceof \WC_Product_Attribute ) {
				Logger::debug( '[ProductTransformer] Unexpected attribute type: ' . gettype( $attribute ) );
				continue;
			}

			$name = wc_attribute_label( $attribute->get_name(), $this->wc_product );

			if ( $attribute->is_taxonomy() ) {
				$values = wc_get_product_terms(
					$this->wc_product->get_id(),
					$attribute->get_name(),
					[ 'fields' => 'names' ]
				);
			} else {
				$values = $attribute->get_options();
			}

			if ( ! empty( $values ) ) {
				$attributes_array[ $name ] = implode( ', ', array_filter( $values ) );
			}
		}

		foreach ( $attributes_array as $key => $value ) {
			if ( strlen( strip_tags( wp_kses_post( $value ) ) ) > 1 ) {
				$array[] = $this->mapProductAttribute( $key, wp_kses_post( $value ) );
			}
		}

		return $array;
	}

	public function mapProductAttribute( $name, $value ): ProductAttribute {
		return new ProductAttribute( $name, $value );
	}


	/**
	 * Read the stock quantity from the product.
	 *
	 * @param bool $is_hot_product If the product is a hot product.
	 * @param int $variation_id The ID of the variation.
	 *
	 * @return Quantity The stock quantity.
	 */
	public function readStockQuantity( bool $is_hot_product = false, $variation_id = null ): Quantity {
		$quantity         = new Quantity();
		$original_product = $this->wc_product;

		if ( $variation_id && $variation_id !== $this->wc_product->get_id() ) {
			$variation = wc_get_product( $variation_id );
			if ( $variation instanceof \WC_Product_Variation ) {
				$this->wc_product = $variation;
			}
		}

		$productQuantity = ( new QuantityIntegrationFactory() )->create( $this->wc_product );

		$quantity->set_quantity_type( $productQuantity->get_quantity_type() );
		$quantity->set_quantity_unit( $productQuantity->get_quantity_unit() );
		$quantity->set_quantity_jump( (int) $productQuantity->get_step_quantity() );
		Logger::debug( '[Add to Basket] Stock quantity: ' . serialize( $this->wc_product ) );

		$availableQuantity = $productQuantity->get_quantity();
		if ( ! is_numeric( $availableQuantity ) ) {
			$availableQuantity = 0;
		}

		if ( $is_hot_product ) {
			if (
				( $this->wc_product->managing_stock() && $availableQuantity === 0 ) ||
				( ! $this->wc_product->managing_stock() && $this->wc_product->get_stock_status() === 'outofstock' )
			) {
				$quantity->set_available_quantity( 0 );
			} else {
				$quantity->set_available_quantity( $availableQuantity );
			}
		} else {
			if ( $this->wc_product->is_type( 'variation' ) && $this->wc_product->managing_stock() ) {
				$variant_stock = $this->wc_product->get_stock_quantity();

				$quantity->set_quantity_type( 'INTEGER' );
				$quantity->set_quantity_unit( $this->wc_product->get_attribute( 'unit' ) ?: 'szt' );
				$quantity->set_quantity_jump( 1 );

				if ( $variant_stock > 0 ) {
					$quantity->set_available_quantity( $variant_stock );
				} elseif (
					$this->wc_product->is_on_backorder() ||
					$this->wc_product->get_backorders( false ) === 'notify' ||
					$this->wc_product->get_backorders( false ) === 'yes'
				) {
					$quantity->set_available_quantity( 999 );
				} else {
					$quantity->set_available_quantity( 0 );
				}
			} elseif ( $availableQuantity > 0 ) {
				$quantity->set_available_quantity( $availableQuantity );
			} elseif (
				$this->wc_product->is_on_backorder() ||
				$this->wc_product->get_backorders( false ) === 'notify' ||
				$this->wc_product->get_backorders( false ) === 'yes'
			) {
				$quantity->set_available_quantity( 999 );
			} elseif (
				( $this->wc_product->managing_stock() && $availableQuantity === 0 ) ||
				( ! $this->wc_product->managing_stock() && $this->wc_product->get_stock_status() === 'outofstock' )
			) {
				$quantity->set_available_quantity( 0 );
			} else {
				$quantity->set_available_quantity( 999 );
			}
		}

		$maxQuantity = $this->wc_product->get_max_purchase_quantity();
		$quantity->set_max_quantity( ( $maxQuantity !== - 1 ) ? (int) $maxQuantity : 999 );

		$this->wc_product = $original_product;

		return $quantity;
	}

	public function getProductIdentifier(): string {

		return (string) $this->wc_product->get_id();
	}

	public function parseGutenbergBlocks( string $description ): string {
		$blocks = parse_blocks( $description );
		$output = '';

		foreach ( $blocks as $block ) {
			if ( $block['blockName'] === 'core/paragraph' ) {
				$output .= render_block( $block );
			}
		}

		return $output;
	}

	public function isProductVirtualOrDigital(): bool {
		return $this->wc_product->is_virtual() || $this->wc_product->is_downloadable();
	}

	protected function getParentProduct(): ?WC_Product {
		$parent_id = $this->wc_product->get_parent_id();

		if ( ! $parent_id ) {
			return null;
		}

		if ( ! isset( $this->parent_cache[ $parent_id ] ) ) {
			$this->parent_cache[ $parent_id ] = wc_get_product( $parent_id );
		}

		return $this->parent_cache[ $parent_id ];
	}
}
