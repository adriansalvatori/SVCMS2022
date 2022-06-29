<?php

namespace Objectiv\Plugins\Checkout\Compatibility\Themes;

use Objectiv\Plugins\Checkout\Compatibility\CompatibilityAbstract;

class Listable extends CompatibilityAbstract {
	public function is_available(): bool {
		return function_exists( 'listable_setup' );
	}

	function run() {
		remove_action( 'woocommerce_checkout_before_customer_details', 'woocommerce_checkout_login_form', 10 );
		remove_action( 'woocommerce_checkout_before_customer_details', 'woocommerce_checkout_coupon_form', 10 );
		remove_action( 'woocommerce_checkout_after_customer_details', 'woocommerce_checkout_payment', 20 );
	}
}
