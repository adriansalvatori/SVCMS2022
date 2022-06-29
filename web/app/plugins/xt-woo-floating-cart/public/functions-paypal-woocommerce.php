<?php

function xt_woofc_pfw_disable_checkout_redirect() {

	if ( class_exists( 'Angelleye_PayPal_Express_Checkout_Helper' ) ) {
		remove_action( 'template_redirect', array(
			Angelleye_PayPal_Express_Checkout_Helper::instance(),
			'angelleye_redirect_to_checkout_page'
		) );
	}
}
add_action( 'xt_woofc_before_woocommerce_constants', 'xt_woofc_pfw_disable_checkout_redirect' );
