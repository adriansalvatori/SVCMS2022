<?php

namespace Objectiv\Plugins\Checkout\Compatibility\Themes;

class Metro {
	public function is_available(): bool {
		return class_exists( '\Metro_Main' );
	}

	public function init() {
		add_action( 'wp', array( $this, 'maybe_run' ) );
		add_action( 'cfw_checkout_update_order_review', array( $this, 'run' ) );
	}

	public function maybe_run() {
		if ( ! cfw_is_checkout() && ! is_checkout_pay_page() ) {
			return;
		}

		$this->run();
	}

	public function run() {
		if ( ! $this->is_available() ) {
			return;
		}

		remove_action( 'woocommerce_checkout_after_order_review', 'woocommerce_checkout_payment' );
	}
}