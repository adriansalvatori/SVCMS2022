<?php

$version = get_option( 'woo-floating-cart-version' );
$options = get_option( 'woofc' );

if ( update_option( 'xt-woo-floating-cart-version', $version ) ) {

	delete_option( 'woo-floating-cart-version' );
}

if ( update_option( 'xt_woofc', $options ) ) {

	delete_option( 'woofc' );
}