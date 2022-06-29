<?php

$customizer = xt_woo_floating_cart()->customizer();

$icons                            = array();
$icons['cart_trigger_icon']       = $customizer->get_option( 'cart_trigger_icon' );
$icons['cart_trigger_close_icon'] = $customizer->get_option( 'cart_trigger_close_icon' );

$options = $customizer->get_options();

foreach ( $icons as $key => $icon ) {

	if ( ! empty( $icon ) && strpos( $icon, 'xt_' ) === false ) {
		$options[ $key ] = 'xt_' . $icon;
	}
}

$customizer->update_options( $options );