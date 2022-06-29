<?php

namespace Objectiv\Plugins\Checkout\Compatibility\Plugins;

use Objectiv\Plugins\Checkout\Compatibility\CompatibilityAbstract;

class WooCommerceOrderDelivery extends CompatibilityAbstract {
	public function is_available(): bool {
		return class_exists( '\\WC_OD_Checkout' );
	}

	public function run() {
		$WC_OD_Checkout = \WC_OD_Checkout::instance();

		remove_action( 'woocommerce_checkout_shipping', array( $WC_OD_Checkout, 'checkout_content' ), 99 );
		add_action( 'cfw_checkout_shipping_method_tab', array( $WC_OD_Checkout, 'checkout_content' ), 25 );
	}

	public function typescript_class_and_params( array $compatibility ): array {
		$compatibility[] = array(
			'class'  => 'WooCommerceOrderDelivery',
			'params' => array(),
		);

		return $compatibility;
	}
}
