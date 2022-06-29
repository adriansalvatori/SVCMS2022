<?php

namespace Objectiv\Plugins\Checkout\Compatibility\Plugins;

use Objectiv\Plugins\Checkout\Compatibility\CompatibilityAbstract;

class SavedAddressesForWooCommerce extends CompatibilityAbstract {
	/**
	 * @return bool
	 */
	public function is_available(): bool {
		return class_exists( '\\SA_Saved_Addresses_For_WooCommerce' );
	}

	public function run() {
		$saved_addresses_for_woocommerce = \SA_Saved_Addresses_For_WooCommerce::get_instance();

		remove_action( 'woocommerce_before_checkout_billing_form', array( $saved_addresses_for_woocommerce, 'get_billing_addresses' ) );
		remove_action( 'woocommerce_after_checkout_billing_form', array( $saved_addresses_for_woocommerce, 'enclose_the_billing_form' ) );
		add_action( 'cfw_start_billing_address_container', array( $saved_addresses_for_woocommerce, 'get_billing_addresses' ) );

		remove_action( 'woocommerce_before_checkout_shipping_form', array( $saved_addresses_for_woocommerce, 'get_shipping_addresses' ) );
		remove_action( 'woocommerce_after_checkout_shipping_form', array( $saved_addresses_for_woocommerce, 'enclose_the_shipping_form' ) );

		add_action( 'cfw_end_shipping_address_container', array( $saved_addresses_for_woocommerce, 'enclose_the_shipping_form' ) );
		add_action( 'cfw_start_shipping_address_container', array( $saved_addresses_for_woocommerce, 'get_shipping_addresses' ) );
	}
}
