<?php

namespace Objectiv\Plugins\Checkout\Model;

use Objectiv\Plugins\Checkout\Interfaces\ItemInterface;
use Objectiv\Plugins\Checkout\Managers\SettingsManager;
use WC_Order_Item;
use WC_Product;

class OrderItem implements ItemInterface {
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

	/**
	 * @param array|WC_Order_Item $item
	 */
	public function __construct( WC_Order_Item $item ) {
		$order         = wc_get_order( $item->get_order_id() );
		$item_product  = $item->get_product();
		$item_subtotal = $order->get_formatted_line_subtotal( $item );

		$this->thumbnail      = apply_filters( 'cfw_order_item_thumbnail', $item_product ? $item_product->get_image( 'cfw_cart_thumb' ) : '', $item );
		$this->quantity       = $item->get_quantity();
		$this->title          = $item->get_name();
		$this->url            = $item_product ? get_permalink( $item->get_product_id() ) : '';
		$this->subtotal       = ! empty( $item_subtotal ) ? $item_subtotal : wc_price( $item->get_subtotal() );
		$this->row_class      = apply_filters( 'cfw_order_item_row_class', '', $item );
		$this->item_key       = $item->get_id();
		$this->raw_item       = $item;
		$this->product        = $item_product;
		$this->data           = $this->get_order_item_data( $item );
		$this->formatted_data = $this->get_formatted_order_item_data();
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

	/**
	 * @return WC_Order_Item
	 */
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

	protected function get_order_item_data( WC_Order_Item $item ): array {
		$data = array();

		foreach ( $item->get_formatted_meta_data() as $meta ) {
			$data[ $meta->display_key ] = $meta->display_value;
		}

		return $data;
	}

	protected function get_formatted_order_item_data() {
		if ( apply_filters( 'cfw_cart_item_data_expanded', SettingsManager::instance()->get_setting( 'cart_item_data_display' ) === 'woocommerce' ) ) {
			return wc_display_item_meta( $this->get_raw_item(), array( 'echo' => false ) );
		}

		$item_data = $this->get_data();

		if ( empty( $item_data ) ) {
			return '';
		}

		$display_outputs = array();

		foreach ( $item_data as $raw_key => $raw_value ) {
			$key               = wp_kses_post( $raw_key );
			$value             = strip_tags( $raw_value );
			$display_outputs[] = "$key: $value";
		}

		return join( ' / ', $display_outputs );
	}
}
