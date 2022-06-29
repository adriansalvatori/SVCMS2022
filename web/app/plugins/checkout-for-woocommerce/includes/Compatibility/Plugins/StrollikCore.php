<?php

namespace Objectiv\Plugins\Checkout\Compatibility\Plugins;

use Objectiv\Plugins\Checkout\Compatibility\CompatibilityAbstract;

class StrollikCore extends CompatibilityAbstract {
	public function is_available(): bool {
		return defined( 'STROLLIK_CORE_VERSION' );
	}

	public function run() {
		remove_action( 'woocommerce_checkout_before_customer_details', 'osf_checkout_before_customer_details_container', 1 );
		remove_action( 'woocommerce_checkout_after_customer_details', 'osf_checkout_after_customer_details_container', 1 );
		remove_action( 'woocommerce_checkout_after_order_review', 'osf_checkout_after_order_review_container', 1 );
		remove_action( 'woocommerce_checkout_order_review', 'osf_woocommerce_order_review_heading', 1 );
	}
}
