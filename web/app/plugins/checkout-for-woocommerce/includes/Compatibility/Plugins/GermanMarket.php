<?php

namespace Objectiv\Plugins\Checkout\Compatibility\Plugins;

use Objectiv\Plugins\Checkout\Compatibility\CompatibilityAbstract;

class GermanMarket extends CompatibilityAbstract {
	public function is_available(): bool {
		return class_exists( '\\WGM_Template' );
	}

	public function run_immediately() {
		add_filter( 'cfw_gateway_order_button_text', array( $this, 'override_gateway_order_button_text' ), 10, 2 );
		remove_filter( 'woocommerce_billing_fields', array( 'WGM_Template', 'billing_fields' ) );
		remove_filter( 'woocommerce_shipping_fields', array( 'WGM_Template', 'shipping_fields' ) );

		if ( remove_action( 'woocommerce_checkout_order_review', array( 'WGM_Template', 'add_review_order' ), 15 ) ) {
			add_action( 'woocommerce_review_order_before_submit', array( 'WGM_Template', 'add_review_order' ), 15 );
		}
	}

	public function override_gateway_order_button_text( $button_text, $gateway ) {
		$button_text = \WGM_Template::change_order_button_text( $button_text );
		$button_text = apply_filters( 'woocommerce_de_buy_button_text_gateway_' . $gateway->id, $button_text, $gateway->order_button_text );

		return $button_text;
	}
}
