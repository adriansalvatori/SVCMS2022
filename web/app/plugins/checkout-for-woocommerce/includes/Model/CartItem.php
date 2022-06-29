<?php

namespace Objectiv\Plugins\Checkout\Model;

use Objectiv\Plugins\Checkout\Interfaces\ItemInterface;
use Objectiv\Plugins\Checkout\Managers\SettingsManager;
use WC_Product;

class CartItem implements ItemInterface {
	protected $thumbnail;
	protected $quantity;
	protected $title;
	protected $url;
	protected $subtotal;
	protected $row_class;
	protected $item_key;
	protected $raw_item;
	protected $product;
	protected $data;
	protected $formatted_data;

	public function __construct( string $key, array $item ) {
		$this->item_key = $key;
		$this->raw_item = $item;

		/** @var WC_Product $_product */
		$product       = apply_filters( 'woocommerce_cart_item_product', $item['data'], $item, $key );
		$this->product = $product;

		$woocommerce_filtered_cart_item_row_class = esc_attr( apply_filters( 'woocommerce_cart_item_class', 'cart_item', $item, $key ) );
		$this->thumbnail                          = apply_filters( 'woocommerce_cart_item_thumbnail', $product->get_image( 'cfw_cart_thumb' ), $item, $key );
		$this->quantity                           = floatval( $item['quantity'] );
		$this->title                              = apply_filters( 'woocommerce_cart_item_name', $product->get_name(), $item, $key );
		$this->url                                = get_permalink( $item['product_id'] );
		$this->subtotal                           = apply_filters( 'woocommerce_cart_item_subtotal', WC()->cart->get_product_subtotal( $product, $item['quantity'] ), $item, $key );
		$this->row_class                          = apply_filters( 'cfw_cart_item_row_class', $woocommerce_filtered_cart_item_row_class, $item );
		$this->data                               = $this->get_cart_item_data( $item );
		$this->formatted_data                     = $this->get_formatted_cart_data();
	}

	protected function get_cart_item_data( array $cart_item ): array {
		$item_data = array();

		// Variation values are shown only if they are not found in the title as of 3.0.
		// This is because variation titles display the attributes.
		if ( $cart_item['data']->is_type( 'variation' ) && is_array( $cart_item['variation'] ) ) {
			foreach ( $cart_item['variation'] as $name => $value ) {
				$taxonomy = wc_attribute_taxonomy_name( str_replace( 'attribute_pa_', '', urldecode( $name ) ) );

				if ( taxonomy_exists( $taxonomy ) ) {
					// If this is a term slug, get the term's nice name.
					$term = get_term_by( 'slug', $value, $taxonomy );
					if ( ! is_wp_error( $term ) && $term && $term->name ) {
						$value = $term->name;
					}
					$label = wc_attribute_label( $taxonomy );
				} else {
					// If this is a custom option slug, get the options name.
					$value = apply_filters( 'woocommerce_variation_option_name', $value, null, $taxonomy, $cart_item['data'] );
					$label = wc_attribute_label( str_replace( 'attribute_', '', $name ), $cart_item['data'] );
				}

				// Check the nicename against the title.
				if ( '' === $value || wc_is_attribute_in_product_name( $value, $cart_item['data']->get_name() ) ) {
					continue;
				}

				$item_data[] = array(
					'key'   => $label,
					'value' => $value,
				);
			}
		}

		// Filter item data to allow 3rd parties to add more to the array.
		$item_data = apply_filters( 'woocommerce_get_item_data', $item_data, $cart_item );

		$prepared_data = array();

		// Format item data ready to display.
		foreach ( $item_data as $key => $data ) {
			// Set hidden to true to not display meta on cart.
			if ( ! empty( $data['hidden'] ) ) {
				unset( $item_data[ $key ] );
				continue;
			}

			$key                   = ! empty( $data['key'] ) ? $data['key'] : $data['name'];
			$display               = ! empty( $data['display'] ) ? $data['display'] : $data['value'];
			$prepared_data[ $key ] = $display;
		}

		return $prepared_data;
	}

	protected function get_formatted_cart_data() {
		if ( apply_filters( 'cfw_cart_item_data_expanded', SettingsManager::instance()->get_setting( 'cart_item_data_display' ) === 'woocommerce' ) ) {
			$output = wc_get_formatted_cart_item_data( $this->get_raw_item() );

			return str_replace( ' :', ':', $output );
		}

		$item_data = $this->get_data();

		if ( empty( $item_data ) ) {
			return '';
		}

		$display_outputs = array();

		foreach ( $item_data as $raw_key => $raw_value ) {
			if ( ! is_string( $raw_value ) ) {
				continue;
			}

			$key               = wp_kses_post( $raw_key );
			$value             = strip_tags( $raw_value );
			$display_outputs[] = "$key: $value";
		}

		return join( ' / ', $display_outputs );
	}


	public function get_thumbnail(): string {
		return strval( $this->thumbnail );
	}

	public function get_quantity(): float {
		return floatval( $this->quantity );
	}

	public function get_title(): string {
		return strval( $this->title );
	}

	public function get_url(): string {
		return strval( $this->url );
	}

	public function get_subtotal(): string {
		return strval( $this->subtotal );
	}

	public function get_row_class(): string {
		return strval( $this->row_class );
	}

	public function get_item_key(): string {
		return strval( $this->item_key );
	}

	public function get_raw_item() {
		// TODO: Eliminate the necessity of this workaround in a future major version
		return $this->raw_item;
	}

	public function get_product(): WC_Product {
		return $this->product;
	}

	public function get_data(): array {
		return $this->data ?? array();
	}

	public function get_formatted_data(): string {
		return $this->formatted_data;
	}
}
