<?php

namespace Objectiv\Plugins\Checkout\Compatibility\Themes;

use Objectiv\Plugins\Checkout\Compatibility\CompatibilityAbstract;

class Tokoo extends CompatibilityAbstract {
	public function is_available(): bool {
		return function_exists( 'tokoo_order_review_open' );
	}

	function run_on_update_checkout() {
		$this->run();
	}

	function run() {
		remove_action( 'woocommerce_before_checkout_shipping_form', 'tokoo_form_shipping_title', 10 );
		remove_action( 'woocommerce_before_checkout_form', 'tokoo_wc_checkout_login_form', 10 );
		remove_action( 'woocommerce_login_form', 'tokoo_login_form_footer_open', 0 );
		remove_action( 'woocommerce_login_form_end', 'tokoo_login_form_footer_close', 90 );
		remove_action( 'woocommerce_checkout_before_customer_details', 'tokoo_customer_details_open', 0 );
		remove_action( 'woocommerce_checkout_after_customer_details', 'tokoo_customer_details_close', 90 );
		remove_action( 'woocommerce_checkout_before_order_review', 'tokoo_order_review_open', 0 );
		remove_action( 'woocommerce_checkout_after_order_review', 'tokoo_order_review_close', 90 );
		remove_filter( 'woocommerce_get_order_item_totals', 'tokoo_add_order_item_totals_title', 10 );
		remove_action( 'woocommerce_review_order_before_payment', 'tokoo_payment_method_title', 10 );
		remove_filter( 'woocommerce_checkout_show_terms', '__return_false', 10 );
		remove_action( 'woocommerce_review_order_before_submit', 'tokoo_place_order_button_wrapper_open', 10 );
		remove_action( 'woocommerce_review_order_after_submit', 'tokoo_place_order_button_wrapper_close', 10 );
	}
}
