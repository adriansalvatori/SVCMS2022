<?php

namespace Objectiv\Plugins\Checkout\Compatibility\Themes;

use Objectiv\Plugins\Checkout\Compatibility\CompatibilityAbstract;

class Shoptimizer extends CompatibilityAbstract {
	public function is_available(): bool {
		return function_exists( 'shoptimizer_cart_progress' );
	}

	function run() {
		remove_action( 'woocommerce_before_checkout_form', 'shoptimizer_cart_progress', 5 );
		remove_filter( 'woocommerce_cart_item_name', 'shoptimizer_product_thumbnail_in_checkout', 20 );
		remove_action( 'woocommerce_after_checkout_form', 'woocommerce_checkout_coupon_form' );
		remove_action( 'woocommerce_after_checkout_form', 'shoptimizer_coupon_wrapper_start', 5 );
		remove_action( 'woocommerce_after_checkout_form', 'shoptimizer_coupon_wrapper_end', 60 );
	}

	function run_on_update_checkout() {
		$this->run();
	}
}
