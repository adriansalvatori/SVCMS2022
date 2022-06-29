<?php

// update multicolor field to 2 color fields	
$fields = [
	'cart_checkout_button_bg_color'     => array(
		'cart_checkout_button_bg_color',
		'cart_checkout_button_bg_hover_color'
	),
	'cart_checkout_button_text_color'   => array(
		'cart_checkout_button_text_color',
		'cart_checkout_button_text_hover_color'
	),
	'cart_header_undo_link_color'       => array( 'cart_header_undo_link_color', 'cart_header_undo_link_hover_color' ),
	'cart_product_title_color'          => array( 'cart_product_title_color', 'cart_product_title_hover_color' ),
	'cart_product_qty_plus_minus_color' => array(
		'cart_product_qty_plus_minus_color',
		'cart_product_qty_plus_minus_hover_color'
	),
];

$customizer = xt_woo_floating_cart()->customizer();

foreach ( $fields as $field ) {

	$old_key = $field[0];

	$link_color_key  = $old_key;
	$hover_color_key = $field[1];

	$color = $customizer->get_option( $old_key );

	if ( ! empty( $color ) ) {

		if ( isset( $color['link'] ) ) {
			$customizer->update_option( $link_color_key, $color['link'] );
		}

		if ( isset( $color['hover'] ) ) {
			$customizer->update_option( $hover_color_key, $color['hover'] );
		}
	}
}
