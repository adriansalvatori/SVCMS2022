<?php

namespace Objectiv\Plugins\Checkout\Compatibility\Plugins;

class WooCommerceMemberships {
	public function init() {
		add_action( 'wc_memberships_discounts_enable_price_html_adjustments', array( $this, 'queue_removal' ) );
	}

	public function queue_removal() {
		add_filter( 'woocommerce_get_item_data', array( $this, 'remove' ), 1 );
	}

	public function remove( $value ) {
		if ( ! function_exists( 'wc_memberships' ) ) {
			return $value;
		}

		$memberships = wc_memberships();

		if ( ! method_exists( $memberships, 'get_member_discounts_instance' ) ) {
			return $value;
		}

		$instance = $memberships->get_member_discounts_instance();
		$callback = array( $instance, 'display_cart_purchasing_discount_message' );

		remove_filter( 'woocommerce_get_item_data', $callback );

		return $value;
	}
}
