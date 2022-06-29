<?php

namespace Objectiv\Plugins\Checkout\Model\Bumps;

use Objectiv\Plugins\Checkout\Interfaces\BumpInterface;

class NullBump implements BumpInterface {
	public function get_id(): int {
		return 0;
	}

	public function add_to_cart( \WC_Cart $cart ) {
		return false;
	}

	public function record_displayed() {
		return;
	}

	public function display( string $location ) {
		return;
	}

	public function record_purchased() {
		return;
	}

	public function get_cart_item_subtotal( $subtotal, $cart_item ) {
		return $subtotal;
	}

	public function update_cart_item_price( \WC_Cart $cart, $cart_item_key ) {
		return;
	}

	public function add_bump_meta_to_order_item( $item, $values ) {
		return;
	}

	public function get_cfw_cart_item_discount( string $price_html ) {
		return $price_html;
	}

	public function get_conversion_rate() {
		return '--';
	}

	public function get_captured_revenue(): float {
		return 0.0;
	}

	public function is_in_cart(): bool {
		return false;
	}

	public function get_item_removal_behavior(): string {
		return 'delete';
	}

	public function is_cart_bump_valid(): bool {
		return false;
	}

	public function can_quantity_be_updated(): bool {
		return false;
	}
}
