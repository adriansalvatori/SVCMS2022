<?php

namespace Objectiv\Plugins\Checkout\Compatibility\Plugins;

use Objectiv\Plugins\Checkout\Compatibility\CompatibilityAbstract;

class WooCommerceEUUKVATCompliancePremium extends CompatibilityAbstract {
	public function is_available(): bool {
		return function_exists( 'WooCommerce_EU_VAT_Compliance' );
	}

	public function run() {
		$object = cfw_get_hook_instance_object( 'woocommerce_checkout_billing', 'vat_number_field', 40 );

		if ( ! $object ) {
			return;
		}

		remove_action( 'woocommerce_checkout_billing', array( $object, 'vat_number_field' ), 40 );

		add_action( 'cfw_checkout_customer_info_tab', array( $object, 'vat_number_field' ), 52 );
	}
}
