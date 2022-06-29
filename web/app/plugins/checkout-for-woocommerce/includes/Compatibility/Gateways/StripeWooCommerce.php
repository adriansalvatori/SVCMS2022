<?php

namespace Objectiv\Plugins\Checkout\Compatibility\Gateways;

use Objectiv\Plugins\Checkout\Compatibility\CompatibilityAbstract;

class StripeWooCommerce extends CompatibilityAbstract {
	public function is_available(): bool {
		return class_exists( '\\WC_Stripe_Field_Manager' );
	}

	public function run() {
		// Remove theirs
		remove_action(
			'woocommerce_checkout_before_customer_details',
			array(
				'\\WC_Stripe_Field_Manager',
				'output_banner_checkout_fields',
			)
		);

		// Add our own stripe requests
		add_action( 'cfw_payment_request_buttons', array( '\\WC_Stripe_Field_Manager', 'output_banner_checkout_fields' ), 1 );
		add_action( 'cfw_after_payment_request_buttons', 'cfw_add_separator', 12 ); // This should be 12, which is after 11, which is the hook other gateways use
	}
}
