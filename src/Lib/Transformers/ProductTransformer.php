<?php
/**
 * Product transformer.
 *
 * @package Ilabs\Inpost_Pay
 */

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
use WC_Product;

/**
 * Transforms WC_Product into product data objects for the InPost Pay API.
 */
class ProductTransformer {


	protected WC_Product $wc_product;
	private array $parent_cache = array();

	/**
	 * Constructor.
	 *
	 * @param WC_Product $product WooCommerce product.
	 */
	public function __construct( WC_Product $product ) {
		$this->wc_product = $product;
	}

	/**
	 * Reads the base (regular) price of the product.
	 *
	 * @return Price
	 */
	public function read_product_base_price(): Price {
		$price = new Price();

		$price_including_tax = wc_get_price_including_tax( $this->wc_product, array( 'price' => $this->wc_product->get_regular_price() ) );
		$price_excluding_tax = wc_get_price_excluding_tax( $this->wc_product, array( 'price' => $this->wc_product->get_regular_price() ) );
		$vat                 = $price_including_tax - $price_excluding_tax;

		$price->set_gross( number_format( $price_including_tax, 2, '.', '' ) );
		$price->set_net( $price_excluding_tax );
		$price->set_vat( number_format( $vat, 2, '.', '' ) );

		return $price;
	}

	/**
	 * Reads the promo (sale) price of the product.
	 *
	 * @return Price
	 */
	public function read_product_promo_price(): Price {
		$price = new Price();

		$price_including_tax = wc_get_price_including_tax( $this->wc_product, array( 'price' => $this->wc_product->get_sale_price() ) );
		$price_excluding_tax = wc_get_price_excluding_tax( $this->wc_product, array( 'price' => $this->wc_product->get_sale_price() ) );
		$vat                 = $price_including_tax - $price_excluding_tax;

		$price->set_gross( number_format( $price_including_tax, 2, '.', '' ) );
		$price->set_net( $price_excluding_tax );
		$price->set_vat( number_format( $vat, 2, '.', '' ) );

		return $price;
	}

	/**
	 * Maps product data into a ProductInterface object.
	 *
	 * @param bool $is_related_product Whether this is a related product.
	 *
	 * @return ProductInterface
	 */
	public function map_product_data( bool $is_related_product = false ): ProductInterface {

		if ( $is_related_product ) {
			$product = new RelatedProduct();
		} else {
			$product = new Product();
		}

		$product->set_product_id( $this->get_product_identifier() );
		if ( isset( $this->wc_product->get_category_ids()[0] ) ) {
			$product->set_product_category( $this->wc_product->get_category_ids()[0] );
		}

		$product->set_ean( EANHelper::prepareEan( $this->wc_product ) );
		$product->set_product_name( wp_strip_all_tags( html_entity_decode( $this->wc_product->get_name() ) ) );
		$product->set_product_description( $this->get_description() );
		$product->set_product_link( $this->wc_product->get_permalink() );
		$product->set_product_image( $this->get_thumbnail_image() );

		$product->set_additional_product_images( $this->get_product_images() );
		$product->set_product_attributes( $this->map_product_attributes() );
		if ( $this->is_product_virtual_or_digital() ) {
			$product->set_product_type( AbstractProduct::TYPE_DIGITAL );
		}

		return $product;
	}

	/**
	 * Returns the product thumbnail image URL.
	 *
	 * @return string
	 */
	protected function get_thumbnail_image(): string {
		$image = wp_get_attachment_image_src( get_post_thumbnail_id( $this->wc_product->get_id() ), 'single-post-thumbnail' );
		if ( ! $image && $this->wc_product->get_parent_id() ) {
			$image = wp_get_attachment_image_src( get_post_thumbnail_id( $this->wc_product->get_parent_id() ), 'single-post-thumbnail' );
		}

		if ( $image && $image[0] ) {
			return str_replace( 'http://', 'https://', $image[0] );
		}

		return '';
	}

