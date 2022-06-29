<?php

namespace Objectiv\Plugins\Checkout\Compatibility\Plugins;

use Objectiv\Plugins\Checkout\Compatibility\CompatibilityAbstract;

class UpsellOrderBumpOffer extends CompatibilityAbstract {
	public function is_available(): bool {
		return defined( 'UPSELL_ORDER_BUMP_OFFER_FOR_WOOCOMMERCE_VERSION' );
	}

	public function run() {
		$mwb_ubo_global_options = get_option( 'mwb_ubo_global_options', array() );

		$bump_offer_location = ! empty( $mwb_ubo_global_options['mwb_ubo_offer_location'] ) ? $mwb_ubo_global_options['mwb_ubo_offer_location'] : '_after_payment_gateways';

		$plugin_public = new \Upsell_Order_Bump_Offer_For_Woocommerce_Public( '', '' );

		if ( '_before_order_summary' === $bump_offer_location ) {
			add_action( 'woocommerce_checkout_before_order_review', array( $plugin_public, 'show_offer_bump' ) );
		} elseif ( '_before_payment_gateways' === $bump_offer_location ) {
			add_action( 'cfw_checkout_before_payment_methods', array( $plugin_public, 'show_offer_bump' ) );
		}
	}
}
