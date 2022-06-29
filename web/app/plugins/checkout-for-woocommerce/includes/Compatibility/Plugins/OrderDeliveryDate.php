<?php

namespace Objectiv\Plugins\Checkout\Compatibility\Plugins;

use Objectiv\Plugins\Checkout\Compatibility\CompatibilityAbstract;

class OrderDeliveryDate extends CompatibilityAbstract {
	public function is_available(): bool {
		return class_exists( '\\order_delivery_date' );
	}

	public function pre_init() {
		add_filter( 'orddd_shopping_cart_hook', array( $this, 'set_delivery_field_hook' ) );
	}

	public function set_delivery_field_hook( $hook ) {
		return 'cfw_checkout_after_shipping_methods';
	}

	public function typescript_class_and_params( array $compatibility ): array {
		$compatibility[] = array(
			'class'  => 'OrderDeliveryDate',
			'params' => array(),
		);

		return $compatibility;
	}
}
