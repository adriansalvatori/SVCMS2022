<?php

namespace Objectiv\Plugins\Checkout\Compatibility\Themes;

use Objectiv\Plugins\Checkout\Compatibility\CompatibilityAbstract;

class Uncode extends CompatibilityAbstract {
	public function is_available(): bool {
		return function_exists( 'uncode_woocommerce_activate_thumbs_on_order_review_table' );
	}

	public function run() {
		remove_action( 'woocommerce_review_order_before_cart_contents', 'uncode_woocommerce_activate_thumbs_on_order_review_table' );
	}
}