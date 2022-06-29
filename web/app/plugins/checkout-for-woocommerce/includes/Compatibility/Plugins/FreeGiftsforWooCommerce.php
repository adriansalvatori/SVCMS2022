<?php

namespace Objectiv\Plugins\Checkout\Compatibility\Plugins;

class FreeGiftsforWooCommerce {
	protected $side_cart_enabled = false;

	public function init( bool $side_cart_enabled ) {
		add_action( 'cfw_cart_updated', array( $this, 'update_cart_gifts' ) );

		if ( $side_cart_enabled ) {
			add_action( 'wp', array( $this, 'prevent_redirect' ), 0 );
		}
	}

	public function prevent_redirect() {
		// Fix for Free Gifts for WooCommerce that causes add to cart output to be hijacked with side cart
		remove_action( 'wp', array( 'FGF_Gift_Products_Handler', 'add_to_cart_automatic_gift_product' ) );
	}

	public function update_cart_gifts() {
		if ( ! defined( 'FGF_PLUGIN_FILE' ) ) {
			return;
		}

		\FGF_Rule_Handler::reset();
		\FGF_Gift_Products_Handler::automatic_gift_product( false );
		\FGF_Gift_Products_Handler::bogo_gift_product( false );
		\FGF_Gift_Products_Handler::coupon_gift_product( false );
		\FGF_Gift_Products_Handler::remove_gift_products();
	}
}
