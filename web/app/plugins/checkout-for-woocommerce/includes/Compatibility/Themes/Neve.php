<?php

namespace Objectiv\Plugins\Checkout\Compatibility\Themes;

use Objectiv\Plugins\Checkout\Compatibility\CompatibilityAbstract;

class Neve extends CompatibilityAbstract {
	public function is_available(): bool {
		return defined( 'NEVE_VERSION' );
	}

	function run() {
		$neve_woocommerce_compatibility = cfw_get_hook_instance_object( 'woocommerce_before_checkout_form', 'move_coupon' );

		if ( $neve_woocommerce_compatibility ) {
			remove_action( 'woocommerce_before_checkout_form', array( $neve_woocommerce_compatibility, 'move_coupon' ) );
			remove_action( 'woocommerce_before_checkout_billing_form', array( $neve_woocommerce_compatibility, 'clear_coupon' ) );
		}
	}
}
