<?php

namespace Objectiv\Plugins\Checkout\Compatibility\Plugins;

use Objectiv\Plugins\Checkout\Compatibility\CompatibilityAbstract;

class PortugalVaspKios extends CompatibilityAbstract {
	public function is_available(): bool {
		return function_exists( 'pvkw_init' );
	}

	public function run() {
		add_action( 'cfw_checkout_after_shipping_methods', 'pvkw_woocommerce_review_order_before_payment' );
	}

	public function typescript_class_and_params( array $compatibility ): array {
		$compatibility[] = array(
			'class'  => 'PortugalVaspKios',
			'params' => array(),
		);

		return $compatibility;
	}
}
