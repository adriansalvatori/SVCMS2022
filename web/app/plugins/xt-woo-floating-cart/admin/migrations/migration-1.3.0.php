<?php

$customizer = xt_woo_floating_cart()->customizer();

$fields                                      = array();
$fields['cart_header_undo_color']            = $customizer->get_option( 'cart_header_undo_color' );
$fields['cart_header_undo_link_color']       = $customizer->get_option( 'cart_header_undo_link_color' );
$fields['cart_header_undo_link_hover_color'] = $customizer->get_option( 'cart_header_undo_link_hover_color' );
$fields['typo_header_undo_msg']              = $customizer->get_option( 'typo_header_undo_msg' );

$options = $customizer->get_options();

foreach ( $fields as $key => $value ) {

	if ( ! empty( $value ) ) {
		$new_key             = str_replace( 'undo_', '', $key );
		$options[ $new_key ] = $value;
	}

	if ( isset( $options[ $key ] ) ) {
		unset( $options[ $key ] );
	}
}

$customizer->update_options( $options );