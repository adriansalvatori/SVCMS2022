<?php

namespace Objectiv\Plugins\Checkout\Compatibility\Plugins;

use Objectiv\Plugins\Checkout\Compatibility\CompatibilityAbstract;

class Chronopost extends CompatibilityAbstract {
	public function is_available(): bool {
		return function_exists( 'activate_chronopost' );
	}

	public function run() {
		add_action(
			'cfw_checkout_payment_method_tab',
			function() {
				do_action( 'woocommerce_review_order_before_payment' );
			},
			9
		);
	}
}
