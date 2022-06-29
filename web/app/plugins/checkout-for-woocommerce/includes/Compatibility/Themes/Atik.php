<?php

namespace Objectiv\Plugins\Checkout\Compatibility\Themes;

use Objectiv\Plugins\Checkout\Compatibility\CompatibilityAbstract;

class Atik extends CompatibilityAbstract {
	public function is_available(): bool {
		return class_exists( '\\Atik_WooCommerce' );
	}

	public function run_immediately() {
		remove_action( 'woocommerce_checkout_after_order_review', 'woocommerce_checkout_payment', 20 );
	}
}