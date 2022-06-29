<?php

namespace Objectiv\Plugins\Checkout\Compatibility\Plugins;

use Objectiv\Plugins\Checkout\Compatibility\CompatibilityAbstract;

class WooCommerceTipping extends CompatibilityAbstract {
	public function is_available(): bool {
		return function_exists( 'wpslash_tipping_woocommerce_checkout_order_review_form' );
	}

	public function run() {
		add_action( 'cfw_checkout_cart_summary', array( $this, 'output' ), 65 );
	}

	public function output() {
		echo '<style>.wpslash-tip-wrapper { float: none !important; } .wpslash_tip_remove_btn  {right: unset !important; margin-left: 10px !important;}</style>';
		wpslash_tipping_woocommerce_checkout_order_review_form();
	}
}
