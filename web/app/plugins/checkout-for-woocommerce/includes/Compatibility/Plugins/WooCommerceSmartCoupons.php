<?php

namespace Objectiv\Plugins\Checkout\Compatibility\Plugins;

use Objectiv\Plugins\Checkout\Compatibility\CompatibilityAbstract;

class WooCommerceSmartCoupons extends CompatibilityAbstract {
	public function is_available(): bool {
		return class_exists( '\\WC_Smart_Coupons' );
	}

	public function run() {
		$wc_sc_purchase_credit = \WC_SC_Purchase_Credit::get_instance();
		add_action( 'cfw_checkout_before_payment_method_terms_checkbox', array( $wc_sc_purchase_credit, 'gift_certificate_receiver_detail_form' ) );
		remove_action( 'woocommerce_checkout_after_customer_details', array( $wc_sc_purchase_credit, 'gift_certificate_receiver_detail_form' ) );
	}
}
