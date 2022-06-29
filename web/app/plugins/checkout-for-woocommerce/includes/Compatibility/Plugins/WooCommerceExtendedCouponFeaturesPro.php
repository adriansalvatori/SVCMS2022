<?php

namespace Objectiv\Plugins\Checkout\Compatibility\Plugins;

use Objectiv\Plugins\Checkout\Compatibility\CompatibilityAbstract;

class WooCommerceExtendedCouponFeaturesPro extends CompatibilityAbstract {
	public function is_available(): bool {
		return defined( 'WJECF_VERSION' );
	}

	public function run() {
		$wjecf = wjecf();

		/** @var \WJECF_Pro_Free_Products $free_plugins */
		$free_plugins = $wjecf->get_plugin( 'pro-free-products' );

		if ( $free_plugins ) {
			add_filter( 'cfw_checkout_cart_summary', array( $free_plugins, 'render_checkout_select_free_product' ), 55 ); // after coupon module
		}
	}

	public function run_on_update_checkout() {
		$this->run();
	}
}
