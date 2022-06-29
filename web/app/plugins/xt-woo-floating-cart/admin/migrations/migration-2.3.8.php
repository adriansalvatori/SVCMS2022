<?php

$customizer = xt_woo_floating_cart()->customizer();

$sync_desktop_values_to_other = array(
	'cart_menu_menus',
	'cart_menu_display',
	'cart_menu_counter_type',
    'cart_menu_counter_badge_position',
    'cart_menu_counter_badge_size',
    'cart_menu_badge_text_color',
    'cart_menu_badge_bg_color',

    'cart_shortcode_size',
    'cart_shortcode_display',
    'cart_shortcode_counter_type',
    'cart_shortcode_counter_badge_position',
    'cart_shortcode_counter_badge_size',
    'cart_shortcode_badge_text_color',
    'cart_shortcode_badge_bg_color',
);

$options = $customizer->get_options();

foreach ( $sync_desktop_values_to_other as $key) {

	if(isset($options[$key])) {

		$original_value = $options[$key];

		// Sync values with tablet and mobile options
		foreach(array('tablet', 'mobile') as $screen) {

		    $new_key = $key.'_'.$screen;
			$options[$new_key] = $original_value;
		}
	}
}

$customizer->update_options( $options );