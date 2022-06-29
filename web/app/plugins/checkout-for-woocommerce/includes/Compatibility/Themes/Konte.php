<?php

namespace Objectiv\Plugins\Checkout\Compatibility\Themes;

use Objectiv\Plugins\Checkout\Compatibility\CompatibilityAbstract;

class Konte extends CompatibilityAbstract {
	public function is_available(): bool {
		return function_exists( 'konte_content_width' );
	}

	function run() {
		remove_action( 'woocommerce_before_checkout_form', 'Konte_WooCommerce_Template_Checkout::checkout_login_form', 10 );
	}
}
