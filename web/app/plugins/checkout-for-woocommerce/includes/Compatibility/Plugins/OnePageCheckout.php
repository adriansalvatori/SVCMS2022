<?php

namespace Objectiv\Plugins\Checkout\Compatibility\Plugins;

use Objectiv\Plugins\Checkout\Compatibility\CompatibilityAbstract;

class OnePageCheckout extends CompatibilityAbstract {
	public function is_available(): bool {
		return function_exists( 'is_wcopc_checkout' );
	}

	public function run() {
		add_filter( 'cfw_disable_templates', 'is_wcopc_checkout', 10, 1 );
	}
}
