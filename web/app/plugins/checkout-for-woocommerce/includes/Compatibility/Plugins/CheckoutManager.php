<?php

namespace Objectiv\Plugins\Checkout\Compatibility\Plugins;

use Objectiv\Plugins\Checkout\Compatibility\CompatibilityAbstract;

class CheckoutManager extends CompatibilityAbstract {
	public function is_available(): bool {
		return defined( 'WOOCCM_PATH' );
	}

	public function run() {
		remove_filter( 'woocommerce_checkout_fields', 'wooccm_remove_fields_filter_billing', 15 );
		remove_filter( 'woocommerce_checkout_fields', 'wooccm_remove_fields_filter_shipping', 1 );
		remove_filter( 'woocommerce_billing_fields', 'wooccm_checkout_billing_fields' );
		remove_filter( 'woocommerce_default_address_fields', 'wooccm_checkout_default_address_fields' );
		remove_filter( 'woocommerce_shipping_fields', 'wooccm_checkout_shipping_fields' );
		remove_action( 'woocommerce_checkout_fields', 'wooccm_order_notes' );
		remove_action( 'wp_head', 'wooccm_display_front' );
		remove_action( 'wp_head', 'wooccm_billing_hide_required' );
		remove_action( 'wp_head', 'wooccm_shipping_hide_required' );
	}
}
