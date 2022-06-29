<?php

namespace Objectiv\Plugins\Checkout\Compatibility\Plugins;

use Objectiv\Plugins\Checkout\Compatibility\CompatibilityAbstract;

class WPCProductBundles extends CompatibilityAbstract {
	public function is_available(): bool {
		return function_exists( 'woosb_init' );
	}

	public function run_immediately() {
		add_filter( 'woocommerce_checkout_cart_item_quantity', array( $this, 'hide_quantity_dropdown' ), 100, 2 );
	}

	public function hide_quantity_dropdown( $quantity, $cart_item ) {
		if ( isset( $cart_item['woosb_parent_id'] ) ) {
			return $cart_item['quantity'];
		}

		return $quantity;
	}
}
