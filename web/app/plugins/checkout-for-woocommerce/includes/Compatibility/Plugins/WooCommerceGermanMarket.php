<?php

namespace Objectiv\Plugins\Checkout\Compatibility\Plugins;

use Objectiv\Plugins\Checkout\Compatibility\CompatibilityAbstract;

class WooCommerceGermanMarket extends CompatibilityAbstract {
	public function is_available(): bool {
		return class_exists( '\\Woocommerce_German_Market' );
	}

	public function run() {
		remove_filter( 'woocommerce_order_button_html', array( 'WGM_Template', 'remove_order_button_html' ), 9999 );
	}

	public function run_on_update_checkout() {
		$this->run();
	}
}
