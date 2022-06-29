<?php

namespace Objectiv\Plugins\Checkout\Compatibility\Plugins;

use Objectiv\Plugins\Checkout\Compatibility\CompatibilityAbstract;

class SUMOSubscriptions extends CompatibilityAbstract {
	public function is_available(): bool {
		return function_exists( 'sumosubscriptions' );
	}

	public function run() {
		$this->add_cart_item_message();
	}

	public function run_on_update_checkout() {
		$this->add_cart_item_message();
	}

	public function add_cart_item_message() {
		add_action( 'cfw_cart_item_after_data', array( $this, 'cart_item_message' ), 10, 2 );
	}

	public function cart_item_message( $cart_item, $cart_item_key ) {
		echo \SUMOSubscriptions_Frontend::get_subscription_message_in_cart_r_checkout( '', $cart_item, $cart_item_key );
	}
}