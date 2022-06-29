<?php

namespace Objectiv\Plugins\Checkout\Compatibility\Themes;

use Objectiv\Plugins\Checkout\Compatibility\CompatibilityAbstract;

class Electro extends CompatibilityAbstract {
	public function is_available(): bool {
		return function_exists( 'electro_wrap_order_review' );
	}

	function pre_init() {
		add_action( 'woocommerce_checkout_update_order_review', array( $this, 'cleanup_actions' ) );
	}

	public function run() {
		$this->cleanup_actions();
	}

	function cleanup_actions() {
		remove_action( 'woocommerce_checkout_shipping', 'electro_shipping_details_header', 0 );
		remove_action( 'woocommerce_checkout_before_order_review', 'electro_wrap_order_review', 0 );
		remove_action( 'woocommerce_checkout_after_order_review', 'electro_wrap_order_review_close', 0 );
	}
}
