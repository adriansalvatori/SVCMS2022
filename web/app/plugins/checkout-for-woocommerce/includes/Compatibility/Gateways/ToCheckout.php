<?php

namespace Objectiv\Plugins\Checkout\Compatibility\Gateways;

use Objectiv\Plugins\Checkout\Compatibility\CompatibilityAbstract;

class ToCheckout extends CompatibilityAbstract {
	public function is_available(): bool {
		return function_exists( 'woocommerce_tocheckoutcw_init' );
	}

	public function run() {
		$_POST['post_data'] = array();
	}
}