	/**
	 * Returns the product description.
	 *
	 * @return string
	 */
	protected function get_description(): string {
		$description = $this->format_product_description( $this->get_description_by_wc_product() );

		if ( ! $description && $this->wc_product->get_parent_id() ) {
			$parent = $this->get_parent_product();
			if ( $parent ) {
				$description = $this->format_product_description( $this->get_description_by_wc_product() );
			}
		}

		return $description;
	}

	/**
	 * Returns product gallery images.
	 *
	 * @param bool  $should_include_gallery Whether to include gallery images.
	 * @param array $gallery_image_ids      Gallery image IDs to use.
	 *
	 * @return array
	 */
	public function get_product_images(
		bool $should_include_gallery = false,
		array $gallery_image_ids = array()
	): array {
		$images = array();
		if ( ! $should_include_gallery ) {
			Logger::log( '[HOT_PRODUCT_DEBUG] Gallery skipped; should_include_gallery=' . ( $should_include_gallery ? 'true' : 'false' ) . ')' );

			return array();
		}

		$gallery_image_ids = ! empty( $gallery_image_ids ) ? $gallery_image_ids : $this->wc_product->get_gallery_image_ids();

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


	/**
	 * Formats product description: strips shortcodes, applies filters, strips tags.
	 *
	 * @param string|null $description Raw description.
	 *
	 * @return string
	 */
	protected function format_product_description( ?string $description ): string {
		if ( empty( $description ) ) {
			return '';
		}

		$description = $this->remove_unregistered_shortcodes( $description );
		$description = do_shortcode( $description );

		for ( $i = 1; $i <= 6; $i++ ) {
			$description = str_replace(
				'<h' . $i . '>',
				"\r\n<h" . $i . '>',
				$description
			);
		}

		$description = $this->replace_literal_newlines( $description );
		$description = trim( $description );

		return wp_strip_all_tags( $description );
	}

	/**
	 * Removes unregistered shortcodes from content.
	 *
	 * @param mixed $content The content to process.
	 *
	 * @return string
	 */
	private function remove_unregistered_shortcodes( $content ): string {
		global $shortcode_tags;

		$matches_block = array();

		// Find block shortcodes.
		preg_match_all(
			'/[\/?\w+[^\]]*].*[\/?\w+[^\]]*]/',
			$content,
			$matches_block,
			PREG_SET_ORDER
		);

		$to_remove_blocks = array();
		foreach ( $matches_block as $match ) {
			$pattern = '/\[([\w_]+)].*?\[\/\1]/';

			// Use preg_match to find the shortcode.
			if ( isset( $match[1] ) && preg_match( $pattern, $match[1], $matches ) ) {
				// Return the ID (which is the first capturing group).

				if ( isset( $matches[1] ) && ! isset( $shortcode_tags[ $matches[1] ] ) ) {
					$to_remove_blocks[] = $matches[1];
				}
			}
		}

		if ( ! empty( $to_remove_blocks ) ) {
			$pattern = get_shortcode_regex( $to_remove_blocks );
			$content = preg_replace_callback(
				"/$pattern/",
				'strip_shortcode_tag',
				$content
			);
		}

		// Find inline shortcodes.
		preg_match_all(
			'/\[(\w+).*]/',
			$content,
			$matches_inline,
			PREG_SET_ORDER
		);

		$to_remove_inline = array();
		foreach ( $matches_inline as $match ) {
			if ( isset( $match[1] ) && ! isset( $shortcode_tags[ $match[1] ] ) ) {
				$to_remove_inline[] = $match[1];
			}
		}

		if ( ! empty( $to_remove_inline ) ) {
			$pattern = get_shortcode_regex( $to_remove_inline );
			$content = preg_replace_callback(
				"/$pattern/",
				'strip_shortcode_tag',
				$content
			);
		}

		if ( ! is_string( $content ) ) {
			$content = '';
		}

		return $content;
	}

	/**
	 * Replaces literal \n sequences with actual newlines.
	 *
	 * @param mixed $input Input string.
	 *
	 * @return string
	 */
	private function replace_literal_newlines( $input ): string {
		$output = preg_replace( '/\\\\n/', "\r\n", $input );

		return is_string( $output ) ? $output : '';
	}

	/**
	 * Returns product description based on plugin settings.
	 *
	 * @return string
	 */
	protected function get_description_by_wc_product(): string {

		if ( SettingsPage::OPT_DROPDOWN_ID_SHORT_PRODUCT_DESC_MAP === get_option(
			SettingsPage::OPT_KEY_PRODUCT_DESC_MAP,
			SettingsPage::OPT_DROPDOWN_ID_DEFAULT_PRODUCT_DESC_MAP
		) ) {
			return $this->wc_product->get_short_description();
		}
		$description = $this->wc_product->get_description();

		if ( function_exists( 'has_blocks' ) && has_blocks( $description ) ) {
			$description = $this->parse_gutenberg_blocks( $description );
		}

		return '' === $description ? $this->wc_product->get_short_description() : $description;
	}

	/**
	 * Maps product variation attributes into Variant objects.
	 *
	 * @return array
	 */
	public function map_product_variables(): array {
		$array  = array();
		$parent = $this->get_parent_product();
		if ( $parent ) {
			$this->wc_product = $parent;
		}

		foreach ( $this->wc_product->get_attributes() as $attribute ) {
			if ( $attribute->get_visible() && $attribute->get_variation() === true ) {
				$array[] = $this->map_product_variable( $attribute );
			}
		}

		return $array;
	}

	/**
	 * Maps a single product attribute into a Variant object.
	 *
	 * @param mixed $attribute WC product attribute.
	 *
	 * @return Variant
	 */
	public function map_product_variable( $attribute ): Variant {
		$variant = new Variant();

		$label = wc_attribute_label( $attribute->get_name() );
		$variant->set_variant_id( (int) $attribute->get_id() );
		$variant->set_variant_name( $label ? $label : $attribute->get_name() );
		$variant->set_variant_values( implode( ', ', $attribute->get_options() ) );

		$variant->set_variant_description( '' );
		$variant->set_variant_type( '' );

		return $variant;
	}

	/**
	 * Maps all product attributes into ProductAttribute objects.
	 *
	 * @return array
	 */
	public function map_product_attributes(): array {
		$array = array();

		$attributes       = $this->wc_product->get_attributes();
		$attributes_array = array();

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
					array( 'fields' => 'names' )
				);
			} else {
				$values = $attribute->get_options();
			}

