<?php

namespace Objectiv\Plugins\Checkout\Compatibility\Themes;

use Objectiv\Plugins\Checkout\Compatibility\CompatibilityAbstract;

class Pro extends CompatibilityAbstract {
	public function is_available(): bool {
		return function_exists( 'x_woocommerce_add_submit_spinner' );
	}

	function run() {
		$this->disable_spinner();
	}

	public function run_on_update_checkout() {
		$this->disable_spinner();
	}

	function disable_spinner() {
		remove_action( 'woocommerce_review_order_after_submit', 'x_woocommerce_add_submit_spinner' );
	}
}