			if ( ! empty( $values ) ) {
				$attributes_array[ $name ] = implode( ', ', array_filter( $values ) );
			}
		}

		foreach ( $attributes_array as $key => $value ) {
			if ( strlen( wp_strip_all_tags( wp_kses_post( $value ) ) ) > 1 ) {
				$array[] = $this->map_product_attribute( $key, wp_kses_post( $value ) );
			}
		}

		return $array;
	}

	/**
	 * Creates a ProductAttribute object from name and value.
	 *
	 * @param mixed $name  Attribute name.
	 * @param mixed $value Attribute value.
	 *
	 * @return ProductAttribute
	 */
	public function map_product_attribute( $name, $value ): ProductAttribute {
		return new ProductAttribute( $name, $value );
	}


	/**
	 * Read the stock quantity from the product.
	 *
	 * @param bool $is_hot_product If the product is a hot product.
	 * @param int  $variation_id   The ID of the variation.
	 *
	 * @return Quantity The stock quantity.
	 */
	public function read_stock_quantity( bool $is_hot_product = false, $variation_id = null ): Quantity {
		$quantity         = new Quantity();
		$original_product = $this->wc_product;

		if ( $variation_id && $variation_id !== $this->wc_product->get_id() ) {
			$variation = wc_get_product( $variation_id );
			if ( $variation instanceof \WC_Product_Variation ) {
				$this->wc_product = $variation;
			}
		}

		$product_quantity = ( new QuantityIntegrationFactory() )->create( $this->wc_product );

		$quantity->set_quantity_type( $product_quantity->get_quantity_type() );
		$quantity->set_quantity_unit( $product_quantity->get_quantity_unit() );
		$quantity->set_quantity_jump( (int) $product_quantity->get_step_quantity() );
		Logger::debug( '[Add to Basket] Stock quantity: ' . wp_json_encode( $this->wc_product->get_id() ) );

		$available_quantity = $product_quantity->get_quantity();
		if ( ! is_numeric( $available_quantity ) ) {
			$available_quantity = 0;
		}

		if ( $is_hot_product ) {
			if (
				( $this->wc_product->managing_stock() && 0 === $available_quantity ) ||
				( ! $this->wc_product->managing_stock() && 'outofstock' === $this->wc_product->get_stock_status() )
			) {
				$quantity->set_available_quantity( 0 );
			} else {
				$quantity->set_available_quantity( $available_quantity );
			}
		} elseif ( $this->wc_product->is_type( 'variation' ) && $this->wc_product->managing_stock() ) {
				$variant_stock = $this->wc_product->get_stock_quantity();

				$quantity->set_quantity_type( 'INTEGER' );
				$unit = $this->wc_product->get_attribute( 'unit' );
				$quantity->set_quantity_unit( $unit ? $unit : 'szt' );
				$quantity->set_quantity_jump( 1 );

			if ( $variant_stock > 0 ) {
				$quantity->set_available_quantity( $variant_stock );
			} elseif (
					$this->wc_product->is_on_backorder() ||
					'notify' === $this->wc_product->get_backorders( false ) ||
					'yes' === $this->wc_product->get_backorders( false )
				) {
				$quantity->set_available_quantity( 999 );
			} else {
				$quantity->set_available_quantity( 0 );
			}
		} elseif ( $available_quantity > 0 ) {
			$quantity->set_available_quantity( $available_quantity );
		} elseif (
				$this->wc_product->is_on_backorder() ||
				'notify' === $this->wc_product->get_backorders( false ) ||
				'yes' === $this->wc_product->get_backorders( false )
			) {
			$quantity->set_available_quantity( 999 );
		} elseif (
				( $this->wc_product->managing_stock() && 0 === $available_quantity ) ||
				( ! $this->wc_product->managing_stock() && 'outofstock' === $this->wc_product->get_stock_status() )
			) {
			$quantity->set_available_quantity( 0 );
		} else {
			$quantity->set_available_quantity( 999 );
		}

		$max_quantity = (int) $this->wc_product->get_max_purchase_quantity();
		$quantity->set_max_quantity( $max_quantity < 0 ? 999 : $max_quantity );

		$this->wc_product = $original_product;

		return $quantity;
	}

	/**
	 * Returns the product identifier string.
	 *
	 * @return string
	 */
	public function get_product_identifier(): string {

		return (string) $this->wc_product->get_id();
	}

	/**
	 * Parses Gutenberg blocks and returns only paragraph content.
	 *
	 * @param string $description Post content with blocks.
	 *
	 * @return string
	 */
	public function parse_gutenberg_blocks( string $description ): string {
		$blocks = parse_blocks( $description );
		$output = '';

		foreach ( $blocks as $block ) {
			if ( 'core/paragraph' === $block['blockName'] ) {
				$output .= render_block( $block );
			}
		}

		return $output;
	}

	/**
	 * Returns whether the product is virtual or digital.
	 *
	 * @return bool
	 */
	public function is_product_virtual_or_digital(): bool {
		return ! $this->wc_product->needs_shipping();
	}

	/**
	 * Returns the parent product, with caching.
	 *
	 * @return WC_Product|null
	 */
	protected function get_parent_product(): ?WC_Product {
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
